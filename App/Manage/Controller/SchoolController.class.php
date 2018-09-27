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
use Manage\Model\SchoolModel;
use Manage\Model\TagsModel;

class SchoolController extends BaseController
{
    /**
     * 分类列表页
     */
    public function index()
    {

        $this->display();
    }


    /**
     * 新增语言学校
     */
    public function add1(){

        if (IS_POST && IS_AJAX)// 新增
        {
            $par = I('post.');// 参数接收
            $fileids = trim($par['fileids'],',');
            if(!empty($fileids)){
                $filearr = explode(',',$fileids);
            }
//            $this->ajaxReturn($par);
            if($par['xingzhiid']==50){
                $par['xingzhi_name'] = '国立';
            }else if($par['xingzhiid']==51){
                $par['xingzhi_name'] = '公立';
            }else if($par['xingzhiid']==52){
                $par['xingzhi_name'] = '私立';
            }
            $par['country'] = 2;
            $par['category_school'] = '日本语言学校';
            $par['country_name'] = '日本';
            $par['leixingid'] = 89;
            $par['status'] = 1;

            try{
                $res = (new SchoolModel())->add($par);// 新增
                if($res){
                    $schooltype = M('schoolType')->add(['school_id'=>$res,'type_id'=>89]);
                    foreach ($filearr as $k=>$v){
                        $add['file_id'] = $v;
                        $add['school_id'] = $res;
                        $add['type'] = 1;
                        M('schoolrfile')->add($add);
                    }
                }
            }catch (\Exception $exception){
                $this->ajaxReturn(['status'=>false,'msg'=>$exception->getMessage()]);// 捕获异常
            }

            $this->ajaxReturn(['status'=>true,'msg'=>'新增成功!']);// 更新成功
        }
        $cdata = M('city')->where(['pid'=>2])->select();
        $this->assign('cdata',$cdata);
        $this->display();
    }
    /**
     * 新增日本大学
     */
    public function add2(){
        if (IS_POST && IS_AJAX)// 新增
        {
            $par = I('post.');// 参数接收
            $fileids = trim($par['fileids'],',');
            if(!empty($fileids)){
                $filearr = explode(',',$fileids);
            }
            if($par['xingzhiid']==50){
                $par['xingzhi_name'] = '国立';
            }else if($par['xingzhiid']==51){
                $par['xingzhi_name'] = '公立';
            }else if($par['xingzhiid']==52){
                $par['xingzhi_name'] = '私立';
            }
            $par['country'] = 2;
            $par['category_school'] = '日本大学';
            $par['country_name'] = '日本';
            $par['status'] = 1;
            try{
                $res = (new SchoolModel())->add($par);// 新增
                if($res && !empty($par['leixingid'])){
                    $schooltype = M('schoolType')->add(['school_id'=>$res,'type_id'=>$par['leixingid']]);
                    foreach ($filearr as $k=>$v){
                        $add['file_id'] = $v;
                        $add['school_id'] = $res;
                        $add['type'] = 1;
                        M('schoolrfile')->add($add);
                    }
                }
            }catch (\Exception $exception){
                $this->ajaxReturn(['status'=>false,'msg'=>$exception->getMessage()]);// 捕获异常
            }

            $this->ajaxReturn(['status'=>true,'msg'=>'新增成功!']);// 更新成功
        }
        $cdata = M('city')->where(['pid'=>2])->select();
        $this->assign('cdata',$cdata);
        $this->display();
    }

    /**
     * 修改语言学校
     */
    public function edit1(){
        if (IS_POST && IS_AJAX)// 如果修改
        {
            $par = I('post.');// 参数接收

            if (!$par['ids']) $this->ajaxReturn(['status'=>false,'msg'=>'缺少关键参数']);// 缺少关键参数
            // 执行更新操作
            $fileids = trim($par['fileids'],',');
            if(!empty($fileids)){
                $filearr = explode(',',$fileids);
            }
            try{
                $delf = M('schoolrfile')->where(['school_id'=>$par['ids']])->delete();
                $res = (new SchoolModel())->where(['id'=>$par['ids']])->save($par);// 更新
                if($res!==false){
                    foreach ($filearr as $k=>$v){
                        $add['file_id'] = $v;
                        $add['school_id'] = $par['ids'];
                        $add['type'] = 1;
                        M('schoolrfile')->add($add);
                    }
                }
            }catch (\Exception $exception){
                $this->ajaxReturn(['status'=>false,'msg'=>$exception->getMessage()]);// 捕获异常
            }

            $this->ajaxReturn(['status'=>true,'msg'=>'修改成功!']);// 更新成功
        }

        // 展示修改页面
        $id = ltrim(I('get.id'),',');// 参数接收

        if (!$id) exit('缺少关键参数');// 缺少关键参数

        $info = (new SchoolModel())->find($id);// 获取要修改的数据

        if (!$info)  exit('数据不存在,当前数据可能已被删除');// 数据不存在
//        var_dump($info);
        $this->info = $info;// 分配数据到模板
        $this->id = $id;
        $fileids = array_column(M('schoolrfile')->where(['school_id'=>$info['id'],'type'=>1])->select(),'file_id');
        if(!empty($fileids)){
            $filedata = M('file')->where(['file_id'=>['in',$fileids]])->select();
        }
//        var_dump($filedata);
        $this->fileids = implode(',',$fileids);
        $this->filedata = $filedata;
        $cdata = M('city')->where(['pid'=>2])->select();
        $paid = M('city')->where(['id'=>$info['nowcid']])->find();
        $xiandata = M('city')->where(['pid'=>$paid['pid']])->select();
//        var_dump($xiandata);
        $this->xiandata = $xiandata;
        if($paid['pid']!=2 || $paid['pid']!=0){
            $this->paid = $paid;
        }
//$this->assign('dd',M('tt')->where(['id'=>26])->find());
        $this->assign('cdata',$cdata);
        $this->display();// 展示模板
    }

    /**
     * 修改日本学校
     */
    public function edit2(){
        if (IS_POST && IS_AJAX)// 如果修改
        {
            $par = I('post.');// 参数接收
//$this->ajaxReturn($par);
            if (!$par['ids']) $this->ajaxReturn(['status'=>false,'msg'=>'缺少关键参数']);// 缺少关键参数
            // 执行更新操作
            $fileids = trim($par['fileids'],',');
            if(!empty($fileids)){
                $filearr = explode(',',$fileids);
            }
            try{
                $delf = M('schoolrfile')->where(['school_id'=>$par['ids']])->delete();
                $res = (new SchoolModel())->where(['id'=>$par['ids']])->save($par);// 更新
                if($res!==false){
                    foreach ($filearr as $k=>$v){
                        $add['file_id'] = $v;
                        $add['school_id'] = $par['ids'];
                        $add['type'] = 1;
                        M('schoolrfile')->add($add);
                    }
                }
            }catch (\Exception $exception){
                $this->ajaxReturn(['status'=>false,'msg'=>$exception->getMessage()]);// 捕获异常
            }

            $this->ajaxReturn(['status'=>true,'msg'=>'修改成功!']);// 更新成功
        }

        // 展示修改页面
        $id = ltrim(I('get.id'),',');// 参数接收

        if (!$id) exit('缺少关键参数');// 缺少关键参数

        $info = (new SchoolModel())->find($id);// 获取要修改的数据

        if (!$info)  exit('数据不存在,当前数据可能已被删除');// 数据不存在
//        var_dump($info);
        $this->info = $info;// 分配数据到模板
        $this->id = $id;
        $fileids = array_column(M('schoolrfile')->where(['school_id'=>$info['id'],'type'=>1])->select(),'file_id');
        if(!empty($fileids)){
            $filedata = M('file')->where(['file_id'=>['in',$fileids]])->select();
        }
//        var_dump($filedata);
        $this->fileids = implode(',',$fileids);
        $this->filedata = $filedata;
        $cdata = M('city')->where(['pid'=>2])->select();
        $paid = M('city')->where(['id'=>$info['nowcid']])->find();
        $xiandata = M('city')->where(['pid'=>$paid['pid']])->select();
//        var_dump($xiandata);
        $this->xiandata = $xiandata;
        if($paid['pid']!=2 || $paid['pid']!=0){
            $this->paid = $paid;
        }

        $this->assign('cdata',$cdata);
        $this->display();// 展示模板
    }


    /**
     * 删除语言学校
     */
    public function delete1()
    {
        $ids = explode(',',ltrim(I('post.ids'),','));// 参数接收
//        $this->ajaxReturn($ids);
        if (!$ids) $this->ajaxReturn(['status'=>false,'msg'=>'缺少关键参数']);// 缺少关键参数
        $smodel = new SchoolModel();
        try{
            $schoolids = array_column($smodel->where(['id'=>['IN',$ids]])->select(),'id');
            M('schoolrfile')->where(['school_id'=>['in',$schoolids],'type'=>1])->delete();
            $res = (new SchoolModel())->where(['id'=>['IN',$ids]])->delete();// 删除
        }catch (\Exception $exception){
            $this->ajaxReturn(['status'=>false,'msg'=>$exception->getMessage()]);// 捕获异常
        }

        if (!$res) $this->ajaxReturn(['status'=>false,'msg'=>'系统维护,请稍后再试!']);// 删除失败

        $this->ajaxReturn(['status'=>true,'msg'=>'删除成功!']);// 删除成功
    }


    /**
     * 列表分类接口
     */
    public function search(){

        $schoolmodel = new SchoolModel();
        $page = I('get.page');
        $offset = I('get.limit');

        $where = [];
        if(!empty($searchname = I('get.search_key'))){
            $where['name_cn'] = ['like',['%' . $searchname . '%']];
        }

        $count = $schoolmodel->where($where)->count();

        $schlooldata = $schoolmodel->where($where)->limit(($page - 1) * $offset, $offset)->select();
        $tag = [];
        $data['data'] = $schlooldata;
        $data['code'] = 0;
        $data['msg']='';
        $data['count'] = $count;
        $this->ajaxReturn($data);
    }




    /**
     * 验证表单
     */
    public function getone(){
        $id = trim($_REQUEST['ids'],',');
        if(!empty($id)){
            $edata = M('school')->where(['id'=>$id])->find();
            $this->ajaxReturn(['status'=>true,'data'=>$edata]);
        }else{
            $this->ajaxReturn(['status'=>false]);
        }
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