<?php
/**
 * Created by PhpStorm.
 * User: dragon
 * Date: 2018/7/20
 * Time: 10:29
 */

namespace Manage\Controller;

use Think\Controller;

class IxiaoyingController extends Controller
{
    /**
     * 初始化
     */
    public function _initialize()
    {
        // 允许来自 i.xiaoying.net 的请求
//        header("Access-Control-Allow-Origin: http://i.xiaoying.net");

        // 允许所有来源的请求
        header("Access-Control-Allow-Origin: *");

    }

}