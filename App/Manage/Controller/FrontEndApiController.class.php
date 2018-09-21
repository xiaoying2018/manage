<?php
/**
 * Created by PhpStorm.
 * User: dragon
 * Date: 2018/7/18
 * Time: 14:01
 */

namespace Manage\Controller;

use Manage\Model\CallLogModel;
use Manage\Model\CallStudentModel;
use Manage\Model\CallTeacherModel;
use Manage\Model\NewscateModel;
use Manage\Model\NewscontentsModel;
use Manage\Model\PremissionModel;
use Manage\Model\RoleModel;
use Manage\Model\SchoolModel;
use Manage\Model\SgpschoolModel;
use Manage\Model\TagsModel;
use Manage\Model\UserModel;
use Manage\Model\ZhanhuiModel;
use Think\Controller;

class FrontEndApiController extends Controller
{
    private $lasttime;
    /**
     * 有层次的权限列表
     */
    public function preTreeApi()
    {

        // 获取所有权限
        $premissions = (new PremissionModel())->field('id,name,pid')->select();

        if (I('get.pre_id'))// 如果来自权限修改
        {
            $pre_pid = (new PremissionModel())->find(I('get.pre_id'))['pid'];// 获取要修改的权限父ID
            foreach ($premissions as $k => $v) {
                if ($v['id'] == $pre_pid) {
//                  $premissions[$k]['is_checked'] = 1;
                    $checked_parent = $v;
                }
            }
        }

        if (I('get.role_id'))// 如果来自角色修改
        {
            // 获取当前角色已有权限
            $_has_premission = M('RolePre')->where(['role_id' => ['eq', I('get.role_id')]])->field('pre_id')->select();
            // 二维转一维
            $has_premission = array_map(function ($v) {
                return $v['pre_id'];
            }, $_has_premission);
            // 添加选中状态
            foreach ($premissions as $k => $pre) {
                foreach ($has_premission as $key => $has) {
                    if ($has == $pre['id']) {
                        $premissions[$k]['is_checked'] = 1;
                    }
                }
            }
        }

        // 格式化权限树
        $tree = getPreTree($premissions);

        $this->ajaxReturn(['status' => true, 'data' => $tree, 'is_checked' => $checked_parent]);
    }

    /**
     * 用户数据
     */
    public function usersearch()
    {
        $par = I();// 获取参数

        $page = $par['page'] ?: 1;// 当前页
        $limit = $par['limit'] ?: 15;// 每页显示条数
        $start = $limit * ($page - 1);// 当前查询起始条数
        $search_key = $par['search_key'] ?: '';// 查询关键字

        $condition = [];// 准备查询条件

        if ($search_key) $condition['name'] = ['LIKE', '%' . $search_key . '%'];// 如果按关键字查询

        $user_model = new UserModel();// 实例化用户模型

        $users = $user_model->where($condition)->limit($start, $limit)->select();// 获取数据列表

        foreach ($users as $k => $v) {
            $users[$k]['create_user_name'] = $user_model->field('name')->find($v['create_user'])['name'];
            $role_id = M('UserRole')->field('role_id')->where(['user_id' => ['eq', $v['id']]])->find()['role_id'];
            $users[$k]['role_name'] = ' - ';
            if ($role_id) $users[$k]['role_name'] = (new RoleModel())->field('name')->find($role_id)['name'];
        }

        $count = $user_model->where($condition)->count();// 统计数据总数

        $this->ajaxReturn([
            'code' => 0,// 0为成功
            'msg' => '',// 错误提示
            'count' => $count,// 总条数
            'data' => $users// 数据列表
        ]);
    }

    /**
     * 角色数据
     */
    public function rolesearch()
    {

        $par = I();// 获取参数

        $page = $par['page'] ?: 1;// 当前页
        $limit = $par['limit'] ?: 15;// 每页显示条数
        $start = $limit * ($page - 1);// 当前查询起始条数
        $search_key = $par['search_key'] ?: '';// 查询关键字

        $condition = [];// 准备查询条件

        if ($search_key) $condition['name'] = ['LIKE', '%' . $search_key . '%'];// 如果按关键字查询

        $role_model = new RoleModel();// 实例化角色模型

        $users = $role_model->where($condition)->limit($start, $limit)->select();// 获取数据列表

        foreach ($users as $k => $v) {
            $users[$k]['create_user_name'] = (new UserModel())->field('name')->find($v['create_user'])['name'];
        }

        $count = $role_model->where($condition)->count();// 统计数据总数

        $this->ajaxReturn([
            'code' => 0,// 0为成功
            'msg' => '',// 错误提示
            'count' => $count,// 总条数
            'data' => $users// 数据列表
        ]);
    }

    /**
     * 权限数据
     */
    public function premissionsearch()
    {

        $par = I();// 获取参数

        $page = $par['page'] ?: 1;// 当前页
        $limit = $par['limit'] ?: 15;// 每页显示条数
        $start = $limit * ($page - 1);// 当前查询起始条数
        $search_key = $par['search_key'] ?: '';// 查询关键字

        $condition = [];// 准备查询条件

        if ($search_key) $condition['name'] = ['LIKE', '%' . $search_key . '%'];// 如果按关键字查询

        $premission_model = new PremissionModel();// 实例化权限模型

        $premissions = $premission_model->where($condition)->limit($start, $limit)->select();// 获取数据列表

        foreach ($premissions as $k => $v) {
            $premissions[$k]['create_user_name'] = (new UserModel())->field('name')->find($v['create_user'])['name'];
            if ($v['pid']) {
                $premissions[$k]['pid_name'] = (new PremissionModel())->field('name')->find($v['pid'])['name'];
            } else {
                $premissions[$k]['pid_name'] = '顶级权限';
            }
        }

        $count = $premission_model->where($condition)->count();// 统计数据总数

        $this->ajaxReturn([
            'code' => 0,// 0为成功
            'msg' => '',// 错误提示
            'count' => $count,// 总条数
            'data' => $premissions// 数据列表
        ]);
    }

    /**
     * 展会城市数据
     */
    public function zhanhuisearch()
    {
        $par = I();// 获取参数

        $page = $par['page'] ?: 1;// 当前页
        $limit = $par['limit'] ?: 15;// 每页显示条数
        $start = $limit * ($page - 1);// 当前查询起始条数
        $search_key = $par['search_key'] ?: '';// 查询关键字

        $condition = [];// 准备查询条件

        if ($search_key) $condition['name'] = ['LIKE', '%' . $search_key . '%'];// 如果按关键字查询

        $zhanhui_city = new ZhanhuiModel();// 实例化模型

        $kbs = $zhanhui_city->where($condition)->order('sort')->limit($start, $limit)->select();// 获取数据列表

        foreach ($kbs as $k => $v) {
            // 创建时间
            $kbs[$k]['create_time'] = $kbs[$k]['create_time'] ? date('Y-m-d H:i:s', $kbs[$k]['create_time']) : ' - ';
            // 创建者
            $kbs[$k]['create_user'] = $kbs[$k]['create_user'] ? (new UserModel())->field('name')->find($v['create_user'])['name'] : ' - ';
            // 更新时间
            $kbs[$k]['update_time'] = $kbs[$k]['update_time'] ? date('Y-m-d H:i:s', $kbs[$k]['update_time']) : ' - ';
            // 更新者
            $kbs[$k]['update_user'] = $kbs[$k]['update_user'] ? (new UserModel())->field('name')->find($v['update_user'])['name'] : ' - ';
        }

        $count = $zhanhui_city->where($condition)->count();// 统计数据总数

        $this->ajaxReturn([
            'code' => 0,// 0为成功
            'msg' => '',// 错误提示
            'count' => $count,// 总条数
            'data' => $kbs// 数据列表
        ]);
    }

    /**
     * 名师数据
     */
    public function callteachersearch()
    {
        $par = I();// 获取参数

        $page = $par['page'] ?: 1;// 当前页
        $limit = $par['limit'] ?: 15;// 每页显示条数
        $start = $limit * ($page - 1);// 当前查询起始条数
        $search_key = $par['search_key'] ?: '';// 查询关键字

        $condition = [];// 准备查询条件

        if ($search_key) $condition['name'] = ['LIKE', '%' . $search_key . '%'];// 如果按关键字查询

        $kb_model = new CallTeacherModel();// 实例化模型

        $kbs = $kb_model->where($condition)->limit($start, $limit)->order('sort')->select();// 获取数据列表

        foreach ($kbs as $k => $v) {
            // 创建时间
            $kbs[$k]['create_time'] = $kbs[$k]['create_time'] ? date('Y-m-d H:i:s', $kbs[$k]['create_time']) : ' - ';
            // 创建者
            $kbs[$k]['create_user'] = $kbs[$k]['create_user'] ? (new UserModel())->field('name')->find($v['create_user'])['name'] : ' - ';
            // 更新时间
            $kbs[$k]['update_time'] = $kbs[$k]['update_time'] ? date('Y-m-d H:i:s', $kbs[$k]['update_time']) : ' - ';
            // 更新者
            $kbs[$k]['update_user'] = $kbs[$k]['update_user'] ? (new UserModel())->field('name')->find($v['update_user'])['name'] : ' - ';
        }

        $count = $kb_model->where($condition)->count();// 统计数据总数

        $this->ajaxReturn([
            'code' => 0,// 0为成功
            'msg' => '',// 错误提示
            'count' => $count,// 总条数
            'data' => $kbs// 数据列表
        ]);
    }

    /**
     * 学员数据
     */
    public function callstudentsearch()
    {
        $par = I();// 获取参数

        $page = $par['page'] ?: 1;// 当前页
        $limit = $par['limit'] ?: 15;// 每页显示条数
        $start = $limit * ($page - 1);// 当前查询起始条数
        $search_key = $par['search_key'] ?: '';// 查询关键字

        $condition = [];// 准备查询条件

        if ($search_key) $condition['name'] = ['LIKE', '%' . $search_key . '%'];// 如果按关键字查询

        $kb_model = new CallStudentModel();// 实例化模型

        $kbs = $kb_model->where($condition)->limit($start, $limit)->order('create_time desc')->select();// 获取数据列表

        foreach ($kbs as $k => $v) {
            // 创建时间
            $kbs[$k]['create_time'] = $kbs[$k]['create_time'] ? date('Y-m-d H:i:s', $kbs[$k]['create_time']) : ' - ';
            // 更新时间
            $kbs[$k]['update_time'] = $kbs[$k]['update_time'] ? date('Y-m-d H:i:s', $kbs[$k]['update_time']) : ' - ';
            // 更新者
            $kbs[$k]['update_user'] = $kbs[$k]['update_user'] ? (new UserModel())->field('name')->find($v['update_user'])['name'] : ' - ';
        }

        $count = $kb_model->where($condition)->count();// 统计数据总数

        $this->ajaxReturn([
            'code' => 0,// 0为成功
            'msg' => '',// 错误提示
            'count' => $count,// 总条数
            'data' => $kbs// 数据列表
        ]);
    }

    /**
     * 打call日志数据
     */
    public function calllogsearch()
    {
        $par = I();// 获取参数

        $page = $par['page'] ?: 1;// 当前页
        $limit = $par['limit'] ?: 15;// 每页显示条数
        $start = $limit * ($page - 1);// 当前查询起始条数
        $search_key = $par['search_key'] ?: '';// 查询关键字

        $condition = [];// 准备查询条件

        // 如果按学员姓名关键字查询
        if ($search_key) {
            $users = (new CallStudentModel())->field('id,name')->where(['name' => ['like', '%' . $search_key . '%']])->select();

            if ($users) {
                $ids = array_map(function ($v) {
                    return $v['id'];
                }, $users);

                $condition['stu_id'] = ['in', $ids];
            } else {
                // 数据为空
                $this->ajaxReturn([
                    'code' => 0,// 0为成功
                    'msg' => '',// 错误提示
                    'count' => 0,// 总条数
                    'data' => []// 数据列表
                ]);
            }
        }

        $kb_model = new CallLogModel();// 实例化模型

        $kbs = $kb_model->where($condition)->limit($start, $limit)->order('create_time desc')->select();// 获取数据列表

        foreach ($kbs as $k => $v) {
            // 创建时间
            $kbs[$k]['create_time'] = $kbs[$k]['create_time'] ? date('Y-m-d H:i:s', $kbs[$k]['create_time']) : ' - ';
            $kbs[$k]['stu_name'] = (new CallStudentModel())->find($v['stu_id'])['name'] ?: ' - ';
            $kbs[$k]['tea_name'] = (new CallTeacherModel())->find($v['tea_id'])['name'] ?: ' - ';
        }

        $count = $kb_model->where($condition)->count();// 统计数据总数

        $this->ajaxReturn([
            'code' => 0,// 0为成功
            'msg' => '',// 错误提示
            'count' => $count,// 总条数
            'data' => $kbs// 数据列表
        ]);
    }

    /**
     * 角色关联权限接口
     */
    public function updatePreApi()
    {
        if (IS_POST) {
            $type = I('post.type');// 更新类型
            $role_id = I('post.role_id');// 角色ID
            $pre_id = I('post.pre_id');// 权限ID

            if (!$type || !$role_id || !$pre_id) $this->ajaxReturn(['status' => false, 'msg' => '缺少关键参数']);

            if (!is_array($pre_id)) $this->ajaxReturn(['status' => false, 'msg' => '参数类型错误']);

            if ($type == 'del')// 取消关联 删除操作
            {
                foreach ($pre_id as $k => $v) {
                    M('RolePre')->where(['role_id' => ['eq', $role_id], 'pre_id' => ['eq', $v]])->delete();// 删除原有的
                }
//                $res = M('RolePre')->where(['role_id' => ['eq', $role_id], 'pre_id'=>['eq',$pre_id]])->delete();// 删除原有的
                $this->ajaxReturn(['status' => true, 'msg' => '更新成功']);
            }

            if ($type == 'add')// 添加关联 新增操作
            {
                foreach ($pre_id as $k => $v) {
                    $res = M('RolePre')->add(['role_id' => $role_id, 'pre_id' => $v]);// 新增权限关联
                }
                $this->ajaxReturn(['status' => true, 'msg' => '更新成功']);
            }

            $this->ajaxReturn(['status' => false, 'msg' => '未知的请求']);

        }

        $this->ajaxReturn(['status' => false, 'msg' => '非法请求']);
    }

    /**
     * 图片上传接口
     */
    public function uploadone()
    {
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize = 3145728;// 设置附件上传大小
        $upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
        $upload->rootPath = './Uploads/'; // 设置附件上传根目录
        $upload->savePath = 'img/'; // 设置附件上传子目录
        // 上传文件
        $info = $upload->upload();
        if (!$info) {// 上传错误提示错误信息
            $this->ajaxReturn(['status' => false, 'msg' => $upload->getError()]);
        } else {// 上传成功
            // 拼接文件路径
            $save_file = $upload->rootPath . $info['file']['savepath'] . $info['file']['savename'];
            $this->ajaxReturn(['status' => true, 'data' => $save_file]);// 返回文件保存路径
        }
    }

    /**
     * 带缩略图的图片上传接口
     */
    public function uploadkoubei()
    {
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize = 3145728;// 设置附件上传大小
        $upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
        $upload->rootPath = './Uploads/'; // 设置附件上传根目录
        $upload->savePath = 'koubei/'; // 设置附件上传子目录
        // 上传文件
        $info = $upload->upload();
        if (!$info) {// 上传错误提示错误信息
            $this->ajaxReturn(['status' => false, 'msg' => $upload->getError()]);
        } else {// 上传成功
            $image = new \Think\Image();
            $thumb_file = $upload->rootPath . $info['file']['savepath'] . $info['file']['savename'];
            $save_path = $upload->rootPath . $info['file']['savepath'] . 'mini_' . $info['file']['savename'];// 保存缩略图的规则
            $image->open($thumb_file)->thumb('66', '66', \Think\Image::IMAGE_THUMB_FILLED)->save($save_path);
            $this->ajaxReturn([
                'status' => true,
                'data' => [
                    'img' => $thumb_file,
                    'mini_img' => $save_path
                ],
            ]);
        }
    }

    /**
     * 资讯接口
     */
    public function tree($rows, $pid = 0, $level = 0)
    {
        static $tree = [];
        foreach ($rows as $row) {
            if ($pid == $row['pid']) {
                $row['level'] = $level;
                $tree[] = $row;
                $this->tree($rows, $row['id'], $level + 1);
            }
        }
        return $tree;
    }

    //分类列表
    public function catesearch()
    {
        if (IS_AJAX) {
            $catemodel = new NewscateModel();
            $page = I('get.page');
            $offset = I('get.limit');
            $start = ($page - 1) * $offset;
            $end = ($page - 1) * $offset + $offset;
            $catedatas = $catemodel->select();
            $catedata = $this->tree($catedatas, 0, 0);
            foreach ($catedata as $k => $v) {
                $catedata[$k]['catename'] = str_repeat('&nbsp;&nbsp;&nbsp;', $v['level']) . $v['catename'];
                if ($k < $start || $k > $end - 1) {
                    unset($catedata[$k]);
                }
            }
//            $catedata = $catemodel->limit(($page - 1) * $offset, $offset)->select();
            $data['data'] = $catedata;
            $data['alldata'] = $catedatas;
            $data['code'] = 0;
            $data['msg'] = '';
            $data['count'] = count($catedatas);
            $this->ajaxReturn($data);
        }
    }

    //资讯内容列表
    public function contentssearch()
    {
//        if (IS_AJAX)
//        {
            $contentmodel = M('article');
            $page = I('get.page');
            $offset = I('get.limit');
            if(empty($page) || empty($offset) || $offset >100){
                $this->ajaxreturn(['status'=>false,'msg'=>'参数有误！']);
            }
            $stags = I('get.tagsearch');
            $contentdatas = $contentmodel->select();
            $tagsmodel = new TagsModel();
            $tagsdata = $tagsmodel->select();
            //标签搜索
            $cid = [];
            if($stags!=''){
                foreach ($contentdatas as $kk=>$vv){
                    if(strpos($vv['body'],$stags)){
                        $cid[] = $vv['id'];
//                        continue;
                    }
                }
                if(!empty($cid)){
                    $where['id'] = ['in',$cid];
                }
                $cid = [];
            }
            //分类搜索
            if($cates = I('get.cateid')!=''){
                $where['newscate'] = $cates;
            }

            $contentdata = $contentmodel->where($where)->limit(($page - 1) * $offset, $offset)->order('sticky desc,publishedTime desc')->select();
            $tag = [];
            foreach ($contentdata as $k=>$v){
//                var_dump($v);
                $contentdata[$k]['thumb'] = substr($v['thumb'],1);
                $contentdata[$k]['categoryname'] = $a = M('articleCategory')->where(['id'=>$v['categoryid']])->find()['name'];
                $contentdata[$k]['create_time'] = date('Y-m-d H:i:s',$v['publishedtime']);
//                $contentdata[$k]['catename'] = M('newscate')->where(['id'=>$v['newscate']])->find()['catename'];
                foreach($tagsdata as $k1=>$v1){
                    if(strpos($v['body'],$v1['tagname'])){
                        $tag[] = $v1['tagname'];
                    }
                }
                $contentdata[$k]['tags'] = array_unique($tag);
                $tag=[];
            }
            $data['data'] = $contentdata;
            $data['code'] = 0;
            $data['msg']='';
            $data['count'] = count($contentdatas);
            $this->ajaxReturn($data);
//        }
    }

    //资讯详情
    public function detailsearch()
    {
        $contentid = I('get.id');
        if (!$contentid) $this->ajaxReturn(['status' => false, 'msg' => '缺少关键参数']);
        $newsmodel = M('article');
        $tagsmodel = new TagsModel();
        $tagsdata = $tagsmodel->select();
        $newsdata = $newsmodel->where(['id' => $contentid])->find();
        if (!empty($newsdata)) {
            $newsdata['newscate'] = M('newscate')->where(['id' => $newsdata['newscate']])->find()['catename'];
            $newsdata['create_time'] = date('Y-m-d H:i:s', $newsdata['create_time']);
            $newsdata['create_user'] = M('user')->where(['id' => $newsdata['create_user']])->find()['name'];
            $tag = [];
            foreach($tagsdata as $k1=>$v1){
                if(strpos($newsdata['body'],$v1['tagname'])!==false){
                    $tag[] = $v1['tagname'];
                }
            }
            $newsdata['tags'] = $tag;
            $this->ajaxreturn(['status' => true, 'data' => $newsdata]);
        } else {
            $this->ajaxreturn(['status' => false, 'msg' => '暂无数据']);
        }
    }

    //不同环境下获取真实的IP
    private function get_ip()
    {
        //判断服务器是否允许$_SERVER
        if (isset($_SERVER)) {
            if (isset($_SERVER[HTTP_X_FORWARDED_FOR])) {
                $realip = $_SERVER[HTTP_X_FORWARDED_FOR];
            } elseif (isset($_SERVER[HTTP_CLIENT_IP])) {
                $realip = $_SERVER[HTTP_CLIENT_IP];
            } else {
                $realip = $_SERVER[REMOTE_ADDR];
            }
        } else {
            //不允许就使用getenv获取
            if (getenv("HTTP_X_FORWARDED_FOR")) {
                $realip = getenv("HTTP_X_FORWARDED_FOR");
            } elseif (getenv("HTTP_CLIENT_IP")) {
                $realip = getenv("HTTP_CLIENT_IP");
            } else {
                $realip = getenv("REMOTE_ADDR");
            }
        }
        return $realip;
    }

    //资讯点赞
    public function vote()
    {
        $cid = I('get.id');
        if (!$cid) $this->ajaxReturn(['status' => false, 'msg' => '缺少关键参数']);
        $last = S('lasttime');
        $now = time();
        if($last){
            $c = intval($now-$last);
        }
        if($c<1){
            $this->ajaxreturn(['status'=>false,'msg'=>'点赞过于频繁！']);
        }
        $last = S('lasttime',time());
        $newsmodel = M('article');
        $res = $newsmodel->where(['id' => $cid])->setInc('upsNum');
        if ($res != 0) {
            $this->ajaxreturn(['status' => true]);
        } else {
            $this->ajaxReturn(['status' => false]);
        }
    }

    //资讯阅读量
    public function reads()
    {
        $cid = I('post.id');
        if (!$cid) $this->ajaxReturn(['status' => false, 'msg' => '缺少关键参数']);
        $last = S('lasttime');
        $now = time();
        if($last){
            $c = intval($now-$last);
        }
        if($c<1){
            $this->ajaxreturn(['status'=>false,'msg'=>'访问过于频繁！']);
        }
        $last = S('lasttime',time());
        $newsmodel = M('article');
        $res = $newsmodel->where(['id' => $cid])->setInc('hits');
        if ($res != 0) {
            $this->ajaxreturn(['status' => true]);
        } else {
            $this->ajaxReturn(['status' => false]);
        }
    }

    //根据地区获取县
    public function getarea()
    {
        $aid = I('post.id');
        if(empty($aid)){
            $aid=2;
        }
        $areadata = M('city')->where(['pid' => $aid, 'country' => 2])->select();
        if (!empty($areadata)) {
            $this->ajaxreturn(['status' => true, 'data' => $areadata]);
        } else {
            $this->ajaxreturn(['status' => false]);
        }
    }

    //语言学校列表
    public function schoolsearch1()
    {
        if (IS_AJAX) {
            $schoolmodel = new SchoolModel();
            $params = I();
            $page = I('get.page')?I('get.page'):1;
            $offset = I('get.limit')?I('get.limit'):15;
            $where = [];
            $where['category_school'] = ['like',['%' . "语言学校" . '%']];
            if (!empty($params['hotorder'])) {//热门标签
                $where['hotorder'] = $params['hotorder'];
            }
            if (!empty($params['cost_fee'])) {//学费  1.60w以下 2.60-70  3.70-80  4.80以上
                switch ($params['cost_fee']) {
                    case 1 :$where['cost_fee'] = ['lt', 60];break;
                    case 2 :$where['cost_fee'] = ['between', [60, 70]];break;
                    case 3 :$where['cost_fee'] = ['between', [70, 80]];break;
                    case 4 :$where['cost_fee'] = ['gt', 80];break;
                }
            }
            if (!empty($params['enroll_time'])) {//入学时间
                $where['enroll_time'] = $params['enroll_time'];
            }
            if (!empty($params['nowcid'])) {//地区
                $where['nowcid'] = $params['nowcid'];
            }
            if (!empty($params['schoolname'])) {//学校名称
                $where['name_cn'] = ['like', ['%' . $params['schoolname'] . '%']];
            }
            $allcount = $schoolmodel->where($where)->count();
            $alldata = $schoolmodel->where($where)->limit(($page - 1) * $offset, $offset)->select();
            $data['data'] = $alldata;
            $data['code'] = 0;
            $data['msg'] = '';
            $data['count'] = $allcount;
            $this->ajaxReturn($data);
        }
    }

    //日本大学列表
    public function schoolsearch2(){
//        if (IS_POST) {

        $schoolmodel = new SchoolModel();
        $params = I();
        $page = I('get.page')?I('get.page'):1;
        $offset = I('get.limit')?I('get.limit'):15;
        $where = [];
        if (!empty($params['school_type'])) {//学校类型
            switch ($params['school_type']){
                case 1:$where['category_school'] = ['like',['%' . "语言学校" . '%']]; break;//语言学校
                case 2:$where['id'] = ['in',array_column(M('schoolType')->where(['type_id'=>82])->select(),'school_id')];break;//高中/专门学校
                case 3:$where['id'] = ['in',array_column(M('schoolType')->where(['type_id'=>18])->select(),'school_id')];break;//本科
                case 4:$where['id'] = ['in',array_column(M('schoolType')->where(['type_id'=>15])->select(),'school_id')];break;//大学别科，预科
                case 5:$where['id'] = ['in',array_column(M('schoolType')->where(['type_id'=>14])->select(),'school_id')];break;//大学别科，预科
            }
        }
        if(!empty($params['xingzhi_name'])){//学校性质
            $where['xingzhi_name'] = $params['xingzhi_name'];
        }
        if(!empty($params['rank'])){//学校排名
            switch ($params['rank']){
                case 1: $where['rank_local'] = ['between',[1,10]];break; //1-10
                case 2: $where['rank_local'] = ['between',[11,20]];break; //11-20
                case 3: $where['rank_local'] = ['between',[21,50]];break; //21-50
            }
        }
        if (!empty($params['nowcid'])) {//地区
            $where['nowcid'] = $params['nowcid'];
        }
        if (!empty($params['schoolname'])) {//学校名称
            $where['name_cn'] = ['like', ['%' . $params['schoolname'] . '%']];
        }
        $allcount = $schoolmodel->where($where)->count();
        $alldata = $schoolmodel->where($where)->limit(($page - 1) * $offset, $offset)->select();
        $data['data'] = $alldata;
        $data['code'] = 0;
        $data['msg'] = '';
        $data['count'] = $allcount;
        $this->ajaxReturn($data);
//        }
    }
    public function getxueli(){
        $xuelidata = M('programType')->select();
        $this->ajaxreturn($xuelidata);
    }

    public function getcate(){
        $catedata = M('programCate')->select();
        $this->ajaxreturn($catedata);
    }

    public function getschoolprogram(){
        $schoolid = I('get.id');
        $programtype = I('get.type');
        $programcate = I('get.cate');
        $where = [];

        if(!empty($programtype)){
            $where['eid'] = $programtype;
        }
        if(!empty($programcate)){
            $where['cid'] = $programcate;
        }
        if(!empty($schoolid)){
            $schoolname = M('school')->where(['id'=>$schoolid])->find()['name_cn'];
            $where['university'] = $schoolname;
        }else{
            $this->ajaxreturn(['status'=>false,'msg'=>'参数不完整！']);
        }
        $programdata = M('program')->where($where)->select();
        if(!empty($programdata)){
            $this->ajaxreturn(['status'=>true,'data'=>$programdata]);
        }else{
            $this->ajaxreturn(['status'=>false,'msg'=>'暂无数据！']);
        }

    }

    public function getprogramdetail(){
        $programid = I('get.id');
        if (!$programid) $this->ajaxReturn(['status' => false, 'msg' => '缺少关键参数']);
        $programdata = M('program')->where(['id'=>$programid])->find();
        if(!empty($programdata)){
            $programdata['schooldata'] = M('school')->where(['id'=>$programdata['comid']])->find();
            $this->ajaxreturn(['status'=>true,'data'=>$programdata]);
        }else{
            $this->ajaxreturn(['status'=>false,'data'=>[]]);
        }
    }
    //语言学校和日本大学学校详情
    public function schooldetail(){
        $schoolid = I('get.id');
        if (!$schoolid) $this->ajaxReturn(['status' => false, 'msg' => '缺少关键参数']);
        $schoolmodel = new SchoolModel();
        $sdata = $schoolmodel->where(['id' => $schoolid])->find();
        $fileids = array_column(M('schoolrfile')->where(['school_id'=>$schoolid])->select(),'file_id');
        if(!empty($fileids) && !empty($sdata)){
            $sdata['allpic'] = array_map(function ($v){
                return '/Uploads/' . $v;
            },array_column(M('file')->where(['file_id'=>['in',$fileids]])->select(),'file_path'));
        }

        if (!empty($sdata)) {
            $this->ajaxreturn(['status' => true, 'data' => $sdata]);
        } else {
            $this->ajaxreturn(['status' => false, 'msg' => '暂无数据']);
        }
    }
    public function clicks(){
        $cid = I('get.id');
        if (!$cid) $this->ajaxReturn(['status' => false, 'msg' => '缺少关键参数']);
        $last = S('lasttime');
        $now = time();
        if($last){
            $c = intval($now-$last);
        }
        if($c<5){
            $this->ajaxreturn(['status'=>false,'msg'=>'访问过于频繁！']);
        }
        $last = S('lasttime',time());
        $newsmodel = new SchoolModel();
        $res = $newsmodel->where(['id' => $cid])->setInc('hits');
        if ($res != 0) {
            $this->ajaxreturn(['status' => true]);
        } else {
            $this->ajaxReturn(['status' => false]);
        }
    }
    //韩国学校列表
    public function schoolsearch3(){
        $params = I();
        $page = I('get.page')?I('get.page'):1;
        $offset = I('get.limit')?I('get.limit'):15;
        $where = [];
        if(!empty($params['schoolname'])){//学校名称
            $where['name_cn'] = $params['schoolname'];
        }
        $schoolkoreamodel = M('schoolKorea');
        $allcount = $schoolkoreamodel->where($where)->count();
        $alldata = $schoolkoreamodel->where($where)->limit(($page-1)*$offset,$offset)->select();
        $this->ajaxreturn(['status'=>true,'count'=>$allcount,'data'=>$alldata]);
    }
    //韩国学校详情
    public function koreaschooldetail(){
        $schoolid = I('get.id');
        if (!$schoolid) $this->ajaxReturn(['status' => false, 'msg' => '缺少关键参数']);
        $sdata = M('schoolKorea')->where(['school_id' => $schoolid])->find();
        $fileids = array_column(M('schoolrfile')->where(['school_id'=>$schoolid])->select(),'file_id');
        if(!empty($fileids && !empty($sdata))){
            $sdata['allpic'] = array_map(function ($v){
                return '/Uploads/' . $v;
            },array_column(M('file')->where(['file_id'=>['in',$fileids]])->select(),'file_path'));
        }

        if (!empty($sdata)) {
            $this->ajaxreturn(['status' => true, 'data' => $sdata]);
        } else {
            $this->ajaxreturn(['status' => false, 'msg' => '暂无数据']);
        }
    }
    //新加坡学校列表
    public function schoolsearch4(){
        $params = I();
        $page = I('get.page')?I('get.page'):1;
        $offset = I('get.limit')?I('get.limit'):15;
        $where = [];
        $majorwhere = [];
        if(!empty($params['schoolname'])){//学校名称
            $where['name_cn'] = ['like',['%' . $params['schoolname'] .'%']];
        }
        if(!empty($params['majorname'])){//专业
            $majorsid = array_unique(array_column(M('schoolMajor')->where(['name_cn'=>$params['majorname']])->field('school_id')->select(),'school_id'));
            $where['school_id'] = ['in',$majorsid];
            $majorwhere['name_cn'] = ['like',['%' . $params['majorname'] . '%']];
        }
        if(!empty($params['xueli'])){//学历
            $majorsid = array_unique(array_column(M('schoolMajor')->where(['xueli'=>$params['xueli']])->field('school_id')->select(),'school_id'));
            $where['school_id'] = ['in',$majorsid];
            $majorwhere['xueli'] = $params['xueli'];
        }
        if(!empty($params['major'])){//专业方向
            $majorsid = array_unique(array_column(M('schoolMajor')->where(['major'=>$params['major']])->field('school_id')->select(),'school_id'));
            $where['school_id'] = ['in',$majorsid];
            $majorwhere['major'] = $params['major'];
        }

        $schoolSingaporemodel = new SgpschoolModel();

        $ssdata = $schoolSingaporemodel->getschoollist($page,$offset,$where);
        if(!empty($params['majorname']) || !empty($params['xueli']) || !empty($params['major'])){
            foreach ($ssdata as $k=>$v){
                $ssdata[$k]['major'] = M('schoolMajor')->where(['school_id'=>$v['school_id']])->where($majorwhere)->select();
            }
        }
        if(!empty($ssdata) && is_array($ssdata)){
            $this->ajaxreturn(['status'=>true,'data'=>$ssdata,'count'=>$schoolSingaporemodel->where($where)->count()]);
        }else{
            $this->ajaxreturn(['status'=>false,'data'=>$ssdata]);
        }
    }
    //新加坡学校详情介绍
    public function sgpschooldetail(){
        $schoolid = I('get.id');
        if (!$schoolid) $this->ajaxReturn(['status' => false, 'msg' => '缺少关键参数']);
        $sdata = M('schoolSingapore')->where(['id' => $schoolid])->find();
        $fileids = array_column(M('schoolrfile')->where(['school_id'=>$schoolid])->select(),'file_id');
        if(!empty($fileids && !empty($sdata))){
            $sdata['allpic'] = array_map(function ($v){
                return '/Uploads/' . $v;
            },array_column(M('file')->where(['file_id'=>['in',$fileids]])->select(),'file_path'));
        }
        if (!empty($sdata)) {
            $this->ajaxreturn(['status' => true, 'data' => $sdata]);
        } else {
            $this->ajaxreturn(['status' => false, 'msg' => '暂无数据']);
        }
    }
    ////新加坡学校专业介绍
    public function sgpmajordetail(){
        $schoolid = I('get.id');
        $params = I();
        if (!$schoolid) $this->ajaxReturn(['status' => false, 'msg' => '缺少关键参数']);
        $majorwhere = [];
        if(!empty($params['xueli'])){//学历
            $majorwhere['xueli'] = $params['xueli'];
        }
        if(!empty($params['major'])){//专业方向
            $majorwhere['major'] = $params['major'];
        }
        $sdata = M('schoolMajor')->where(['school_id' => $schoolid])->where($majorwhere)->where($majorwhere)->select();
        if (!empty($sdata)) {
            $this->ajaxreturn(['status' => true, 'data' => $sdata,'count'=>M('schoolMajor')->where(['school_id' => $schoolid])->where($majorwhere)->count(),'major'=>M('schoolMajor')->distinct(true)->field('major')->where(['school_id' => $schoolid])->select()]);
        } else {
            $this->ajaxreturn(['status' => false, 'msg' => '暂无数据']);
        }
    }

    /**
     * 学院库多图片上传接口
     */
    public function uploadall()
    {
        $upload = new \Think\Upload();// 实例化上传类
        $upload->maxSize = 3145728;// 设置附件上传大小
        $upload->exts = array('jpg', 'gif', 'png', 'jpeg');// 设置附件上传类型
        $upload->rootPath = './Uploads/'; // 设置附件上传根目录
        $upload->savePath = 'img/'; // 设置附件上传子目录
        // 上传文件
        $info = $upload->upload();
//        var_dump($info);die;
        $m_file = M('file');
        if(is_array($info) && !empty($info)){
            if(substr($info['file']['savename'], -3)=='jpg' || substr($info['file']['savename'], -3)=='png' || substr($info['file']['savename'], -4)=='jpeg'){
                $data['file_path_thumb'] = $info['file']['savepath'] .'thumb_'. $info['file']['savename'];
            }
            $data['file_path'] = $info['file']['savepath'] . $info['file']['savename'];
            $data['name'] = $info['file']['name'];
            $data['role_id'] = session('xy_manager')['id'];
            $data['size'] = $info['file']['size'];
            $data['create_date'] = time();
            $file_id = $m_file->add($data);
            if($file_id){
                $this->ajaxreturn(['file_id'=>$file_id,'filepath'=>'/Uploads/'.$info['file']['savepath'].$info['file']['savename'],'filename'=>$info['file']['name']]);
            }else{
                echo '"'.$data['name'].'" 上传失败！';
            }
        }
    }

//    public function delfile(){
////        echo 1;die;
//        if(IS_AJAX){
//            $id = I('file_id');
//            if (!$id) $this->ajaxReturn(['status' => false, 'msg' => '缺少关键参数']);
//            $del = M('file')->where(['file_id'=>$id])->delete();
//            if($del){
//                $this->ajaxreturn(['file_id'=>$id,'status'=>true]);
//            }else{
//                $this->ajaxreturn(['status'=>false]);
//            }
//        }
//
//    }

    /**
     * 获取网站banner图或推荐位图片
     */
    public function getrecom(){
        $id = I('get.recom_id');
        if (!$id) $this->ajaxReturn(['status' => false, 'msg' => '缺少关键参数']);
        $recomdata = M('recomrfile')->where(['recom_id'=>$id,'isshow'=>1])->order('orders')->select();
        if(!empty($recomdata)){
            foreach ($recomdata as $k=>$v){
                $recomdata[$k]['file_path'] = M('file')->where(['file_id'=>$v['file_id']])->find()['file_path'];
            }
        }
        if($recomdata){
            $this->ajaxreturn(['recomdata'=>$recomdata,'status'=>true]);
        }else{
            $this->ajaxreturn(['status'=>false]);
        }
    }
}