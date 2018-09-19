<?php
/**
 * Created by PhpStorm.
 * User: dragon
 * Date: 2018/7/17
 * Time: 18:37
 */

/**
 * 格式化权限树
 * @param $data
 * @return array
 */
function getPreTree($data)
{
    $refer = array();
    $tree = array();
    foreach($data as $k => $v){
        $refer[$v['id']] = & $data[$k]; //创建主键的数组引用
    }
    foreach($data as $k => $v){
        $pid = $v['pid'];  //获取当前分类的父级id
        if($pid == 0){
            $tree[] = & $data[$k];  //顶级栏目
        }else{
            if(isset($refer[$pid])){
                $refer[$pid]['children'][] = & $data[$k]; //如果存在父级栏目，则添加进父级栏目的子栏目数组中
            }
        }
    }
    return $tree;
}

/**
 * 判断当前登录用户是否有操作某个uri的权限
 * @param $uri
 * @return bool
 */
function hasPre($uri)
{
//    session_start();// 开启session

    // 当前用户已有权限
    $current_user_has_pre = session('xy_manager')['has_pre'];

    // 如果当前用户拥有想要操作的uri的权限
    if (in_array($uri,$current_user_has_pre))
        return true;

    return false;// 否则无权限
}

/**
 * 判断手机号是否合法
 * @param $phone
 * @return bool
 */
function is_phone($phone)
{
    return strlen(trim($phone)) == 11 && preg_match("/^1[3|4|5|6|7|8|9][0-9]{9}$/i", trim($phone));
}

/**
 * 按指定字段排序数组
 * @param $array
 * @param $field
 * @param bool $desc
 */
function sortArrByField(&$array, $field, $desc = false){
    $fieldArr = array();
    foreach ($array as $k => $v) {
        $fieldArr[$k] = $v[$field];
    }
    $sort = $desc == false ? SORT_ASC : SORT_DESC;
    array_multisort($fieldArr, $sort, $array);
}

/**
 * 发送短信
 * @param $phone
 * @param $content
 * @return bool|string
 */
function sendsms($phone,$content)
{
    $u = 'everelite';
    $p = md5('invY1234');
    $url = "http://api.smsbao.com/sms?u={$u}&p={$p}&m={$phone}&c={$content}";

    return file_get_contents($url);
}

/**
 * [getRandStr 随机字符串]
 * @param  integer $length [长度]
 * @param  integer $type   [类型]
 * @return [type]          [str]
 */
function getRandStr($length = 6,$type = 1 , $encrypt = false )
{
    //判断Type，1. 字母数字混合 2. 纯数字 3. 纯字母
    switch ( $type )
    {
        case 1:
            $str = '0123456789abcdefghijklmnopqrstuvwxyz';
            break;
        case 2:
            $str = '0123456789';
            break;
        case 3:
            $str = 'abcdefghijklmnopqrstuvwxyz';
    }

    //打乱顺序
    $str = str_shuffle($str);

    //根据长度截取,得到原始字符串
    $result = substr($str,0,$length);

    //判断是否加密
    if ( $encrypt )
    {
        return md5(trim($result));
    }
    else
    {
        return trim($result);
    }
}

/**
 * curl采集
 */
function curlget($url){

//生成一个curl对象
    $curl=curl_init();
//设置URL和相应的选项
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);  //将curl_exec()获取的信息以字符串返回，而不是直接输出。
//执行curl操作
    $data=curl_exec($curl);
    var_dump($data);
}

