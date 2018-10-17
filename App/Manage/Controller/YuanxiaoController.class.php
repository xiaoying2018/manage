<?php

namespace Manage\Controller;

use Think\Log;

class YuanxiaoController extends BaseController
{
    public function __construct()
    {
        parent::__construct();

        header("Content-type:text/html;charset=utf-8");
    }

    public function index()
    {
        $this->display();
    }


    public function showAddYuanXiao()
    {
        $yuanxiao_model=D('Yuanxiao');
        $country = $yuanxiao_model->country;
        $property = $yuanxiao_model->property;
        $category = $yuanxiao_model->category;
        $location = $yuanxiao_model->location;
        $region = $yuanxiao_model->region;
        $this->assign('data',compact('country','property','category','location','region'));
        $this->display();
    }

    public function addYuanXiao()
    {
        $yuanxiao_model=D('Yuanxiao');
        if (!$data=$yuanxiao_model->create()){
            // 如果创建失败 表示验证没有通过 输出错误提示信息
            $this->ajaxReturn(array(
                'status'=>false,
                'msg'=>$yuanxiao_model->getError()
            ));
        }else{
            Log::write(json_encode($data));
            // 验证通过 可以进行其他数据操作
            $yuanxiao_model->add($data);
            $this->ajaxReturn(array(
                'status'=>true
            ));
        }
    }


    public function search()
    {
        $yuanxiao_model=D('Yuanxiao');
        $country = $yuanxiao_model->country;
        $property = $yuanxiao_model->property;
        $category = $yuanxiao_model->category;
        $location = $yuanxiao_model->location;
        $region = $yuanxiao_model->region;

        $page = I('get.page');
        $offset = I('get.limit');

        $where = array();
        $where['status']=1;
        if(!empty($searchname = I('get.search_key'))){
            $where['name'] = array('like',array('%' . $searchname . '%'));
        }
        $count = $yuanxiao_model->where($where)->count();
        $yuanxiao = $yuanxiao_model->where($where)->limit(($page - 1) * $offset, $offset)->select();
        $data['data'] = $yuanxiao;
        $data['code'] = 0;
        $data['msg']='';
        $data['count'] = $count;
        $data['more']=compact('country','property','category','location','region');
        $this->ajaxReturn($data);
    }


    public function deleteYuanXiao()
    {
        $ids = explode(',',ltrim(I('post.ids'),','));// 参数接收
        if (!$ids) $this->ajaxReturn(['status'=>false,'msg'=>'缺少关键参数']);// 缺少关键参数
        $yuanxiao_model=D('Yuanxiao');
        $schoolids = array_column($yuanxiao_model->where(['id'=>['IN',$ids]])->select(),'id');
        $res = $yuanxiao_model->where(['id'=>['IN',$ids]])->save(['status'=>0]);// 软删除
        if (!$res) $this->ajaxReturn(['status'=>false,'msg'=>'系统维护,请稍后再试!']);// 删除失败
        $this->ajaxReturn(['status'=>true,'msg'=>'删除成功!']);// 删除成功
    }


    public function showEditYuanXiao()
    {
        $id = trim(I('get.id'),',');
        if (!$id) die('缺少关键参数');// 缺少关键参数
        $yuanxiao_model=D('Yuanxiao');
        $country = $yuanxiao_model->country;
        $property = $yuanxiao_model->property;
        $category = $yuanxiao_model->category;
        $location = $yuanxiao_model->location;
        $region = $yuanxiao_model->region;
        $subject_category = $yuanxiao_model->subject_category;
        $img_category = $yuanxiao_model->img_category;
        $yuanxiao =$yuanxiao_model->where(['id'=>['eq',$id]])->find();
        if(!$yuanxiao){
            die('无此院校信息');// 缺少关键参数
        }


        $this->assign('data',compact('yuanxiao','country','property','category','location','region','subject_category','img_category'));
        $this->display();
    }


    public function updateYuanXiao()
    {
        $yuanxiao_model=D('Yuanxiao');
        $id=I('post.yuanxiao_id');
        if (!$data=$yuanxiao_model->create()){
            // 如果创建失败 表示验证没有通过 输出错误提示信息
            $this->ajaxReturn(array(
                'status'=>false,
                'msg'=>$yuanxiao_model->getError()
            ));
        }else{
            // 验证通过 可以进行其他数据操作
            $yuanxiao_model->where(['id'=>$id])->save($data);
            Log::write($yuanxiao_model->getLastSql());
            $this->ajaxReturn(array(
                'status'=>true
            ));
        }
    }


    public function addYuanXiaoImage()
    {
        $yuanxiao_iamge_model=D('YuanxiaoImage');

        $yuanxiao_id=I('post.yuanxiao_id');
        $yuanxiao=D('yuanxiao')->field('sysno,name')->where(['id'=>$yuanxiao_id])->find();
        $data['schoolsysno']=$yuanxiao['sysno'];
        $data['schoolname']=$yuanxiao['name'];
        $data['imagepath']=I('post.image_path');
        $yuanxiao_iamge_model->add($data);
        $this->ajaxReturn(array(
            'status'=>true
        ));

    }


    public function upload_image_lists()
    {
        $yuanxiao_img_model=D('YuanxiaoImage');
        $yuanxiao_model=D('Yuanxiao');
        $img_category = $yuanxiao_model->img_category;
        $page = I('get.page');
        $offset = I('get.limit');

        $where = array();
        $id=I('get.yuanxiao_id');
        $sysno=$yuanxiao_model->where(['id'=>$id])->getField('sysno');
        $where['schoolsysno']=$sysno;
        $count = $yuanxiao_img_model->where($where)->count();
        $yuanxiao = $yuanxiao_img_model->where($where)->limit(($page - 1) * $offset, $offset)->order('id desc')->select();

        $data['data'] = $yuanxiao;
        $data['code'] = 0;
        $data['msg']='';
        $data['count'] = $count;
        $data['more']=compact('img_category');
        $this->ajaxReturn($data);
    }

    public function deleteYuanXiaoImage()
    {
        $ids = explode(',',ltrim(I('post.ids'),','));// 参数接收
        if (!$ids) $this->ajaxReturn(['status'=>false,'msg'=>'缺少关键参数']);// 缺少关键参数
        $yuanxiao_model=D('YuanxiaoImage');
        $schoolids = array_column($yuanxiao_model->where(['id'=>['IN',$ids]])->select(),'id');
        $res = $yuanxiao_model->where(['id'=>['IN',$ids]])->delete();// 软删除
        if (!$res) $this->ajaxReturn(['status'=>false,'msg'=>'系统维护,请稍后再试!']);// 删除失败
        $this->ajaxReturn(['status'=>true,'msg'=>'删除成功!']);// 删除成功
    }


    public function addYuanXiaoZhuanye()
    {
        $yuanxiao_iamge_model=D('YuanxiaoZhuanye');
        if (!$data=$yuanxiao_iamge_model->create()){
            // 如果创建失败 表示验证没有通过 输出错误提示信息
            $this->ajaxReturn(array(
                'status'=>false,
                'msg'=>$yuanxiao_iamge_model->getError()
            ));
        }else{
            // 验证通过 可以进行其他数据操作
            $yuanxiao_id=I('post.yuanxiao_id');
            $yuanxiao=D('yuanxiao')->field('sysno,name')->where(['id'=>$yuanxiao_id])->find();
            $data['schoolsysno']=$yuanxiao['sysno'];
            $data['schoolname']=$yuanxiao['name'];
            $yuanxiao_iamge_model->add($data);
            $this->ajaxReturn(array(
                'status'=>true
            ));
        }
    }

    public function yuanxiao_zhuanye_lists()
    {
        $yuanxiao_img_model=D('YuanxiaoZhuanye');
        $yuanxiao_model=D('Yuanxiao');
        $subject_category = $yuanxiao_model->subject_category;
        $page = I('get.page');
        $offset = I('get.limit');
        $where = array();
        $id=I('get.yuanxiao_id');
        $sysno=$yuanxiao_model->where(['id'=>$id])->getField('sysno');
        $where['schoolsysno']=$sysno;
        $count = $yuanxiao_img_model->where($where)->count();
        $yuanxiao = $yuanxiao_img_model->where($where)->limit(($page - 1) * $offset, $offset)->order('id desc')->select();
        $data['data'] = $yuanxiao;
        $data['code'] = 0;
        $data['msg']='';
        $data['count'] = $count;
        $data['more']=compact('subject_category');
        $this->ajaxReturn($data);
    }


    public function deleteYuanXiaoZhuanye()
    {
        $ids = explode(',',ltrim(I('post.ids'),','));// 参数接收
        if (!$ids) $this->ajaxReturn(['status'=>false,'msg'=>'缺少关键参数']);// 缺少关键参数
        $yuanxiao_model=D('YuanxiaoZhuanye');
        $schoolids = array_column($yuanxiao_model->where(['id'=>['IN',$ids]])->select(),'id');
        $res = $yuanxiao_model->where(['id'=>['IN',$ids]])->delete();// 软删除
        Log::write(json_encode($yuanxiao_model->getLastSql()));
        if (!$res) $this->ajaxReturn(['status'=>false,'msg'=>'系统维护,请稍后再试!']);// 删除失败
        $this->ajaxReturn(['status'=>true,'msg'=>'删除成功!']);// 删除成功
    }



    public function showInfoYuanXiao()
    {
        $id=I('get.id');
        if(!$id) die('缺少关键参数');
        $yuanxiao_model=D('Yuanxiao');
        $yuanxiao=$yuanxiao_model->where(['id'=>$id])->find();
        $this->assign('data',compact('yuanxiao'));
        $this->display();
    }


    public function getYuanXiaoLists()
    {
        $yuanxiao_model=D('Yuanxiao');
        $country = $yuanxiao_model->country;
        $property = $yuanxiao_model->property;
        $category = $yuanxiao_model->category;
        $location = $yuanxiao_model->location;
        $region = $yuanxiao_model->region;

        $page = I('get.page');
        $offset = I('get.limit');

        $where = array();
        $where['status']=1;
        if(!empty($searchname = I('get.search_key'))){
            $where['name_cn'] = array('like',array('%' . $searchname . '%'));
        }
        $count = $yuanxiao_model->where($where)->count();
        $yuanxiao = $yuanxiao_model->where($where)->limit(($page - 1) * $offset, $offset)->select();
        $data['data'] = $yuanxiao;
        $data['code'] = 0;
        $data['msg']='';
        $data['count'] = $count;
        $data['more']=compact('country','property','category','location','region');
        $this->ajaxReturn($data);
    }





}