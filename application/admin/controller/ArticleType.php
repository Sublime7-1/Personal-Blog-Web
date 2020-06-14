<?php
namespace app\admin\controller;

use think\Db;
use think\Request;

/**
 * 文章类型控制器
 */
class ArticleType extends Common
{
    /**
     * 文章类型管理
     * @return mixed
     */
    public function index()
    {
        $data = request()->get('', '', 'trim');

        $map = [];
        $map['status'] = 1;
        if (isset($data['keyword'])) {
            $map['name'] = array('like', "%" . addslashes($_GET['keyword']) . "%");
        }

        $res = Db::table('article_type')
            ->where($map)
            ->paginate($this->pageSize, false, ['query' => request()->param()]);

        $this->assign('res', $res);
        return $this->fetch();
    }

    /**
     * 添加文章类型
     * @return mixed
     */
    public function add()
    {
        return $this->fetch();
    }

    /**
     * 编辑文章类型
     * @param $id
     * @return mixed
     */
    public function edit($id)
    {
        $res = Db::table('article_type')->where('id', $id)->find();

        $this->assign('res', $res);
        return $this->fetch();
    }

    /**
     * 保存文章类型
     */
    public function save()
    {
        $data = request()->post('', '', 'trim');

        if (!isset($data['id'])) {
            $data['create_time']  = $data['update_time']  = time();
            $insert_id = Db::table('article_type')->insert($data);
            if ($insert_id) {
                $this->actionLog('保存文章类型', 'article_type', $this->adminInfo['id'], serialize($data));
                $this->success('保存成功', 'admin/ArticleType/index');
            } else {
                $this->error('保存失败');
            }
        } else {
            $id = $data['id'];
            unset($data['id']);

            $data['update_time']  = time();
            $update = Db::table('article_type')->where('id',$id)->update($data);
            if ($update) {
                $this->actionLog('更新文章类型', 'article_type', $this->adminInfo['id'], serialize($data));
                $this->success('更新成功', 'admin/ArticleType/index');
            } else {
                $this->error('更新失败');
            }
        }
    }

    /**
     * 删除文章类型
     */
    public function delete($id)
    {
        $res = Db::table('article_type')->where('id', $id)->update(['status'=>0]);

        if ($res) {
            Db::table('article')->where('pid', $id)->update(['status'=>0]);
            $this->actionLog('删除文章类型', 'article_type', $this->adminInfo['id'], serialize($id));
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }

}
