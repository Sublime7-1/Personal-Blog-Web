<?php
/**
 *Created by PhpStorm
 *User: CCH
 *Date: 2020/6/13
 *Time: 0:24
 *Note：搜索详情
 */
namespace app\index\controller;

use think\Db;
use think\Request;

class Search extends Common
{
    public function index()
    {
        $param = request()->param();

        if (!isset($param['keyword']) || empty($param['keyword'])) {
            return $this->redirect('Index/index');
        }


        $map['status'] = 1;
        $map['name'] = array('like', "%" . addslashes($param['keyword']) . "%");
        $articleType = Db::table('article_type')->where($map)->field('id')->select();
        $articleTypeId = '';
        if ($articleType) {
            $articleTypeId = implode(',', array_column($articleType,'id'));
        }

        $maps['title|label|describe|content'] = array('like', "%" . addslashes($_GET['keyword']) . "%");

        // 文章列表
        $res = Db::table('article')
            ->where(['status' => 1])
            ->where(function ($query) use ($maps,$articleTypeId) {
                $query->where($maps)
                    ->whereor('pid','in',$articleTypeId);
            })
            ->order('id DESC')
            ->paginate($this->pageSize, false, ['query' => request()->param()]);

        // 点击排行
        $recommend = Db::table('article')
            ->where('status', 1)
            ->where('recommend', 1)
            ->orderRaw('rand()')
            ->limit(6)
            ->select();

        $this->assign('res', $res);
        $this->assign('recommend', $recommend);
        return $this->fetch();
    }
}