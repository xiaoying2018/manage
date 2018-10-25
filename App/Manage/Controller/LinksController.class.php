<?php
/**
 * Created by PhpStorm.
 * User: dragon
 * Date: 2018/10/25
 * Time: 12:06
 */

namespace Manage\Controller;


use Manage\Model\LinksModel;
use Manage\Model\UserModel;

class LinksController extends BaseController
{
    /**
     * 底部友链
     */
    public function footer()
    {
        // AJAX请求返回数据
        if (IS_AJAX)
        {
            $par = I();// 获取参数

            $page = $par['page'] ?: 1;// 当前页
            $limit = $par['limit'] ?: 15;// 每页显示条数
            $start = $limit * ($page - 1);// 当前查询起始条数
            $search_key = $par['search_key'] ?: '';// 查询关键字

            $condition = [];// 准备查询条件

            if ($search_key) $condition['name'] = ['LIKE', '%' . $search_key . '%'];// 如果按关键字查询

            $links_model = new LinksModel();// 实例化

            $data = $links_model->where($condition)->limit($start, $limit)->select();// 获取数据列表

            if ($data)
            {
                foreach ($data as $k => $v)
                {
                    $data[$k]['create_time'] = date('Y-m-d H:i:s',$v['create_time']);
                    $data[$k]['create_user'] = (new UserModel())->field('name')->find($v['create_user'])['name'];
                }
            }

            $count = $links_model->where($condition)->count();// 统计数据总数

            $this->ajaxReturn([
                'code' => 0,// 0为成功
                'msg' => '',// 错误提示
                'count' => $count,// 总条数
                'data' => $data// 数据列表
            ]);
        }
        
        // 普通请求展示页面
        $this->display('footer');
    }

    /**
     * 新增底部友链
     */
    public function footerlink_create()
    {
        // AJAX请求 新增数据
        if (IS_AJAX && IS_POST)
        {
            if (!$par = I('post.')) $this->ajaxReturn(['status'=>false,'msg'=>'缺少参数']);// 参数接收

            if (!$par['name'] || !$par['link']) $this->ajaxReturn(['status'=>false,'msg'=>'链接名称|链接地址 不能为空']);// 参数过滤

            if ((new LinksModel())->where(['name'=>['eq',$par['name']]])->select()) $this->ajaxReturn(['status'=>false,'msg'=>'链接名称已存在!']);

//            if ((new LinksModel())->where(['link'=>['eq',$par['link']]])->select()) $this->ajaxReturn(['status'=>false,'msg'=>'链接地址已存在!']);

            $data = $this->validatePar($par);// 数据验证

            try{
                $res = (new LinksModel())->add($data);// 插入
            }catch (\Exception $exception){
                $this->ajaxReturn(['status'=>false,'msg'=>$exception->getMessage()]);// 捕获异常
            }

            if (!$res) $this->ajaxReturn(['status'=>false,'msg'=>'系统维护,请联系管理员']);// 如果添加失败

            $this->ajaxReturn(['status'=>true,'msg'=>'添加成功']);// 添加成功
        }

        // 普通请求展示页面
        $this->display('footer_create');
    }

    public function footerlink_edit()
    {
        // AJAX请求 新增数据
        if (IS_AJAX || IS_POST)
        {
            $par = I('post.');// 参数接收

            if (!$par['id']) $this->ajaxReturn(['status'=>false,'msg'=>'缺少关键参数']);// 缺少关键参数

            if (!$par['name'] || !$par['link']) $this->ajaxReturn(['status'=>false,'msg'=>'链接名称|链接地址 不能为空']);// 参数过滤
            
            // 获取原始数据
            $before_data = (new LinksModel())->find($par['id']);

            if (!$before_data)  $this->ajaxReturn(['status'=>false,'msg'=>'数据不存在,当前数据可能已被删除!']);// 数据不存在

            // 如果要修改名称,则需要判断新的名称是否已经存在
            if ($before_data['name'] != $par['name'])
            {
                if ((new LinksModel())->where(['name'=>['eq',$par['name']]])->select()) $this->ajaxReturn(['status'=>false,'msg'=>'链接名称已存在!']);
            }

            // TODO 数据验证

            // 执行更新操作
            try{
                $res = (new LinksModel())->save($par);// 更新
            }catch (\Exception $exception){
                $this->ajaxReturn(['status'=>false,'msg'=>$exception->getMessage()]);// 捕获异常
            }

//        if (!$res) $this->ajaxReturn(['status'=>false,'msg'=>'系统维护,请稍后再试!']);// 更新失败
            
            $this->ajaxReturn(['status'=>true,'msg'=>'修改成功!']);// 更新成功
        }
        
        // 普通请求展示页面
        $id = ltrim(I('get.id'),',');// 参数接收

        if (!$id) exit('缺少关键参数');// 缺少关键参数

        $info = (new LinksModel())->find($id);// 获取要修改的数据

        if (!$info)  exit('数据不存在,当前数据可能已被删除');// 数据不存在

        $this->info = $info;// 分配数据到模板

        $this->display('footerlink_edit');
    }

    /**
     * 更新用户状态
     */
    public function changestatus()
    {
        $ids = explode(',',ltrim(I('post.ids'),','));// 参数接收

        if (!$ids) $this->ajaxReturn(['status'=>false,'msg'=>'缺少关键参数']);// 缺少关键参数

        if (I('post.target_status'))
        {
            $change = ['status'=>1];
        }else{
            $change = ['status'=>0];
        }

        try{
            $res = (new LinksModel())->where(['id'=>['IN',$ids]])->save($change);// 更新
        }catch (\Exception $exception){
            $this->ajaxReturn(['status'=>false,'msg'=>$exception->getMessage()]);// 捕获异常
        }

//        if (!$res) $this->ajaxReturn(['status'=>false,'msg'=>'系统维护,请稍后再试!']);// 更新失败

        $this->ajaxReturn(['status'=>true,'msg'=>'更新成功!']);//更新成功
    }

    /**
     * 数据验证
     */
    private function validatePar($par=[])
    {
        $data = $par;

        // 拼接数据
        $data['create_user'] = session('xy_manager')['id'];// 创建者
        $data['create_time'] = time();// 创建时间

        return $data;
    }
}