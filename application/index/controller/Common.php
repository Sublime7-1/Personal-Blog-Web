<?php

namespace app\index\controller;

use think\Db;
use think\Request;
use think\Cache;
use think\Loader;

class Common extends \think\Controller
{
    public $pageSize = 10;

    public function __construct()
    {
        parent::__construct();

        $articleTypeHeader = Db::table('article_type')->where('status', 1)->order('sort ASC')->select();

        $robotConfig = getConfig('robot');

        $this->assign('robotConfig', $robotConfig);
        $this->assign('articleTypeHeader', $articleTypeHeader);
    }

    protected function saveLog($name, $log)
    {
        $data = array(
            'time' => date('Y-m-d H:i:s', time()),
            'log' => $log
        );
        $json = '/****' . json_encode($data) . '****/' . "\r\n";

        file_put_contents('./checklog/' . $name . '.text', $json, FILE_APPEND);
    }

    /**
     * 记录PV,访问量, 即页面浏览量或点击量
     */
    protected function recordPV()
    {
        $hours = date('H');

        $datetime = date('Ymd', time());

        $now = Db::table('visitor_pv')->where('datetime', $datetime)->where('hours', $hours)->find();

        if ($now) {
            Db::table('visitor_pv')->where('datetime', $datetime)->where('hours', $hours)->setInc('num', 1);
        } else {
            $data = [
                'datetime'      => $datetime,
                'hours'         => $hours,
                'num'           => 1,
                'create_time'   => time()
            ];
            Db::table('visitor_pv')->insert($data);
        }
    }

    /**
     * 记录IP
     */
    protected function recordIP()
    {
        $ip = getIP();

        $datetime = date('Ymd', time());

        $now = Db::table('visitor_ip')->where('ip', $ip)->where('datetime', $datetime)->find();

        if (!$now) {
            Db::table('visitor_ip')->insert(['ip' => $ip, 'datetime'=>$datetime, 'create_time' => time()]);
        }
    }

    /**
     * TODO
     * 找上一条消息
     */
    protected function prevMessage($arr)
    {
        $prevMessageArr = [];

        if (!empty($arr['pid'])) {
            $message = Db::table('message_board')->where('id', $arr['pid'])->find();

            $prevMessageArr[] = $message;

            if (!empty($message['pid'])) {
                $prevMessage = $this->prevMessage($message);

                foreach ($prevMessage as $k => $v) {
                    $prevMessageArr[] = $v;
                }
            }
        }

        return array_reverse($prevMessageArr);
    }
}