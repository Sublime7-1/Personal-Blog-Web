<?php
namespace app\admin\controller;

use think\Db;
use think\Request;

/**
 * 个人资料控制器
 */
class PersonalData extends Common
{
    public function index()
    {
    	$res = Db::table('personal_data')->find();

    	$this->assign('res',$res);
        return $this->fetch();
    }

    public function save()
    {
        $param = request()->post('', '', 'trim');
        
        if (!isset($param['id'])) {
            $this->error('参数丢失');
        } else {
            $id = (int)$param['id'];

            unset($param['id']);
            $data = $param;

            $file = request()->file('file');
            if (isset($file)) {
                $file_info  = $file->getInfo();

                $img_type   = ['image/jpeg','image/png','image/jpg'];
                if (!in_array($file_info['type'], $img_type)) {
                    $this->error('请上传对应类型的文件');
                }

                $info = $file->move(ROOT_PATH . 'public/uploads' . DS . 'personal');
                if ($info) {
                   $cert_src            = '/uploads/personal/' . $info->getSaveName();
                   $data['image']     = $cert_src;
                } else {
                   $this->error('保存图片失败');
                }
            }
 
            $data['birth'] = strtotime($data['birth']);
            $data['update_time'] = time();

            $result = Db::table('personal_data')->where(['id' => $id])->update($data);

            if ($result === false) {
                $this->error('操作失败');
            }
            $this->actionLog('修改博主资料', 'personal_data', $this->adminInfo['id'], serialize($param));
        }

        $this->success('操作成功');
    }
}
