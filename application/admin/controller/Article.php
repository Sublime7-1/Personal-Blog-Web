<?php
namespace app\admin\controller;

use think\Db;
use think\Request;

/**
 * 文章控制器
 */
class Article extends Common
{
    /**
     * 文章列表
     */
    public function index()
    {
    	$data = request()->get('', '', 'trim');

        $map = [];
        $map['article.status'] = array('<>', 2);
        if (isset($data['keyword'])) {
            $map['title|username'] = array('like', "%" . addslashes($_GET['keyword']) . "%");
        }

    	$res = Db::table('article')
            ->join('admin_user','article.uid=admin_user.id', 'LEFT')
            ->join('article_type', 'article.pid=article_type.id', 'LEFT')
            ->where($map)
            ->field('article.*, admin_user.username, article_type.name as atName')
            ->paginate($this->pageSize, false, ['query' => request()->param()]);

    	$this->assign('res',$res);
        return $this->fetch();
    }

    /**
     * 添加文章
     */
    public function add()
    {
        $articleType = Db::table('article_type')->where('status', 1)->select();

        if (count($articleType) == 0) {
            $this->error('请先添加文章类型', 'admin/ArticleType/add');
        }

        $this->assign('articleType', $articleType);
        return $this->fetch();
    }

    /**
     * 修改文章
     */
    public function edit()
    {
        if (!empty($_GET['id'])) {
            $map        = [];
            $map['article.id']  = (int)$_GET['id'];

            $res = Db::table('article')
                ->join('admin_user au', 'article.uid=au.id', 'LEFT')
                ->where($map)
                ->field('article.*, au.username')
                ->find();
            $res['images'] = explode(',',$res['images']);

            $articleType = Db::table('article_type')->where('status', 1)->select();

            $this->assign('articleType', $articleType);
            $this->assign('res', $res);
        } else {
            $this->error('请求错误');
        }

        return $this->fetch();
    }

    /**
     * 保存/更新文章
     */
    public function save()
    {
        $data               = [];
        $data['uid']        = $this->adminInfo['id'];
        $data['title']      = $_POST['title'];
        $data['label']      = $_POST['label'];
        $data['describe']   = $_POST['describe'];
        $data['content']    = $_POST['content'];
        $data['status']     = $_POST['status'];
        $data['recommend']  = $_POST['recommend'];
        $data['update_time']= time();
        $data['create_time']= time();

        $images = request()->file('images');//图集
        if (count($images)) {
            foreach ($images as $k => $v) {
                $images_info = $v->move(ROOT_PATH . 'public/uploads' . DS . 'article');
                if ($images_info) {
                   $src             = '/uploads/article/' . $images_info->getSaveName();
                   $images_all[$k]  = $src;
                } else {
                   $this->error('保存图集失败');
                }
            }
            $data['images'] = implode(',', $images_all);
        }

        if (!empty($_POST['id'])) {
            unset($data['create_time'],$data['uid']);
            $map['id'] = (int)$_POST['id'];
            if (Db::table('article')->where($map)->update($data)) {
                $this->actionLog('修改文章', 'Article', $this->adminInfo['id'], serialize($_POST));
                $this->success('修改成功', 'Article/index');
            } else {
                $this->error('修改失败');
            }
        } else {
            $data['pid'] = $_POST['pid'];
            if (Db::table('article')->insert($data)) {
                $this->actionLog('添加文章', 'Article', $this->adminInfo['id'], serialize($_POST));
                $this->success('添加成功', 'Article/index');
            } else {
                $this->error('添加失败');
            }
        }
    }

    /**
     * 删除文章
     */
    public function delete($id)
    {
        $res = Db::table('article')->where('id', $id)->update(['status'=>2]);

        if ($res) {
            $this->actionLog('删除文章', 'article', $this->adminInfo['id'], serialize($id));
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }

    /**
     * 查看文章评论
     */
    public function comment()
    {
        if (!empty($_GET['id'])) {
            $map        = [];
            $map['aid']  = (int)$_GET['id'];
            $map['pid']  = '';
            $map['type']  = 1;

            $res = Db::table('article_comment')
                ->alias('ac')
                ->join('users u', 'ac.uid=u.id', 'LEFT')
                ->where($map)
                ->field('ac.*,u.name')
                ->find();

            $this->assign('res', $res);
            return $this->fetch();
        } else {
            $this->error('请求错误');
        }
    }
}
