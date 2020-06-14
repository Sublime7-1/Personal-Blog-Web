<?php
/**
 *Created by PhpStorm
 *User: CCH
 *Date: 2020/6/9
 *Time: 22:28
 */
/*
 * 图灵机器人接口
 */
class TLRobot
{
    public $url;
    public $key;
    public $secret;

    public function __construct($url, $key, $secret)
    {
        $this->url      = $url;
        $this->key      = $key;
        $this->secret   = $secret;
    }

    public function robotApi($data)
    {
        if (!$data) {
            return array('code'=>0, 'msg'=>'error');
        }

        $data['userInfo'] = array ('apiKey' => $this->key,'userId' => $this->secret);

        $data = json_encode($data);

        $res = curl_post($this->url, $data);

        $res = json_decode($res, true);

        file_put_contents('../checklog/tlrobot/tlrobot' . date('Ymd') . '.log', 'result:'.date('Y-m-d H:i:s').PHP_EOL.$data.PHP_EOL.json_encode($res,JSON_UNESCAPED_UNICODE).PHP_EOL, FILE_APPEND);

        if (empty($res['results'][0])) {

            return array('code'=>0, 'msg'=>'error');

        }

        return array('code'=>1, 'res'=>$res['results'][0]);
    }
}