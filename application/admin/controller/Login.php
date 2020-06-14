<?php

namespace app\admin\controller;

use think\Cookie;
use think\Db;

class Login extends \think\Controller
{
    function __construct()
    {
        parent::__construct();
        session_start();
    }

    //登录页
    public function index()
    {
        /* 用户已是登录状态 */
        if (!empty($_SESSION['ADMIN_USER_INFO'])) {
            $this->redirect('Index/index');
        }
        return $this->fetch('login');
    }

    //登录页
    public function login()
    {
        /* 用户已是登录状态 */
        if (!empty($_SESSION['ADMIN_USER_INFO'])) {
            $this->redirect('Index/index');
        }
        return $this->fetch('login');
    }

    //登录
    public function loginout()
    {
        unset($_SESSION['ADMIN_USER_INFO']);
        $this->redirect('Login/index');

    }


    /**
     * Ajax提交登录信息
     */
    function ajaxLogin()
    {

        $username = addslashes($_POST['username']);
        $password = addslashes($_POST['password']);
        $ip = getIP();                      // 用户请求IP
        $time = date('Y-m-d H:i:s', time());

        /* 用户已是登录状态 */
        if (!empty($_SESSION['ADMIN_USER_INFO'])) {
            $return['msg'] = '你已经登录了，不要重复登录';
            $return['status'] = 0;
            exit(json_encode($return));
        }

        $res = Db::table('admin_user')
            ->join("admin_user_group", 'admin_user_group.id = admin_user.group_id', 'left')
            ->where('username', $username)
            ->field('admin_user.*, admin_user_group.group_name, admin_user_group.staff, admin_user_group.auth')
            ->find();

        /* 账号不存在 */
        if (empty($res)) {
            $return['msg'] = '账号不存在';
            $return['status'] = 0;

            exit(json_encode($return));
        }

        /* 登录密码错误 */
        $topass = md5(md5($res['salt']) . $password);

        if ($topass != $res['password']) {
            $return['msg'] = '登录密码错误';
            $return['status'] = 0;

            exit(json_encode($return));
        }

        /* 当前用户账号不可用 */
        if ($res['status'] != 1) {
            $return['msg'] = '当前账号登录异常，请联系管理员!';
            $return['status'] = 0;

            echo json_encode($return);
            exit();
        }

        /* 更新用户登录信息失败 */
        if (!Db::table('admin_user')->where('username', $username)->update(['last_login_time' => time(), 'last_login_ip' => $ip,])) {
            $return['msg'] = '登录失败';
            $return['status'] = 0;

            echo json_encode($return);
            exit();
        }

        /* 登录成功 */
        unset($res['password']);
        unset($res['salt']);
        $_SESSION['ADMIN_USER_INFO'] = $res;
        $_SESSION['EXPIRE_TIME'] = time() + 3600;   // 登录超时1小时
        if (Cookie::get('admin_return_url')) {
            $return_url = Cookie::get('admin_return_url');
        } else {
            $return_url = url('Index/index');
        }
        $return['msg'] = '登录成功!';
        $return['status'] = 1;
        $return['return_url'] = $return_url;

        echo json_encode($return);
    }
}
