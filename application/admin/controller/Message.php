<?php
namespace app\admin\controller;

use think\Db;
use think\Request;

/**
 * 留言板控制器(待完成)
 */
class Message extends Common
{
    public function index()
    {
    	$data = request()->get('', '', 'trim');

        $map = [];

        if (isset($data['keyword'])) {
            $map['content|u.name'] = array('like', "%" . addslashes($_GET['keyword']) . "%");
        }

    	$res = Db::table('message_board')
            ->alias('mb')
            ->join('users u', 'mb.uid = u.id and mb.type = 1', 'LEFT')
            ->join('admin_user au', 'mb.uid = au.id and mb.type = 2', 'LEFT')
            ->where($map)
            ->field('mb.*, u.name, au.username')
            ->order('id', 'DESC')
            ->paginate($this->pageSize, false, ['query' => request()->param()]);

    	$this->assign('res',$res);
        return $this->fetch();
    }

    public function showMessage()
    {
        $id = (int)$_GET['id'];

        if (empty($id)) {
            $this->error('参数错误！');
        }

        $res = Db::table('message_board')->where('id', $id)->find();

        if (!$res) {
            $this->error('数据错误！');
        }

        $lastAllMsg = array_reverse($this->getMessage($res, 1));
        $nowMsg[]   = $res;
        $nextAllMsg = $this->getMessage($res, 2);

        $allMsg = array_merge($lastAllMsg, $nowMsg, $nextAllMsg);




        $children = $this->message_html($res);

        $this->assign('res', $res);
        $this->assign('children', $children);
        return $this->fetch();
    }

    public function message_html($arr) {
        $html = '';

        if (is_array($arr['children'])) {
            foreach ($arr['children'] as $key => $value) {
                $html.='<div class="item">
                    <div class="feed d-flex justify-content-between">
                      <div class="feed-body d-flex justify-content-between"><a href="#" class="feed-profile"><img src="/static/img/cc.jpg" alt="person" class="img-fluid rounded-circle"></a>
                        <div class="content">
                          <h5>Aria Smith</h5><span>'.$value['content'] .'</span>
                          <div class="full-date"><small>回复时间：'. date('Y-m-d H:i:s',$value['create_time']).'</small></div>
                        </div>
                      </div>
                    </div>
                  </div>';
                if (is_array($value['children'])) {
                    $html.=$this->message_html($value);
                }
            }
        }

        return $html;
    }

    public function reply()
    {
        $id = (int)$_GET['id'];

        $res = Db::table('message_board')
            ->alias('mb')
            ->join('users u', 'mb.uid = u.id', 'LEFT')
            ->where(['mb.id' => $id])
            ->field('mb.*, u.name')
            ->find();

        if (!$res) {
            $this->error('数据错误！');
        }

        if ($res['pid']) {
            $last_msg = Db::table('message_board')->where('id', $res['pid'])->find();
            if ($last_msg['type'] == 2) {
                if ($last_msg['uid'] == $this->adminInfo['id']){
                    $this->actionLog('已读消息', 'Message', $this->adminInfo['id'], serialize($id));
                    Db::table('message_board')->where('id', $id)->update(['is_read'=>1]);
                } else {
                    $this->error('本条信息管理员不可回复！');
                }
            }
        } else {
            $this->actionLog('已读消息', 'Message', $this->adminInfo['id'], serialize($id));
            Db::table('message_board')->where('id', $id)->update(['is_read'=>1]);
        }

        $this->assign('res', $res);
        return $this->fetch();
    }

    public function replySave()
    {
        $data = request()->param();

        $id = (int)$data['id'];

        if (empty($id)) {
            $this->error('数据错误！');
        }


        $data = [
            'pid'        => $id,
            'uid'        => $this->adminInfo['id'],
            'type'       => 2,
            'content'    => addslashes($data['content']),
            'create_time'=>time()
        ];

        $res = Db::table('message_board')->insert($data);

        if (!$res) {
            $this->error('回复失败！');
        }

        $this->success('回复成功！', 'Message/index');

    }
}
