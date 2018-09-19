<?php
/**
 * Created by PhpStorm.
 * User: lqs
 * Date: 2018/8/17
 * Time: 11:28
 */

namespace Manage\Controller;

use Manage\Model\NewscateModel;

class NewsController extends BaseController
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
            if (!$par['catename']) $this->ajaxReturn(['status'=>false,'msg'=>'分类名不能为空']);// 参数过滤
            // TODO 数据验证
            $par['create_user'] = session('xy_manager')['id'];// 更新者
            $par['create_time'] = time();// 更新时间

            try{
                $res = (new NewscateModel())->add($par);// 新增
            }catch (\Exception $exception){
                $this->ajaxReturn(['status'=>false,'msg'=>$exception->getMessage()]);// 捕获异常
            }


            $this->ajaxReturn(['status'=>true,'msg'=>'新增成功!']);// 更新成功
        }

        $newscateModel = new NewscateModel();
        $catedata = $newscateModel->select();
        $catedata = $this->tree($catedata,0,0);
        foreach ($catedata as $k=>$v){
//            $catedata[$k]['catename'] =  str_repeat('__',$v['level']) . $v['catename'];
            if($v['level']!=0){
                $catedata[$k]['catename'] = str_repeat('&nbsp;&nbsp;&nbsp;',$v['level'])  . '|__' .  $catedata[$k]['catename'];
                $catedata[$k]['catenames'] = str_repeat('&nbsp;&nbsp;&nbsp;',$v['level'])  . '|__' .  $catedata[$k]['catename'];
            }
        }
        $this->assign('catedata',$catedata);
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


            // 执行更新操作
            try{
                $res = (new NewscateModel())->where(['id'=>$par['ids']])->save($par);// 更新
            }catch (\Exception $exception){
                $this->ajaxReturn(['status'=>false,'msg'=>$exception->getMessage()]);// 捕获异常
            }

//        if (!$res) $this->ajaxReturn(['status'=>false,'msg'=>'系统维护,请稍后再试!']);// 更新失败

            $this->ajaxReturn(['status'=>true,'msg'=>'修改成功!']);// 更新成功
        }

        // 展示修改页面
        $id = ltrim(I('get.id'),',');// 参数接收

        if (!$id) exit('缺少关键参数');// 缺少关键参数

        $info = (new NewscateModel())->find($id);// 获取要修改的数据

        if (!$info)  exit('数据不存在,当前数据可能已被删除');// 数据不存在
        $catemodel = new NewscateModel();
        $catedata = $catemodel->select();
        $catedata = $this->tree($catedata,0,0);
        foreach ($catedata as $k=>$v){
            if($v['level']!=0){
                $catedata[$k]['catename'] = str_repeat('&nbsp;&nbsp;&nbsp;',$v['level'])  . '|__' .  $catedata[$k]['catename'];

            }
//            $catedata[$k]['catename'] = str_repeat('&nbsp;&nbsp;&nbsp;',$v['level']) . $v['catename'];
        }

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
            $catemodel = new NewscateModel();
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
                    $catedata[$k]['catename'] = str_repeat('&nbsp;&nbsp;&nbsp;',$v['level'])  . '|__' .  $catedata[$k]['catename'];
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
            $catemodel = new NewscateModel();
            $res = [];
            $pid = I('get.pid');
            if(!empty($pid)){
                $newsmodel =  new NewscateModel();
                $pdata = $newsmodel->where(['id'=>$pid])->find();
                $pdata['name'] = $pdata['catename'];
                $res['is_checked'] = $pdata;
            }
            $catedata = $catemodel->select();
            foreach ($catedata as $k=>$v){
                $catedata[$k]['name'] = $v['catename'];
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

        try{
            $res = (new NewscateModel())->where(['id'=>['IN',$ids]])->delete();// 删除
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