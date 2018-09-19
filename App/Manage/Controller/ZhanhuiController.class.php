<?php
/**
 * Created by PhpStorm.
 * User: dragon
 * Date: 2018/8/2
 * Time: 14:33
 */

namespace Manage\Controller;

use Manage\Model\ZhanhuiModel;
use Manage\Model\ZhanhuiRoundModel;

class ZhanhuiController extends BaseController
{
    /**
     * 列表页显示
     */
    public function index()
    {
        $this->display();
    }

    /**
     * 新增
     */
    public function create()
    {
        // 如果新增
        if (IS_AJAX && IS_POST)
        {
            if (!$par = I('post.')) $this->ajaxReturn(['status'=>false,'msg'=>'缺少参数']);// 参数接受

            if (!$par['name']) $this->ajaxReturn(['status'=>false,'msg'=>'城市名称不能为空']);// 参数过滤

            $data = $this->validatePar($par);// 数据验证

            try{
                $res = (new ZhanhuiModel())->add($data);// 插入
            }catch (\Exception $exception){
                $this->ajaxReturn(['status'=>false,'msg'=>$exception->getMessage()]);// 捕获异常
            }

            if (!$res) $this->ajaxReturn(['status'=>false,'msg'=>'系统维护,请联系管理员']);// 如果添加失败

            $this->ajaxReturn(['status'=>true,'msg'=>'添加成功']);// 添加成功
        }

        $this->display();// 展示模板
    }

    /**
     * 修改
     */
    public function edit()
    {

        if (IS_POST && IS_AJAX)// 如果修改
        {
            $par = I('post.');// 参数接收

            if (!$par['id']) $this->ajaxReturn(['status'=>false,'msg'=>'缺少关键参数']);// 缺少关键参数

            if (!$par['name']) $this->ajaxReturn(['status'=>false,'msg'=>'城市名称不能为空']);// 参数过滤

            // TODO 数据验证
            $par['update_user'] = session('xy_manager')['id'];// 更新者
            $par['update_time'] = time();// 更新时间

            // 执行更新操作
            try{
                $res = (new ZhanhuiModel())->save($par);// 更新
            }catch (\Exception $exception){
                $this->ajaxReturn(['status'=>false,'msg'=>$exception->getMessage()]);// 捕获异常
            }

//        if (!$res) $this->ajaxReturn(['status'=>false,'msg'=>'系统维护,请稍后再试!']);// 更新失败

            $this->ajaxReturn(['status'=>true,'msg'=>'修改成功!']);// 更新成功
        }

        // 展示修改页面
        $id = ltrim(I('get.id'),',');// 参数接收

        if (!$id) exit('缺少关键参数');// 缺少关键参数

        $info = (new ZhanhuiModel())->find($id);// 获取要修改的数据

        if (!$info)  exit('数据不存在,当前数据可能已被删除');// 数据不存在

        $this->info = $info;// 分配数据到模板

        $this->display();// 展示模板
    }

    /**
     * 删除
     */
    public function delete()
    {
        $ids = explode(',',ltrim(I('post.ids'),','));// 参数接收

        if (!$ids) $this->ajaxReturn(['status'=>false,'msg'=>'缺少关键参数']);// 缺少关键参数

        try{
            $res = (new ZhanhuiModel())->where(['id'=>['IN',$ids]])->delete();// 删除
        }catch (\Exception $exception){
            $this->ajaxReturn(['status'=>false,'msg'=>$exception->getMessage()]);// 捕获异常
        }

        if (!$res) $this->ajaxReturn(['status'=>false,'msg'=>'系统维护,请稍后再试!']);// 删除失败

        $this->ajaxReturn(['status'=>true,'msg'=>'删除成功!']);// 删除成功
    }

    /**
     * 更新状态
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

        $change['update_user'] = session('xy_manager')['id'];// 更新者
        $change['update_time'] = time();// 更新时间

        try{
            $res = (new ZhanhuiModel())->where(['id'=>['IN',$ids]])->save($change);// 更新
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
        $data['update_user'] = session('xy_manager')['id'];// 更新者
        $data['update_time'] = time();// 更新时间

        return $data;
    }

    /**
     * 展会场次弹窗
     */
    public function roundView()
    {
        $city_id = I('get.city');

        if (!$city_id) die("<div style='margin: 200px;text-align:center;font-size:30px;color: #D9534F;'>缺少关键参数.</div>");// 缺少关键参数 city

        $current_city_round = (new ZhanhuiRoundModel())->where(['zhanhui_id'=>['eq',$city_id]])->order('time desc')->select();// 当前城市的展会场次列表

        // 格式化展会时间
        if ($current_city_round)
        {
            foreach ($current_city_round as $k => $v)
            {
                $current_city_round[$k]['time'] = date('Y-m-d H:i:s',$v['time']);
            }
        }

        $this->current_city_round = $current_city_round;

        $this->city_id = $city_id;
        
        $this->display();
    }

    public function roundCreate()
    {
        $par = I('post.');

        if (!$par['zhanhui_id']) $this->ajaxReturn(['status'=>false,'msg'=>'操作异常 缺少关键参数']);

        if (!$par['time']) $this->ajaxReturn(['status'=>false,'msg'=>'展会时间不能为空']);

        $par['time'] = strtotime($par['time']);

        if (!$par['addr']) $this->ajaxReturn(['status'=>false,'msg'=>'展会地址不能为空']);

        $data = $this->validatePar($par);
        
        try{
            $res = (new ZhanhuiRoundModel())->add($data);// 插入
        }catch (\Exception $exception){
            $this->ajaxReturn(['status'=>false,'msg'=>$exception->getMessage()]);// 捕获异常
        }

        if (!$res) $this->ajaxReturn(['status'=>false,'msg'=>'系统维护,请联系管理员']);// 如果添加失败

        $this->ajaxReturn(['status'=>true,'msg'=>'添加成功']);// 添加成功
    }

    /**
     * 修改
     */
    public function roundedit()
    {
        if (!IS_POST && !IS_AJAX) $this->ajaxReturn(['status'=>false,'msg'=>'非法请求!']);

        $par = I('post.');// 参数接收

        if (!$par['id']) $this->ajaxReturn(['status'=>false,'msg'=>'缺少关键参数']);// 缺少关键参数

        // TODO 数据验证
        $par['update_user'] = session('xy_manager')['id'];// 更新者
        $par['update_time'] = time();// 更新时间

        if ($par['time']) $par['time'] = strtotime($par['time']);

        // 执行更新操作
        try{
            $res = (new ZhanhuiRoundModel())->save($par);// 更新
        }catch (\Exception $exception){
            $this->ajaxReturn(['status'=>false,'msg'=>$exception->getMessage()]);// 捕获异常
        }

        $this->ajaxReturn(['status'=>true,'msg'=>'修改成功!']);// 更新成功

    }


    /**
     * 删除
     */
    public function roundDelete()
    {
        $id = I('post.id');// 参数接收

        if (!$id) $this->ajaxReturn(['status'=>false,'msg'=>'缺少关键参数']);// 缺少关键参数

        try{
            $res = (new ZhanhuiRoundModel())->where(['id'=>['eq',$id]])->delete();// 删除
        }catch (\Exception $exception){
            $this->ajaxReturn(['status'=>false,'msg'=>$exception->getMessage()]);// 捕获异常
        }

        if (!$res) $this->ajaxReturn(['status'=>false,'msg'=>'系统维护,请稍后再试!']);// 删除失败

        $this->ajaxReturn(['status'=>true,'msg'=>'删除成功!']);// 删除成功
    }

    /**
     * 更新状态
     */
    public function roundchangestatus()
    {
        $id = I('post.id');// 参数接收

        if (!$id) $this->ajaxReturn(['status'=>false,'msg'=>'缺少关键参数']);// 缺少关键参数

        if (I('post.target_status'))
        {
            $change = ['status'=>0];
        }else{
            $change = ['status'=>1];
        }

        $change['update_user'] = session('xy_manager')['id'];// 更新者
        $change['update_time'] = time();// 更新时间

        try{
            $res = (new ZhanhuiRoundModel())->where(['id'=>['eq',$id]])->save($change);// 更新
        }catch (\Exception $exception){
            $this->ajaxReturn(['status'=>false,'msg'=>$exception->getMessage()]);// 捕获异常
        }

//        if (!$res) $this->ajaxReturn(['status'=>false,'msg'=>'系统维护,请稍后再试!']);// 更新失败

        $this->ajaxReturn(['status'=>true,'msg'=>'更新成功!']);//更新成功
    }

}