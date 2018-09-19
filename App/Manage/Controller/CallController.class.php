<?php
/**
 * Created by PhpStorm.
 * User: dragon
 * Date: 2018/7/26
 * Time: 14:08
 */

namespace Manage\Controller;


use Manage\Model\CallTeacherModel;

class CallController extends BaseController
{
    /**
     * 名师列表页
     */
    public function index()
    {
        $this->display();
    }

    /**
     * 新增名师
     */
    public function teacherCreate()
    {
        // 如果新增
        if (IS_AJAX && IS_POST)
        {
            if (!$par = I('post.')) $this->ajaxReturn(['status'=>false,'msg'=>'缺少参数']);// 参数接受

            if (!$par['name']) $this->ajaxReturn(['status'=>false,'msg'=>'姓名不能为空']);// 参数过滤

            $data = $this->validatePar($par);// 数据验证

            if (!$data['sort']) $data['sort'] = 999;// 默认排序为最靠后

            if (!$data['call_num']) $data['call_num'] = 0;// 默认call值为0

            try{
                $res = (new CallTeacherModel())->add($data);// 插入
            }catch (\Exception $exception){
                $this->ajaxReturn(['status'=>false,'msg'=>$exception->getMessage()]);// 捕获异常
            }

            if (!$res) $this->ajaxReturn(['status'=>false,'msg'=>'系统维护,请联系管理员']);// 如果添加失败

            $this->ajaxReturn(['status'=>true,'msg'=>'添加成功']);// 添加成功
        }

        $this->display();// 展示模板
    }

    /**
     * 修改名师
     */
    public function teacherEdit()
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
}