<?php

use think\Db;
use think\Loader;
use think\Request;

// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件
/**
 * 获取IP信息
 * @return [type]
 */
function getIP()
{
    if (@$_SERVER["HTTP_X_FORWARDED_FOR"])
        $ip = $_SERVER["HTTP_X_FORWARDED_FOR"];
    else if (@$_SERVER["HTTP_CLIENT_IP"])
        $ip = $_SERVER["HTTP_CLIENT_IP"];
    else if (@$_SERVER["REMOTE_ADDR"])
        $ip = $_SERVER["REMOTE_ADDR"];
    else if (@getenv("HTTP_X_FORWARDED_FOR"))
        $ip = getenv("HTTP_X_FORWARDED_FOR");
    else if (@getenv("HTTP_CLIENT_IP"))
        $ip = getenv("HTTP_CLIENT_IP");
    else if (@getenv("REMOTE_ADDR"))
        $ip = getenv("REMOTE_ADDR");
    else
        $ip = "Unknown";
    return $ip;
}

/*将base64数据以图片形式保存*/
function base64_save_img($base_img, $path, $extension = 'jpg')
{
    $base_img = str_replace('data:image/jpg;base64,', '', $base_img);
    $base_img = str_replace('data:image/jpeg;base64,', '', $base_img);
    $base_img = str_replace('data:image/png;base64,', '', $base_img);
    $prefix = 'bbc_';
    $output_file = $prefix . time() . rand(1, 9999) . randStr() . '.' . $extension;
    $path = $path . $output_file;
    $ifp = fopen($path, "wb");
    fwrite($ifp, base64_decode($base_img));
    fclose($ifp);

    return $path;
}

//正则验证手机号 正确返回 true
function preg_mobile($mobile)
{
    if (preg_match("/^1\d{10}$/", $mobile)) {
        return TRUE;
    } else {
        return FALSE;
    }
}

//随机生成字符串
function randStr($length = 4, $type = 'str')
{
    if ($type == 'str')
        $pattern = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijkmnopqrstuvwxyz123456789';
    else
        $pattern = '123456789';
    $key = '';
    for ($i = 0; $i < $length; $i++) {
        $key .= $pattern[mt_rand(0, strlen($pattern) - 1)];
    }

    return $key;
}

/**
 * 求两个日期之间相差的天数
 * (针对1970年1月1日之后，求之前可以采用泰勒公式)
 * @param string $day1
 * @param string $day2
 * @return number
 */
function diffBetweenTwoDays($day1, $day2, $type = 1)
{
    if ($type == 1) {
        $second1 = $day1;
        $second2 = $day2;
    } else {
        $second1 = strtotime($day1);
        $second2 = strtotime($day2);
    }

    return ceil(($second1 - $second2) / 86400);
}

/*多图上传,拆成数组，单个上传*/
function files_single($files)
{
    $data = array();
    foreach ($files as $key => $val) {
        foreach ($val as $num => $value) {
            $data[$num][$key] = $value;
        }
    }
    return $data;
}

//获取文件类型后缀 
function extend($file_name)
{
    $extend = pathinfo($file_name);
    $extend = strtolower($extend["extension"]);
    return $extend;
}

/*转换时间格式，05-15-18*/
function changeTime($dateTime)
{
    $arr = preg_split("/\-/", $dateTime);
    $time = strtotime($arr[2] . '-' . $arr[0] . '-' . $arr[1]);
    return $time;
}

/*时间戳转成05-15-18*/
function changeTimestamp($timestamp)
{
    $dateline = date('y-m-d', $timestamp);
    $arr = explode('-', $dateline);
    return $arr[1] . '-' . $arr[2] . '-' . $arr[0];
}

/*textarea换行空格处理*/
function textareaLine($sContent)
{
    $pattern = array(
        '/ /',//半角下空格
        '/　/',//全角下空格
        '/\r\n/',//window 下换行符
        '/\n/',//Linux && Unix 下换行符
    );
    $replace = array('&nbsp;', '&nbsp;', '<br />', '<br />');
    $sContent = preg_replace($pattern, $replace, $sContent);
    return $sContent;
}

/*固定金额格式,保留两位小数*/
function price_format($price)
{
    $price = sprintf("%.2f", $price);
    return $price;
}

// 获取系统配置
function getConfig($type)
{
    $map['type'] = $type;
    $config = Db::table('system_config')->where($map)->select();
    $configs = array();
    foreach ($config as $k => $v) {
        $configs[$v['code']] = $v['value'];
    }
    return $configs;
}
function getConfigValue($code)
{
    $map['code'] = $code;
    $value = Db::table('system_config')->where($map)->value('value');
    return $value;
}

/*CURL请求*/
function curl_post($url, $post_data = '', $timeout = 5)
{
    $ch = curl_init();

    curl_setopt ($ch, CURLOPT_URL, $url);

    curl_setopt ($ch, CURLOPT_POST, 1);

    if($post_data != ''){

        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);

    }

    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);

    curl_setopt ($ch, CURLOPT_CONNECTTIMEOUT, $timeout);

    curl_setopt($ch, CURLOPT_HEADER, false);

    $file_contents = curl_exec($ch);

    curl_close($ch);

    return $file_contents;
}

function curl_get($url){
    $ch = curl_init();

    if(stripos($url,"https://")!==FALSE){

        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);

        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        curl_setopt($ch, CURLOPT_SSLVERSION, 1); //CURL_SSLVERSION_TLSv1

    }

    curl_setopt($ch, CURLOPT_URL, $url);

    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1 );

    $file_contents = curl_exec($ch);

    $aStatus = curl_getinfo($ch);

    curl_close($ch);

    if(intval($aStatus["http_code"])==200){

        return $file_contents;

    }else{

        return false;
        
    }
}