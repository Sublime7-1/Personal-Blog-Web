<?php
namespace app\index\controller;

use think\Db;
use think\Request;

class PersonalData extends Common
{
    public function index()
    {
    	$res = Db::table('personal_data')->find();

    	$res['age'] = date('Y',time()) - date('Y',$res['birth']);

    	$this->assign('res', $res);
        return $this->fetch();
    }

}
