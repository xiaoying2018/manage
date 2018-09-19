<?php
/**
 * Created by PhpStorm.
 * User: lqs
 * Date: 2018/8/17
 * Time: 11:28
 */

namespace Manage\Controller;

use Manage\Model\ContentsModel;

class FileController extends BaseController
{
    public function delfile(){
        if(IS_AJAX){
            $id = I('file_id');
            if (!$id) $this->ajaxReturn(['status' => false, 'msg' => '缺少关键参数']);
            $del = M('file')->where(['file_id'=>$id])->delete();
            if($del){
                $this->ajaxreturn(['file_id'=>$id,'status'=>true]);
            }else{
                $this->ajaxreturn(['status'=>false]);
            }
        }
    }
}