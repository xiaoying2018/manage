<?php
/**
 * Created by PhpStorm.
 * User: lqs
 * Date: 2018/8/17
 * Time: 11:28
 */

namespace Manage\Controller;



use Manage\Model\ContentsModel;
use Manage\Model\NewscateModel;
use Manage\Model\NewscontentsModel;
use Manage\Model\TagsModel;

class ArticleController extends BaseController
{
    /**
     * 分类列表页
     */
    public function index()
    {

        $this->display();
    }


    /**
     * 新增资讯
     */
    public function add(){

        if (IS_POST && IS_AJAX)// 新增
        {
            $par = I('post.');// 参数接收


//            if (!$par['pid'] || $par['pid'] !=0) $this->ajaxReturn(['status'=>false,'msg'=>'父分类不能为空']);// 参数过滤
//            if (!$par['catename']) $this->ajaxReturn(['status'=>false,'msg'=>'分类名不能为空']);// 参数过滤
            // TODO 数据验证
//            $par['create_user'] = session('xy_manager')['id'];// 更新者
            $par['publishedTime'] = time();// 更新时间
//            $par['webs'] = implode(',',I('webs'));
            try{
                $res = M('article')->add($par);// 新增
            }catch (\Exception $exception){
                $this->ajaxReturn(['status'=>false,'msg'=>$exception->getMessage()]);// 捕获异常
            }


            $this->ajaxReturn(['status'=>true,'msg'=>'新增成功!']);// 更新成功
        }

        $newscateModel = new NewscateModel();
        $catedata = $newscateModel->select();
        $catedata = $this->tree($catedata,0,0);
        foreach ($catedata as $k=>$v){
            $catedata[$k]['catename'] = str_repeat('&nbsp;&nbsp;&nbsp;',$v['level']) . $v['catename'];
        }
//        var_dump($catedata);
        $this->assign('catedata',$catedata);
        $this->display();
    }
    /**
     * 修改
     */
    public function edit(){
        if (IS_POST && IS_AJAX)// 如果修改
        {
            $par = I('post.');// 参数接收

            if (!$par['ids']) $this->ajaxReturn(['status'=>false,'msg'=>'缺少关键参数']);// 缺少关键参数
            $par['updatedTime'] = time();
            // 执行更新操作
            try{
                $res = M('article')->where(['id'=>$par['ids']])->save($par);// 更新
            }catch (\Exception $exception){
                $this->ajaxReturn(['status'=>false,'msg'=>$exception->getMessage()]);// 捕获异常
            }

            $this->ajaxReturn(['status'=>true,'msg'=>'修改成功!']);// 更新成功
        }

        // 展示修改页面
        $id = ltrim(I('get.id'),',');// 参数接收

        if (!$id) exit('缺少关键参数');// 缺少关键参数
        $info = M('article')->find($id);// 获取要修改的数据
//        var_dump($info);die;
        if (!$info)  exit('数据不存在,当前数据可能已被删除');// 数据不存在
//        $catemodel = M('article');
//        $catedata = $catemodel->select();
//        $catedata = $this->tree($catedata,0,0);
//        foreach ($catedata as $k=>$v){
//            $catedata[$k]['catename'] = str_repeat('&nbsp;&nbsp;&nbsp;',$v['level']) . $v['catename'];
//        }
//        $this->assign('catedata',$catedata);
        $this->info = $info;// 分配数据到模板
        $this->id = $id;
        $this->display();// 展示模板
    }

    /**
     * 列表分类接口
     */
    public function search(){
        if (IS_AJAX)
        {
            $contentmodel = M('article');
            $page = I('get.page');
            $offset = I('get.limit');
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
            $where = [];
            if(!empty($searchname = I('get.search_key'))){
                $where['title'] = ['like',['%' . $searchname . '%']];
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
                    if(strpos($v['content'],$v1['tagname'])){
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
        }
    }




    /**
     * 删除
     */
    public function delete()
    {
        $ids = explode(',',ltrim(I('post.ids'),','));// 参数接收

        if (!$ids) $this->ajaxReturn(['status'=>false,'msg'=>'缺少关键参数']);// 缺少关键参数

        try{
            $res = M('article')->where(['id'=>['IN',$ids]])->delete();// 删除
        }catch (\Exception $exception){
            $this->ajaxReturn(['status'=>false,'msg'=>$exception->getMessage()]);// 捕获异常
        }

        if (!$res) $this->ajaxReturn(['status'=>false,'msg'=>'系统维护,请稍后再试!']);// 删除失败

        $this->ajaxReturn(['status'=>true,'msg'=>'删除成功!']);// 删除成功
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
        $data['update_user'] = session('xy_manager')['id'];// 更新者
        $data['update_time'] = time();// 更新时间

        return $data;
    }

    public function tree($rows, $pid = 0, $level = 0) {
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

}