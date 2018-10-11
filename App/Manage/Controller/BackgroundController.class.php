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

class BackgroundController extends BaseController
{
    /**
     * 分类列表页
     */
    public function index()
    {

        $this->display();
    }


    /**
     * 新增
     */
    public function add(){

        if (IS_POST && IS_AJAX)// 新增
        {
            $par = I('post.');// 参数接收
            $fileids = trim($par['fileids'],',');
            if(!empty($fileids)){
                $filearr = explode(',',$fileids);
            }
//            $this->ajaxreturn($par);
            $par['create_time'] = time();
            try{
                $res = M('bgpromote')->add($par);// 新增
                if($res){
                    foreach ($filearr as $k=>$v){
                        $add['file_id'] = $v;
                        $add['bg_id'] = $res;
//                        $add['type'] = 1;
                        M('bgrfile')->add($add);
                    }
                }
            }catch (\Exception $exception){
                $this->ajaxReturn(['status'=>false,'msg'=>$exception->getMessage()]);// 捕获异常
            }

            $this->ajaxReturn(['status'=>true,'msg'=>'新增成功!']);// 更新成功
        }
        $this->display();
    }

    /**
     * 修改
     */
    public function edit(){
        if (IS_POST && IS_AJAX)// 如果修改
        {
            $par = I('post.');// 参数接收
//$this->ajaxreturn($par);
            if (!$par['ids']) $this->ajaxReturn(['status'=>false,'msg'=>'缺少关键参数']);// 缺少关键参数
            // 执行更新操作
            $fileids = trim($par['fileids'],',');
            $par['update_time'] = time();
            if(!empty($fileids)){
                $filearr = explode(',',$fileids);
            }
            try{
                $delf = M('bgrfile')->where(['bg_id'=>$par['ids']])->delete();
                $res = M('bgpromote')->where(['id'=>$par['ids']])->save($par);// 更新
                if($res!==false){
                    foreach ($filearr as $k=>$v){
                        $add['file_id'] = $v;
                        $add['bg_id'] = $par['ids'];
                        M('bgrfile')->add($add);
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

        $info = M('bgpromote')->find($id);// 获取要修改的数据

        if (!$info)  exit('数据不存在,当前数据可能已被删除');// 数据不存在
//        var_dump($info);
        $info['headimg'] = substr($info['headimg'],1);
        $this->info = $info;// 分配数据到模板
        $this->id = $id;
        $fileids = array_column(M('bgrfile')->where(['bg_id'=>$info['id']])->select(),'file_id');

        if(!empty($fileids)){
            $filedata = M('file')->where(['file_id'=>['in',$fileids]])->select();
        }
        $this->fileids = implode(',',array_column($filedata,'file_id'));
        $this->filedata = $filedata;
        $this->display();// 展示模板
    }

    /**
     * 删除
     */
    public function delete()
    {
        $ids = explode(',',ltrim(I('post.ids'),','));// 参数接收
//        $this->ajaxReturn($ids);
        if (!$ids) $this->ajaxReturn(['status'=>false,'msg'=>'缺少关键参数']);// 缺少关键参数
        $smodel = M('bgpromote');
        try{
            $bgids = array_column($smodel->where(['id'=>['IN',$ids]])->select(),'id');
            M('bgrfile')->where(['bg_id'=>['in',$bgids]])->delete();
            $res = $smodel->where(['id'=>['IN',$ids]])->delete();// 删除
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
        if (IS_AJAX)
        {
            $schoolmodel = M('bgpromote');
            $page = I('get.page');
            $offset = I('get.limit');
            $contentdatas = $schoolmodel->select();
            $where = [];
            if(!empty($searchname = I('get.search_key'))){
                $where['title'] = ['like',['%' . $searchname . '%']];
            }

            $schlooldata = $schoolmodel->where($where)->limit(($page - 1) * $offset, $offset)->order('create_time desc')->select();
            foreach ($schlooldata as $k=>$v){
                $schlooldata[$k]['create_time'] = $v['create_time']!=''?date('Y-m-d H:i:s',$v['create_time']):'';
                $schlooldata[$k]['update_time'] = $v['update_time']!=''?date('Y-m-d H:i:s',$v['update_time']):'';
            }
            $tag = [];
            $data['data'] = $schlooldata;
            $data['code'] = 0;
            $data['msg']='';
            $data['count'] = count($contentdatas);
            $this->ajaxReturn($data);
        }
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