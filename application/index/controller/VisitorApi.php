<?php
/**
 *Created by PhpStorm
 *User: CCH
 *Date: 2020/6/8
 *Time: 22:50
 *Note: 记录IP\PV
 */
namespace app\index\controller;

use think\Db;
use think\Request;

class VisitorApi extends Common
{
    public function recordApi()
    {
        $this->recordPV();
        $this->recordIP();
    }

    public function recordShow()
    {
        $today = date('Ymd');
        $yesterday = date("Ymd",strtotime("-1 day"));
        $visitor_ip = Db::table('visitor_ip')->wherebetween('datetime',[$yesterday, $today])->group('datetime')->field('datetime,count(*) as num')->select();
        $visitor_pv = Db::table('visitor_pv')->wherebetween('datetime',[$yesterday, $today])->group('datetime')->field('datetime,sum(num) as num')->select();

        $visitor_all = [];
        $visitor_all[$today] = [];
        $visitor_all[$yesterday] = [];

        foreach ($visitor_ip as $key => $value) {
            $visitor_all[$value['datetime']]['ip'] = $value['num'];
        }

        foreach ($visitor_pv as $key => $value) {
            $visitor_all[$value['datetime']]['pv'] = $value['num'];
        }

        $res = [];
        $res[] = $visitor_all[$today];
        $res[] = $visitor_all[$yesterday];

        $today_ip = 0;
        $today_pv = 0;
        $yes_ip = 0;
        $yes_pv = 0;
        foreach ($res as $k => $v) {
            if ($v) {
                if (isset($v['ip'])) {
                    if ($k == 0) {
                        $today_ip = $v['ip'];
                    } else {
                        $yes_ip = $v['ip'];
                    }
                }
                if (isset($v['pv'])) {
                    if ($k == 0) {
                        $today_pv = $v['pv'];
                    } else {
                        $yes_pv = $v['pv'];
                    }
                }
            } else {
                if ($k == 0) {
                    $today_ip = 0;
                    $today_pv = 0;
                } else {
                    $yes_ip = 0;
                    $yes_pv = 0;
                }
            }
        }

        $content = '统计：|  今日IP['.$today_ip.'] | 今日PV['.$today_pv.'] | 昨日IP['.$yes_ip.'] |  昨日PV['.$yes_pv.']';

        return json(array('code'=>1, 'msg'=>'success', 'res'=>$content));
    }
}
