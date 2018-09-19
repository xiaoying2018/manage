<?php
/**
 * Created by PhpStorm.
 * User: lenovo
 * Date: 2018/9/7
 * Time: 17:49
 */
namespace Manage\Model;

use Think\Model;

class SgpschoolModel extends Model
{
    protected $trueTableName = 'school_singapore';

    public function getschoollist($page,$offset,$schoolwhere=[],$majorwhere=[]){
        return $schooldata =  $this->alias('ss')
            ->limit(($page-1)*$offset,$offset)
            ->where($schoolwhere)
            ->select();
    }
}