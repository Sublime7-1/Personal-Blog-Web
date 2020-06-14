<?php
namespace app\admin\controller;

use think\Db;
use think\Request;

/**
 * 系统设置控制器
 */
class Setting extends Common
{
    public function index()
    {
    	$data = request()->get('', '', 'trim');

        $map = [];
        if (isset($data['keyword'])) {
            $map['name'] = array('like', "%" . addslashes($_GET['keyword']) . "%");
        }

    	$res = Db::table('system_config')->where($map)->paginate($this->pageSize, false, ['query' => request()->param()]);

    	$this->assign('res',$res);
        return $this->fetch();
    }

    public function edit()
    {
        $id = (int)$_GET['id'];

        $res = Db::table('system_config')->where(['id' => $id])->find();
        if (!$res) {
            $this->error('数据错误！');
        }

        $this->assign('data', $res);
        return $this->fetch();
    }

    public function save()
    {
        $param = request()->post('', '', 'trim');

        $data = [];
        $data['name']   = $param['name'];
        $data['remark'] = $param['remark'];

        if ($param['date_type'] == 'image') {
            $images = request()->file('value');
            if (isset($images)) {

                $file_info = $images->getInfo();

                $img_type = ['image/jpeg', 'image/png', 'image/jpg'];
                if (!in_array($file_info['type'], $img_type)) {
                    $this->error('请上传对应类型的文件');
                }

                $images_info = $images->move(ROOT_PATH . 'public/uploads' . DS . 'setting');
                if ($images_info) {
                    $src             = '/uploads/setting/' . $images_info->getSaveName();
                    $data['value']   = $src;
                } else {
                    $this->error('保存图片失败');
                }
            }
        } else {
            $data['value'] = $param['value'];
        }
        
        if (!isset($param['id'])) {
            $this->error('参数丢失');
        } else {
            $id = (int)$param['id'];
            $result = Db::table('system_config')->where(['id' => $id])->update($data);

            if ($result === false) {
                $this->error('修改系统设置失败');
            }
            $this->actionLog('修改系统设置', 'system_config', $this->adminInfo['id'], serialize($param));
        }

        $this->success('操作成功');
    }
}
