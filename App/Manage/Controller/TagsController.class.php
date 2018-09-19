<?php
/**
 * Created by PhpStorm.
 * User: lqs
 * Date: 2018/8/17
 * Time: 11:28
 */

namespace Manage\Controller;


use Manage\Model\TagsModel;

class TagsController extends BaseController
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
    public function add(){
        if (IS_POST && IS_AJAX)// 新增
        {
            $par = I('post.');// 参数接收

//            if (!$par['pid'] || $par['pid'] !=0) $this->ajaxReturn(['status'=>false,'msg'=>'父分类不能为空']);// 参数过滤
            if (!$par['tagname']) $this->ajaxReturn(['status'=>false,'msg'=>'标签名不能为空']);// 参数过滤
            // TODO 数据验证
//            $par['create_user'] = session('xy_manager')['name'];// 创建者
//            $par['create_time'] = time();// 更新时间

            try{
                $res = (new TagsModel())->add($par);// 新增
            }catch (\Exception $exception){
                $this->ajaxReturn(['status'=>false,'msg'=>$exception->getMessage()]);// 捕获异常
            }

            $this->ajaxReturn(['status'=>true,'msg'=>'新增成功!']);// 更新成功
        }

//        $newscateModel = new TagsModel();
//        $catedata = $newscateModel->select();
//        $this->assign('catedata',$catedata);
        $this->display();
    }



    /**
     * 列表接口
     */
    public function search(){
        if (IS_AJAX)
        {
            $catemodel = new TagsModel();
            $page = I('get.page');
            $offset = I('get.limit');
//            $nowpage = ($page-1)*$offset;
            $catedata = $catemodel->limit(($page - 1) * $offset, $offset)->select();
            $catedatas = $catemodel->select();
//            $catedata = $this->tree($catedata,0,0);
//            foreach ($catedata as $k=>$v){
//                $catedata[$k]['catename'] = str_repeat('&nbsp;&nbsp;&nbsp;',$v['level']) . $v['catename'];
//            }
            $data['data'] = $catedata;
            $data['code'] = 0;
            $data['msg']='';
            $data['count'] = count($catedatas);
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
            $res = (new TagsModel())->where(['id'=>['IN',$ids]])->delete();// 删除
        }catch (\Exception $exception){
            $this->ajaxReturn(['status'=>false,'msg'=>$exception->getMessage()]);// 捕获异常
        }

        if (!$res) $this->ajaxReturn(['status'=>false,'msg'=>'系统维护,请稍后再试!']);// 删除失败

        $this->ajaxReturn(['status'=>true,'msg'=>'删除成功!']);// 删除成功
    }

    /**
     * 删除
     */
    public function teacherDelete()
    {
        $ids = explode(',',ltrim(I('post.ids'),','));// 参数接收

        if (!$ids) $this->ajaxReturn(['status'=>false,'msg'=>'缺少关键参数']);// 缺少关键参数

        try{
            $res = (new CallTeacherModel())->where(['id'=>['IN',$ids]])->delete();// 删除
        }catch (\Exception $exception){
            $this->ajaxReturn(['status'=>false,'msg'=>$exception->getMessage()]);// 捕获异常
        }

        if (!$res) $this->ajaxReturn(['status'=>false,'msg'=>'系统维护,请稍后再试!']);// 删除失败

        $this->ajaxReturn(['status'=>true,'msg'=>'删除成功!']);// 删除成功
    }

    /**
     * 更新状态
     */
    public function teacherChangestatus()
    {
        $ids = explode(',',ltrim(I('post.ids'),','));// 参数接收

        if (!$ids) $this->ajaxReturn(['status'=>false,'msg'=>'缺少关键参数']);// 缺少关键参数

        if (I('post.target_status'))
        {
            $change = ['status'=>1];
        }else{
            $change = ['status'=>0];
        }

        $change['update_user'] = session('xy_manager')['id'];// 更新者
        $change['update_time'] = time();// 更新时间

        try{
            $res = (new CallTeacherModel())->where(['id'=>['IN',$ids]])->save($change);// 更新
        }catch (\Exception $exception){
            $this->ajaxReturn(['status'=>false,'msg'=>$exception->getMessage()]);// 捕获异常
        }

//        if (!$res) $this->ajaxReturn(['status'=>false,'msg'=>'系统维护,请稍后再试!']);// 更新失败

        $this->ajaxReturn(['status'=>true,'msg'=>'更新成功!']);//更新成功
    }

    /**
     * 学员列表页面
     */
    public function studentShow()
    {
        $this->display();
    }

    /**
     * 打Call日志列表页面
     */
    public function calllog()
    {
        $this->display();
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