<?php
namespace app\admin\controller;

use think\Db;
use think\Request;

/**
 * 首页轮播图控制器
 */
class Carousel extends Common
{
    /**
     * 轮播图管理
     * @return mixed
     */
    public function index()
    {
        $res = Db::table('carousel')
            ->where('status', '<>', 2)
            ->paginate($this->pageSize, false, ['query' => request()->param()]);

        $this->assign('res', $res);
        return $this->fetch();
    }

    /**
     * 添加轮播图
     * @return mixed
     */
    public function add()
    {
        return $this->fetch();
    }

    /**
     * 编辑轮播图
     * @param $id
     * @return mixed
     */
    public function edit($id)
    {
        $res = Db::table('carousel')->where('id', $id)->find();

        $this->assign('res', $res);
        return $this->fetch();
    }

    /**
     * 保存轮播图
     */
    public function save()
    {
        $data = request()->post('', '', 'trim');

        $file = request()->file('file');
        if (isset($file)) {
            $file_info  = $file->getInfo();

            $img_type   = ['image/jpeg','image/png','image/jpg'];
            $video_type = ['video/mp4'];
            if ($data['type'] == 1 && !in_array($file_info['type'], $img_type)) {
                $this->error('请上传对应类型的文件');
            }
            if ($data['type'] == 2 && !in_array($file_info['type'], $video_type)) {
                $this->error('请上传对应类型的文件');
            }

            $info = $file->move(ROOT_PATH . 'public/uploads' . DS . 'carousel');
            if ($info) {
               $cert_src            = '/uploads/carousel/' . $info->getSaveName();
               $data['content']     = $cert_src;
            } else {
               $this->error('保存图片失败');
            }
        }

        if (!isset($data['id'])) {
            $data['create_time']  = $data['update_time']  = time();
            $insert_id = Db::table('carousel')->insert($data);
            if ($insert_id) {
                $this->actionLog('保存轮播图', 'carousel', $this->adminInfo['id'], serialize($data));
                $this->success('保存成功');
            } else {
                $this->error('保存失败');
            }
        } else {
            $id = $data['id'];
            unset($data['id']);

            $data['update_time']  = time();
            $update = Db::table('carousel')->where('id',$id)->update($data);
            if ($update) {
                $this->actionLog('更新轮播图', 'carousel', $this->adminInfo['id'], serialize($data));
                $this->success('更新成功', 'admin/Carousel/index');
            } else {
                $this->error('更新失败');
            }
        }
    }

    /**
     * 删除轮播图
     */
    public function delete($id)
    {
        $res = Db::table('carousel')->where('id', $id)->update(['status'=>2]);

        if ($res) {
            $this->actionLog('删除轮播图', 'carousel', $this->adminInfo['id'], serialize($id));
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }

}
