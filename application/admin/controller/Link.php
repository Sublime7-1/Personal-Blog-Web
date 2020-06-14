<?php
namespace app\admin\controller;

use think\Db;
use think\Request;

/**
 * 友情链接控制器
 */
class Link extends Common
{
    public function index()
    {
    	$data = request()->get('', '', 'trim');

        $map = [];
        if (isset($data['keyword'])) {
            $map['name'] = array('like', "%" . addslashes($_GET['keyword']) . "%");
        }

    	$res = Db::table('link')->where($map)->paginate($this->pageSize, false, ['query' => request()->param()]);

    	$this->assign('res',$res);
        return $this->fetch();
    }

    public function showLink()
    {
        $id = (int)$_GET['id'];

        $res = Db::table('link')->where(['id' => $id])->find();
        if (!$res) {
            $this->error('数据错误！');
        }

        $this->assign('res', $res);
        return $this->fetch();
    }

    public function check()
    {
        $param = request()->get('', '', 'trim');
        
        if (!isset($param['id'])) {
            $this->error('参数丢失');
        } else {
            $id = (int)$param['id'];

            $data = [];
            $data['status'] = (int)$param['status'];

            if ($data['status'] == 1 || $data['status'] == 2) {
                $data['check_time'] = time();
            }

            $result = Db::table('link')->where(['id' => $id])->update($data);

            if ($result === false) {
                $this->error('操作失败');
            }
            $this->actionLog('修改友情链接状态', 'link', $this->adminInfo['id'], serialize($param));
        }

        $this->success('操作成功');
    }
}
