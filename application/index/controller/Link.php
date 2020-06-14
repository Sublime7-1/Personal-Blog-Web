<?php
namespace app\index\controller;

use think\Db;
use think\Request;
use app\index\validate\LinkValidate;

class Link extends Common
{
    public function index()
    {
        return $this->fetch();
    }

    public function save()
    {
        $param = request()->post('', '', 'trim');

        $validate = new LinkValidate();
        $result = $validate->check($param);
        if ($result !== true) {
            return json(array('code'=>0, 'msg'=>$validate->getError()));
        }

        $data = [
            'ip'            => getIP(),
            'name'          => addslashes($param['name']),
            'url'           => $param['url'],
            'descript'      => addslashes($param['descript']),
            'contacts'      => addslashes($param['contacts']),
            'phone'         => $param['phone'],
            'email'         => $param['email'],
            'apply_time'    => time(),
        ];

        $res = Db::table('link')->insert($data);

        if ($res) {
            return json(array('code'=>1, 'msg'=>'申请成功，等待审核吧！'));
        }

        return json(array('code'=>0, 'msg'=>'申请失败，请联系管理员！'));
    }

}
