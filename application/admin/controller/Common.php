<?php

namespace app\admin\controller;

use think\Collection;
use think\Cookie;
use think\Db;
use think\Request;

class Common extends \think\Controller
{

    public $staffPost = array('', '超级管理员', '普通管理员');
    public $activePost = array('未发布', '已发布', '已失效', '删除');
    public $adminInfo;
    public $pageSize = 20;

    function __construct()
    {
        parent::__construct();
        session_start();
        $request = Request::instance();
        if (empty($_SESSION['ADMIN_USER_INFO']) and request()->method() == 'GET') {
            Cookie::set('admin_return_url', $request->url(true));
            $this->redirect('Login/index');
        }
        $this->adminInfo = $_SESSION['ADMIN_USER_INFO'];

        // if (!$this->checkAuth()) {
        //     $this->error('您操作没有权限', 'Index/index');
        // }
        // $menus = $this->getUserMenus();
        // $headmenus = $this->getUserMenus(1);
        // $this->assign('menus', $menus);
        // $this->assign('headmenus', $headmenus);

        $this->assign('adminInfo', $this->adminInfo);
        $this->assign('action', strtolower($request->action()));
        $this->assign('controller', strtolower($request->controller()));
    }

    /**
     * 获取后台用户可操作菜单
     * @return array   返回当前登录账号可操作菜单项
     */
    protected function getUserMenus($type = 0)
    {

        if (!$this->adminInfo) {
            return null;
        }
        $gid = $this->adminInfo['group_id'];
        $result = Db::table('admin_user_group')->where(array('id' => $gid))->find();

        if (empty($result['auth'])) {
            return array();
        }
        $map = array('id' => array('in', $result['auth']), 'status' => 1);
        $request = \think\Request::instance();
        $parent['id'] = 0;
        if ($type == 1) {
            $map['parent_id'] = 0;
        } else {
            $map['controller'] = $request->controller();
            $map['parent_id'] = array('neq', 0);
            $parent = Db::table('admin_menu')->where(array('parent_id' => 0, 'controller' => $request->controller()))->find();
        }

        $result = Db::table('admin_menu')->where($map)->order('display_index desc')->select();

        $menus = $this->getSubMenus($result, $parent['id']);
        return $menus;
    }

    /**
     * 获取分级的用户操作菜单
     * @param array $menus 分级前的用户菜单
     * @param int $pid 菜单的上一级ID
     * @return array 返回分级后的菜单
     */
    protected function getSubMenus($menus = array(), $pid = 0)
    {
        $subMenus = array();
        foreach ($menus as $k => $menu) {
            if ($menu['parent_id'] == $pid) {
                $sub = $this->getSubMenus($menus, $menu['id']);
                $subMenus[$menu['id']]['id'] = $menu['id'];
                $subMenus[$menu['id']]['name'] = $menu['name'];
                $subMenus[$menu['id']]['parent_id'] = $menu['parent_id'];
                if ($menu['parent_id'] == 0) {
                    $subMenus[$menu['id']]['url'] = $menu['controller'] . '/' . $menu['action'];
                } else {
                    $subMenus[$menu['id']]['url'] = $menu['url'];
                }

                $subMenus[$menu['id']]['controller'] = $menu['controller'];
                $subMenus[$menu['id']]['action'] = $menu['action'];
                $subMenus[$menu['id']]['icon'] = $menu['icon'];
                $subMenus[$menu['id']]['display_index'] = $menu['display_index'];
                $subMenus[$menu['id']]['status'] = $menu['status'];
                $subMenus[$menu['id']]['sub'] = $sub;
            }
        }
        return $subMenus;
    }

    protected function checkAuth()
    {
        $request = \think\Request::instance();
        $url = $request->controller() . '/' . $request->action();
        $authId = Db::table('admin_menu')->where(array('url' => $url, 'needAuth' => 1))->find();
        if (!$authId['id']) {
            return true;
        }

        $gid = $this->adminInfo['group_id'];
        $auth = Db::table('admin_user_group')->where(array('id' => $gid))->find();
        $authArr = explode(',', $auth['auth']);
        if (in_array($authId['id'], $authArr)) {
            return true;

        } else {
            return false;
        }
    }

    protected function actionLog($actionName = '', $desc = '', $user = '', $log = '')
    {
        $data['action_name'] = $actionName;
        $data['desc'] = $desc;
        $data['userid'] = $user;
        $data['log'] = $log;
        $data['createtime'] = time();
        Db::table('admin_action_log')->insert($data);
    }

    //随机生成字符串
    protected function randStr($length = 4)
    {
        $pattern = 'abcdefghijklmnopqrstuvwxyz123456789';
        $key = '';
        for ($i = 0; $i < $length; $i++) {
            $key .= $pattern[mt_rand(0, strlen($pattern) - 1)];
        }
        return $key;
    }

    /**
     * 获取留言相关的所有留言
     * @param $arr
     * @param int $type 1之前的 2之后的
     * @param array $allMsg 留言数组
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    protected function getMessage($arr, $type = 1, &$allMsg = [])
    {
        if ($type == 1) {
            if ($arr['pid']) {
                $res = Db::table('message_board')->where('id', $arr['pid'])->find();
                $allMsg[] = $res;
                $this->getMessage($res, $type, $allMsg);
            }
        } else {
            $res = Db::table('message_board')->where('pid', $arr['id'])->select();
            if ($res) {
                foreach ($res as $k => $v) {
                    $allMsg[] = $v;
                    $this->getMessage($v, $type, $allMsg);
                }
            }
        }

        return $allMsg;
    }
}