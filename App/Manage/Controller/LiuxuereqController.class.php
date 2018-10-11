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
use Manage\Model\SchoolkoreaModel;
use Manage\Model\SchoolModel;
use Manage\Model\TagsModel;

class LiuxuereqController extends BaseController
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
            $par['cate_id'] = $par['pid'];
//            $this->ajaxReturn($par);

//            $this->ajaxReturn([$linkurl,$newfileids]);
            $par['create_time'] = time();
            try{
                $res = M('liuxue')->add($par);// 新增
            }catch (\Exception $exception){
                $this->ajaxReturn(['status'=>false,'msg'=>$exception->getMessage()]);// 捕获异常
            }

            $this->ajaxReturn(['status'=>true,'msg'=>'新增成功!']);// 更新成功
        }
        $countrydata = M('country')->select();
//        var_dump($countrydata);
        $this->countrydata = $countrydata;
        $this->display();
    }

    /**
     * 修改
     */
    public function edit(){

        if (IS_POST && IS_AJAX)// 如果修改
        {
            $par = I('post.');// 参数接收
//            asort($par['fileids']);

            if (!$par['ids']) $this->ajaxReturn(['status'=>false,'msg'=>'缺少关键参数']);// 缺少关键参数
            // 执行更新操作
            $par['update_time'] = time();
            $par['cate_id'] = $par['pid'];
//            $this->ajaxReturn($par);
            try{
                $res = M('liuxue')->where(['id'=>$par['ids']])->save($par);// 更新
            }catch (\Exception $exception){
                $this->ajaxReturn(['status'=>false,'msg'=>$exception->getMessage()]);// 捕获异常
            }

            $this->ajaxReturn(['status'=>true,'msg'=>'修改成功!']);// 更新成功
        }

        // 展示修改页面
        $id = ltrim(I('get.id'),',');// 参数接收
        if (!$id) exit('缺少关键参数');// 缺少关键参数
        $info = M('liuxue')->find($id);// 获取要修改的数据
        if (!$info)  exit('数据不存在,当前数据可能已被删除');// 数据不存在
        $countrydata = M('country')->select();
        $this->countrydata = $countrydata;
        if(substr($info['headimg'],0,1)=='.'){
            $info['headimgs'] = substr($info['headimg'],1);
        }
        $this->info = $info;// 分配数据到模板
        $this->id = $id;
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
        $smodel = M('liuxue');

        try{
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
            $liuxuemodel = M('liuxue');
            $page = I('get.page');
            $offset = I('get.limit');
            $contentdatas = $liuxuemodel->select();
            $where = [];
            if(!empty($searchname = I('get.search_key'))){
                $where['name_cn'] = ['like',['%' . $searchname . '%']];
            }

            $schlooldata = $liuxuemodel->where($where)->limit(($page - 1) * $offset, $offset)->order('create_time desc')->select();
            foreach ($schlooldata as $k=>$v){
                $schlooldata[$k]['create_time'] = date('Y-m-d H:i:s',$v['create_time']);
                $schlooldata[$k]['update_time'] = $v['update_time']!=''?date('Y-m-d H:i:s',$v['update_time']):'';
                $schlooldata[$k]['countryname'] = M('country')->where(['id'=>$v['country_id']])->find()['name'];
                $schlooldata[$k]['catename'] = M('liuxueCate')->where(['id'=>$v['cate_id']])->find()['name'];
            }
            $tag = [];
            $data['data'] = $schlooldata;
            $data['code'] = 0;
            $data['msg']='';
            $data['count'] = count($contentdatas);
            $this->ajaxReturn($data);
        }
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

    /**
     * 分类管理
     */
    public function getallcate(){
        if(IS_AJAX){
            $catemodel = M('liuxueCate');
            $res = [];
            $pid = I('get.pid');
            if(!empty($pid)){
                $newsmodel =  M('liuxueCate');
                $pdata = $newsmodel->where(['id'=>$pid])->find();
                $pdata['name'] = $pdata['name'];
                $res['is_checked'] = $pdata;
            }
            $catedata = $catemodel->select();
            foreach ($catedata as $k=>$v){
                $catedata[$k]['name'] = $v['name'];
            }
            $data= getPreTree($catedata);
            $res['data'] = $data;
            $this->ajaxReturn($res);
        }
    }
    public function cateindex(){
        $this->display();
    }
    public function cateadd(){
        if (IS_POST && IS_AJAX)// 新增
        {
            $par = I('post.');// 参数接收
            try{
                $res = M('liuxueCate')->add($par);// 新增
            }catch (\Exception $exception){
                $this->ajaxReturn(['status'=>false,'msg'=>$exception->getMessage()]);// 捕获异常
            }

            $this->ajaxReturn(['status'=>true,'msg'=>'新增成功!']);// 更新成功
        }
        $this->display();
    }

    public function cateedit(){
        if (IS_POST && IS_AJAX)// 如果修改
        {
            $par = I('post.');// 参数接收

//            asort($par['fileids']);
//            $this->ajaxReturn($par);
            if($par['ids'] == $par['pid']){
                $this->ajaxReturn(['status'=>false,'msg'=>'无法选择当前分类']);// 捕获异常
            }
            try{
                $res = M('liuxueCate')->where(['id'=>$par['ids']])->save($par);// 更新
            }catch (\Exception $exception){
                $this->ajaxReturn(['status'=>false,'msg'=>$exception->getMessage()]);// 捕获异常
            }

            $this->ajaxReturn(['status'=>true,'msg'=>'修改成功!']);// 更新成功
        }

        // 展示修改页面
        $id = trim(I('get.id'),',');// 参数接收
        if (!$id) exit('缺少关键参数');// 缺少关键参数

        $info = M('liuxueCate')->find($id);// 获取要修改的数据

        if (!$info)  exit('数据不存在,当前数据可能已被删除');// 数据不存在
        if(substr($info['headimg'],0,1)=='.'){
            $info['headimgs'] = substr($info['headimg'],1);
        }
        $this->id = $id;
        $this->info = $info;// 分配数据到模板
        $this->display();// 展示模板
    }

    public function catedelete(){
        $ids = explode(',',ltrim(I('post.ids'),','));// 参数接收

        if (!$ids) $this->ajaxReturn(['status'=>false,'msg'=>'缺少关键参数']);// 缺少关键参数

        try{
            $res = M('liuxueCate')->where(['id'=>['IN',$ids]])->delete();// 删除
        }catch (\Exception $exception){
            $this->ajaxReturn(['status'=>false,'msg'=>$exception->getMessage()]);// 捕获异常
        }

        if (!$res) $this->ajaxReturn(['status'=>false,'msg'=>'系统维护,请稍后再试!']);// 删除失败

        $this->ajaxReturn(['status'=>true,'msg'=>'删除成功!']);// 删除成功
    }

    public function catesearch(){
        if(IS_AJAX){
                $catemodel = M('liuxueCate');
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
}