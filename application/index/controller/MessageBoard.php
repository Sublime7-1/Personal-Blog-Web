<?php
namespace app\index\controller;

use think\Db;
use think\Request;

class MessageBoard extends Common
{
    public function index()
    {
        $param = request()->param();

        $page_size = 5;

        if (isset($param['page']) && !empty($param['page'])) {
            $page = intval($param['page']) - 1;
        } else {
            $page = 0;
        }

        $limit_start = $page * $page_size;
        $limit_end = ($limit_start + 1) * $page_size;

        // 留言
        $messageoard = Db::table('message_board')->order('id DESC')->limit($limit_start, $limit_end)
            ->paginate($this->pageSize, false, ['query' => request()->param()])->each(function($item){
                if ($item['type'] == 1) {
                    $name = Db::table('users')->where('id', $item['uid'])->value('name');
                } else {
                    $name = Db::table('admin_user')->where('id', $item['uid'])->value('username');
                }
                $item['name'] = $name;

                /*回复者*/
                if ($item['pid']) {
                    $reply = Db::table('message_board')->where('id', $item['pid'])->find();
                    if ($reply['type'] == 1) {
                        $reply_name = Db::table('users')->where('id', $reply['uid'])->value('name');
                    } else {
                        $reply_name = Db::table('admin_user')->where('id', $reply['uid'])->value('username');
                    }
                    $reply['name'] = $reply_name;

                    $item['reply'] = $reply;
                }

                return $item;
            });

        // 点击排行
        $clickRank = Db::table('article')
            ->where(['status' => 1])
            ->order('browse_num DESC')
            ->limit(10)
            ->select();

        $this->assign('messageAll', $messageoard);
        $this->assign('clickRank', $clickRank);
        return $this->fetch();
    }

    public function save()
    {
        $param = request()->post('', '', 'trim');

        if (empty($param['content'])) {
            return $this->error('留言不能为空！');
        }

        $data = [
            'uid'          => 1,
            'type'         => 1,
            'content'      => addslashes($param['content']),
            'create_time'  => time(),
        ];

        $res = Db::table('message_board')->insert($data);

        if ($res) {
            return $this->error('留言成功', 'MessageBoard/index');
        }

        return $this->error('留言失败，请稍后提交！');
    }

    public function ajaxSaveMessage()
    {
        $param = request()->post('', '', 'trim');

        if (empty($param['content'])) {
            return array('code'=>0, 'msg'=>'留言不能为空');
        }

        $data = [
//            'uid'          => $_SESSION['id'],
            'uid'          => 1,
            'pid'          => intval($param['pid']),
            'type'         => 1,
            'content'      => addslashes($param['content']),
            'create_time'  => time(),
        ];

        $res = Db::table('message_board')->insert($data);

        if ($res) {
            return array('code'=>1, 'msg'=>'回复成功');
        }

        return array('code'=>0, 'msg'=>'回复失败，请稍后提交');
    }

}
