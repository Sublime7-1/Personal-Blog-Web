<?php
namespace app\index\controller;

use think\Db;
use think\Request;

class DaySentence extends Common
{
    public function index()
    {
        return $this->fetch();
    }

    public function save()
    {
        $param = request()->post('', '', 'trim');

        if (empty($param['content']) || empty($param['content_en'])) {
            return $this->error('中、英两版的精句内容不能为空！');
        }

        $data = [
            'uid'            => $_SESSION['id'],
            'content'        => htmlspecialchars($param['content']),
            'content_en'     => htmlspecialchars($param['content_en']),
            'type'           => 1,
            'create_time'    => time()
        ];

        $res = Db::table('day_sentence')->insert($data);

        if ($res) {
            return $this->error('提交成功，等待审核吧！', 'index/index');
        }

        return $this->error('提交失败，请稍后提交！');
    }

}
