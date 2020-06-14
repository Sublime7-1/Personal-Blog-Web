<?php
namespace app\index\controller;

use think\Db;
use think\Request;

class Index extends Common
{
    public function index()
    {
    	// 轮播图
    	$carousel = Db::table('carousel')->where('status', 1)->order('sort ASC')->select();

    	// 热门标签
        $hotTags = [];
    	$hotTagAll = explode('、', getConfigValue('hotTags'));
        foreach ($hotTagAll as $k => $v) {
            $hotTags[$v] = 0;
            $artile_type = Db::table('article_type')->where('name', $v)->find();
            if ($artile_type) {
                $hotTags[$v] = $artile_type['id'];
            }
    	}

    	// 友情链接
    	$link = Db::table('link')->where('status', 1)->select();

        // 本站运行天数
        $webStartTime   = getConfigValue('webStartTime');
        $webRunDay      = diffBetweenTwoDays(time(), $webStartTime);

        // 本月更新文章数量
        $monthBegin = date('Y-m-01', strtotime(date("Y-m-d")));
        $monthEnd   = date('Y-m-d', strtotime("$monthBegin +1 month -1 day"));
        $monthBeginTime = strtotime($monthBegin);
        $monthEndTime   = strtotime($monthEnd) + 86399;
        $monthArticleNum = Db::table('article')->wherebetween('create_time', [$monthBeginTime, $monthEndTime])->count();

        // 全部文章数量
        $allArticleNum = Db::table('article')->count();

        // 每日一句
        $daySentence = Db::table('day_sentence')->where('status', 1)->orderRaw('rand()')->find();

        // 特别推荐
        $recommend = Db::table('article')
        ->where('status', 1)
        ->where('recommend', 1)
        ->orderRaw('rand()')
        ->limit(6)
        ->select();

        // 最新发布
        $newArticle = Db::table('article')
                ->join('article_type', 'article.pid = article_type.id', 'LEFT')
                ->where('article.status', 1)
                ->order('id', 'DESC')
                ->limit(6)
                ->field('article.*, article_type.name as atName')
                ->select();

    	$this->assign('carousel', $carousel);
    	$this->assign('hotTags', $hotTags);
        $this->assign('link', $link);
        $this->assign('webRunDay', $webRunDay);
        $this->assign('monthArticleNum', $monthArticleNum);
        $this->assign('allArticleNum', $allArticleNum);
        $this->assign('daySentence', $daySentence);
        $this->assign('recommend', $recommend);
        $this->assign('newArticle', $newArticle);
        return $this->fetch();
    }

}
