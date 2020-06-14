<?php
/**
 *Created by PhpStorm
 *User: CCH
 *Date: 2020/6/9
 *Time: 22:35
 */
namespace app\index\controller;

use think\Db;
use think\Request;
use think\Loader;

class Robot extends Common
{
    public $url = '';
    public $key = '';
    public $secret = '';

    public function robotApi()
    {
        $param = request()->param();

        if (!isset($param['content']) || empty($param['content'])) {
            return json(array('code'=>0,'msg'=>'请输入内容'));
        }

        /*消息入库*/
        $chatData = [
            'ip' => getIP(),
            'uid'=> 0,
            'message'=>$param['content'],
            'create_time'=>time()
        ];
        $robot_chat_id = Db::table('robot_chat')->insertGetId($chatData);

        Loader::import('TLRobot.TLRobot');

        $TLRobot = new \TLRobot($this->url, $this->key, $this->secret);

        $data = array(
            'reqType' => 0,//文本
            'perception' => array(
                'inputText' => array (
                    'text' => $param['content']
                )
            )
        );

        $res = $TLRobot->robotApi($data);

        if ($res['code'] == 1) {
            Db::table('robot_chat')->where('id', $robot_chat_id)->update(['robot_message'=>$res['res']['values']['text']]);

            $res['robot'] = getConfig('robot');

            return json($res);
        }

        return json(array('code'=>0,'msg'=>'系统错误'));
    }
}