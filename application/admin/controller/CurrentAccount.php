<?php
/**
 *Created by PhpStorm
 *User: CCH
 *Date: 2020/6/7
 *Time: 0:54
 */

namespace app\admin\controller;

use think\Db;
use think\Request;

/**
 * 流水账控制器
 */
class CurrentAccount extends Common
{
    public function index()
    {
        $data = request()->get('', '', 'trim');

        $map = [];
        $map['ca.status'] = 1;
        if (isset($data['keyword'])) {
            $map['au.username|u.name'] = array('like', "%" . addslashes($_GET['keyword']) . "%");
        }
        if (isset($data['star_time']) && !empty($data['star_time'])) {
            $map['account_date'] = array('>=', $data['star_time']);
        }
        if (isset($data['end_time']) && !empty($data['end_time'])) {
            $map['account_date'] = array('<=', $data['end_time']);
        }

        $res = Db::table('current_account')
            ->alias('ca')
            ->join('users u', 'u.id = ca.uid and ca.type = 2', 'LEFT')
            ->join('admin_user au', 'au.id = ca.uid and ca.type = 1', 'LEFT')
            ->where($map)
            ->field('ca.*, u.name, au.username')
            ->order('ca.id DESC')
            ->paginate($this->pageSize, false, ['query' => request()->param()]);

        $this->assign('res',$res);
        return $this->fetch();
    }

    public function add()
    {
        return $this->fetch();
    }

    public function save()
    {
        $data = request()->post('', '', 'trim');

        if (!isset($data['id'])) {
            $res = Db::table('current_account')->where(['id'=>$this->adminInfo['id'], 'account_date'=>$data['account_date']])->find();
            if ($res) {
                $this->error('非法新增，当天流水账已存在');
            }

            $data['uid']          = $this->adminInfo['id'];
            $data['type']         = 1;
            $data['create_time']  = time();

            $insert_id = Db::table('current_account')->insert($data);
            if ($insert_id) {
                $this->actionLog('新增流水账', 'day_sentence', $this->adminInfo['id'], serialize($data));
                $this->success('操作成功', 'CurrentAccount/Index');
            } else {
                $this->error('操作失败');
            }
        } else {
            if (!empty($data['id'])) {
                $id = $data['id'];
                unset($data['id']);

                $res = Db::table('current_account')->where('id', $id)->update($data);

                if ($res !== false) {
                    $data['id'] = $id;
                    $this->actionLog('修改流水账', 'day_sentence', $this->adminInfo['id'], serialize($data));
                    $this->success('操作成功','CurrentAccount/Index');
                } else {
                    $this->error('操作失败');
                }
            } else {
                $this->error('系统错误');
            }
        }
    }

    public function edit()
    {
        $id = (int)$_GET['id'];

        $res = Db::table('current_account')
            ->alias('ca')
            ->join('users u', 'u.id = ca.uid and ca.type = 2', 'LEFT')
            ->join('admin_user au', 'au.id = ca.uid and ca.type = 1', 'LEFT')
            ->field('ca.*, u.name, au.username')
            ->where(['ca.id' => $id])
            ->find();

        if (!$res) {
            $this->error('数据错误！');
        }

        $this->assign('res', $res);
        return $this->fetch();
    }

    public function delete($id)
    {
        $res = Db::table('current_account')->where('id', $id)->update(['status'=>0]);

        if ($res) {
            $this->actionLog('删除流水账', 'DaySentence', $this->adminInfo['id'], serialize($id));
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }
}
