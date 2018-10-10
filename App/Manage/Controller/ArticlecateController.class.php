<?php
/**
 * Created by PhpStorm.
 * User: lqs
 * Date: 2018/8/17
 * Time: 11:28
 */

namespace Manage\Controller;

use Manage\Model\NewscateModel;

class ArticlecateController extends BaseController
{
    /**
     * 分类列表页
     */
    public function index()
    {

        $this->display();
    }

    /**
     * 新增分类
     */
    public function addcate(){
        if (IS_POST && IS_AJAX)// 新增
        {
            $par = I('post.');// 参数接收

//            if (!$par['pid'] || $par['pid'] !=0) $this->ajaxReturn(['status'=>false,'msg'=>'父分类不能为空']);// 参数过滤
            if (!$par['name']) $this->ajaxReturn(['status'=>false,'msg'=>'分类名不能为空']);// 参数过滤
            // TODO 数据验证
//            $par['create_user'] = session('xy_manager')['id'];// 更新者
            $par['createdTime'] = time();// 更新时间

            try{
                $res = M('articleCategory')->add($par);// 新增
            }catch (\Exception $exception){
                $this->ajaxReturn(['status'=>false,'msg'=>$exception->getMessage()]);// 捕获异常
            }


            $this->ajaxReturn(['status'=>true,'msg'=>'新增成功!']);// 更新成功
        }
        $country = M('country')->select();
        $this->country = $country;
        $this->display();
    }
    /**
     * 分类修改
     */
    public function editcate(){
        if (IS_POST && IS_AJAX)// 如果修改
        {
            $par = I('post.');// 参数接收
            if (!$par['ids']) $this->ajaxReturn(['status'=>false,'msg'=>'缺少关键参数']);// 缺少关键参数
            if($par['ids'] == $par['pid']){
                $this->ajaxReturn(['status'=>false,'msg'=>'无法选择当前分类']);// 捕获异常
            }

            // 执行更新操作
            try{
                $res = M('articleCategory')->where(['id'=>$par['ids']])->save($par);// 更新
            }catch (\Exception $exception){
                $this->ajaxReturn(['status'=>false,'msg'=>$exception->getMessage()]);// 捕获异常
            }

//        if (!$res) $this->ajaxReturn(['status'=>false,'msg'=>'系统维护,请稍后再试!']);// 更新失败

            $this->ajaxReturn(['status'=>true,'msg'=>'修改成功!']);// 更新成功
        }

        // 展示修改页面
        $id = ltrim(I('get.id'),',');// 参数接收

        if (!$id) exit('缺少关键参数');// 缺少关键参数

        $info = M('articleCategory')->find($id);// 获取要修改的数据
//var_dump($info);
        if (!$info)  exit('数据不存在,当前数据可能已被删除');// 数据不存在
        $catemodel = M('articleCategory');
        $catedata = $catemodel->select();
        $catedata = $this->tree($catedata,0,0);
        foreach ($catedata as $k=>$v){
            if($v['level']!=0){
                $catedata[$k]['name'] = str_repeat('&nbsp;&nbsp;&nbsp;',$v['level'])  . '|__' .  $catedata[$k]['name'];

            }
//            $catedata[$k]['catename'] = str_repeat('&nbsp;&nbsp;&nbsp;',$v['level']) . $v['catename'];
        }
        $this->country = M('country')->select();
        $this->assign('catedata',$catedata);
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
            $catemodel = M('articleCategory');
            $page = I('get.page');
            $offset = I('get.limit');
            $start = ($page-1)*$offset;
            $end = ($page-1)*$offset+$offset;
            $catedatas = $catemodel->select();
            $catedata = $this->tree($catedatas,0,0);
//            $this->ajaxReturn($catedata);
            foreach ($catedata as $k=>$v){
//                $catedata[$k]['catename'] = str_repeat('&nbsp;&nbsp;&nbsp;',$v['level']) . $v['catename'];
                if($v['level']!=0){
                    $catedata[$k]['name'] = str_repeat('&nbsp;&nbsp;&nbsp;',$v['level'])  . '|__' .  $catedata[$k]['name'];
                }
                if($k<$start || $k>$end-1 ){
                    unset($catedata[$k]);
                }
            }
//            ksort($catedata);
//            $catedata = $catemodel->limit(($page - 1) * $offset, $offset)->select();
            $data['data'] = $catedata;
            $data['alldata'] = $catedatas;
            $data['code'] = 0;
            $data['msg']='';
            $data['count'] = count($catedatas);
            $this->ajaxReturn($data);
        }
    }

    public function getallcate(){
        if(IS_AJAX){
            $catemodel = M('articleCategory');
            $res = [];
            $pid = I('get.pid');
            $cid = I('get.id');
            if(!empty($pid)){
                $newsmodel =  M('articleCategory');
                $pdata = $newsmodel->where(['id'=>$pid,'countryid'=>$cid])->find();
                $pdata['name'] = $pdata['name'];
                $res['is_checked'] = $pdata;
            }
            $catedata = $catemodel->where(['countryid'=>$cid])->select();
//            $this->ajaxreturn($catedata);
            foreach ($catedata as $k=>$v){
                $catedata[$k]['name'] = $v['name'];
            }
            $data= getPreTree($catedata);
            $res['data'] = $data;
            $this->ajaxReturn($res);
        }
    }


    /**
     * 修改资讯
     */
    public function newsEdit()
    {

        if (IS_POST && IS_AJAX)// 如果修改
        {
            $par = I('post.');// 参数接收

            if (!$par['id']) $this->ajaxReturn(['status'=>false,'msg'=>'缺少关键参数']);// 缺少关键参数

            if (!$par['name']) $this->ajaxReturn(['status'=>false,'msg'=>'姓名不能为空']);// 参数过滤

            // TODO 数据验证
            $par['update_user'] = session('xy_manager')['id'];// 更新者
            $par['update_time'] = time();// 更新时间

            // 执行更新操作
            try{
                $res = (new CallTeacherModel())->save($par);// 更新
            }catch (\Exception $exception){
                $this->ajaxReturn(['status'=>false,'msg'=>$exception->getMessage()]);// 捕获异常
            }

//        if (!$res) $this->ajaxReturn(['status'=>false,'msg'=>'系统维护,请稍后再试!']);// 更新失败

            $this->ajaxReturn(['status'=>true,'msg'=>'修改成功!']);// 更新成功
        }

        // 展示修改页面
        $id = ltrim(I('get.id'),',');// 参数接收

        if (!$id) exit('缺少关键参数');// 缺少关键参数

        $info = (new CallTeacherModel())->find($id);// 获取要修改的数据

        if (!$info)  exit('数据不存在,当前数据可能已被删除');// 数据不存在

        $this->info = $info;// 分配数据到模板

        $this->display();// 展示模板
    }


    /**
     * 删除分类
     */
    public function deletecate()
    {
        $ids = explode(',',ltrim(I('post.ids'),','));// 参数接收

        if (!$ids) $this->ajaxReturn(['status'=>false,'msg'=>'缺少关键参数']);// 缺少关键参数
        $articledata = M('article')->where(['categoryId'=>['in',$ids]])->select();
//        $this->ajaxReturn($articledata);
        if($articledata){
            $this->ajaxReturn(['status'=>false,'msg'=>'该分类下有数据，无法删除！']);//
        }
        try{
            $res = M('articleCategory')->where(['id'=>['IN',$ids]])->delete();// 删除
        }catch (\Exception $exception){
            $this->ajaxReturn(['status'=>false,'msg'=>$exception->getMessage()]);// 捕获异常
        }

        if (!$res) $this->ajaxReturn(['status'=>false,'msg'=>'系统维护,请稍后再试!']);// 删除失败

        $this->ajaxReturn(['status'=>true,'msg'=>'删除成功!']);// 删除成功
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