<?php
namespace app\index\controller;

use think\Db;
use think\Request;

class Article extends Common
{
    public function index()
    {
        $param = request()->param();

        if (!isset($param['pid'])) {
            return $this->redirect('Index/index');
        }

        // 文章类型
        $articleType = Db::table('article_type')->where('id', $param['pid'])->where('status', 1)->find();
        if (!$articleType) {
            return $this->error('系统错误');
        }

        // 文章列表
        $res = Db::table('article')
            ->where(['pid' => $param['pid'], 'status' => 1])
            ->order('id DESC')
            ->paginate($this->pageSize, false, ['query' => request()->param()]);

        // 点击排行
        $clickRank = Db::table('article')
            ->where(['pid' => $param['pid'], 'status' => 1])
            ->order('browse_num DESC')
            ->limit(10)
            ->select();

        $this->assign('articleType', $articleType);
        $this->assign('res', $res);
        $this->assign('clickRank', $clickRank);
        return $this->fetch();
    }

    public function details()
    {
        $param = request()->param();

        if (!isset($param['id'])) {
            return $this->error('系统错误');
        }

        // 当前文章是否存在
        $res = Db::table('article')->where('id', $param['id'])->where('status', 1)->find();
        if (!$res) {
            return $this->error('文章错误');
        }

        // 浏览次数+1
        Db::table('article')->where('id', $param['id'])->setInc('browse_num', 1);

        // 当前文章
        $article = Db::table('article')->where('id', $param['id'])->find();

        // 上一篇
        $prev_article = Db::table('article')->where('id', '<', $param['id'])->where('status', 1)->order('id', 'DESC')->find();
        // 下一篇
        $next_article = Db::table('article')->where('id', '>', $param['id'])->where('status', 1)->order('id', 'ASC')->find();

        $this->assign('res', $article);
        $this->assign('prev_article', $prev_article);
        $this->assign('next_article', $next_article);
        return $this->fetch();
    }

}
