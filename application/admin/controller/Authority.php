<?php

namespace app\admin\controller;

use think\Db;
use think\Request;

class Authority extends Common
{

	/**
	 * 权限组管理
	 * @return [type] [description]
	 */
   	public function authGroup()
   	{
   		$map = array('status' => 1);

        if (isset($_GET['keyword'])) {
            $map['group_name'] = array('like', "%" . addslashes($_GET['keyword']) . "%");
        }

        $res = Db::table('admin_user_group')
	        ->where($map)
	        ->order('edit_time DESC')
	        ->paginate($this->pageSize, false, ['query' => request()->param()]);

        $this->assign('res', $res);
        return $this->fetch();
   	}

   	//管理员组添加
    public function addGroup()
    {
        $result = Db::table('admin_menu')->select();
        $auths = $this->getSubMenus($result);
        $staffPost = $this->staffPost;
        unset($staffPost[0]);

        $this->assign('staffPost', $staffPost);
        $this->assign('auths', $auths);
        return $this->fetch();
    }

   	//管理员组编辑
    public function editGroup()
    {
        if (!empty($_GET['id'])) {
            $map = array('status' => 1);
            $map['id'] = (int)$_GET['id'];
            $group = Db::table('admin_user_group')->where($map)->find();
            $group['auths'] = explode(',', $group['auth']);
            $this->assign('group', $group);
        } else {
            $this->error('请求错误');
        }
        $result = Db::table('admin_menu')->select();
        $auths = $this->getSubMenus($result);

        $staffPost = $this->staffPost;
        unset($staffPost[0]);

        $this->assign('staffPost', $staffPost);
        $this->assign('auths', $auths);
        return $this->fetch();
    }

    //管理员组保存
    public function saveGroup()
    {
        $data['group_name'] = addslashes($_POST['group_name']);
        if (!empty($_POST['id'])) {
            $data['id'] = array('neq', (int)$_POST['id']);
        }
        $name = Db::table('admin_user_group')->where($data)->find();
        if ($name) {
            $this->error('管理员组已存在');
        }
        unset($data['id']);
        $data['staff'] = intval($_POST['staff']);
        $data['desc'] = addslashes($_POST['desc']);
        $data['auth'] = implode(',', $_POST['access']);
        $data['edit_time'] = time();

        if (!empty($_POST['id'])) {
            $map['id'] = (int)$_POST['id'];
            if (Db::table('admin_user_group')->where($map)->update($data)) {
                //更新当前登陆人的信息
                //若当前更改的组是当前登录人的组。则修改当前登录信息
                if ($map['id'] == $this->adminInfo['group_id']) {
                    $this->adminInfo['auth'] = $data['auth'];
                    $_SESSION['ADMIN_USER_INFO']['auth'] = $data['auth'];
                }
                $this->actionLog('修改管理员组', 'Authority', $this->adminInfo['id'], serialize($_POST));
                $this->success('修改成功', 'Authority/authGroup');
            } else {
                $this->error('修改失败');
            }
        } else {
            if (Db::table('admin_user_group')->insert($data)) {
                $this->actionLog('添加管理员组', 'Authority', $this->adminInfo['id'], serialize($_POST));
                $this->success('添加成功', 'Authority/authGroup');
            } else {
                $this->error('添加失败');
            }
        }
    }

   	//删除权限组
    public function delGroup()
    {
        if (!empty($_GET['id'])) {
            $map['id'] = (int)$_GET['id'];
            $admin = Db::table('admin_user_group')->where($map)->find();
            $_GET['content'] = '删除管理员：' . $admin['group_name'];
            if (Db::table('admin_user_group')->where($map)->update(array('status' => 0))) {
                $this->actionLog('删除管理员组', 'Authority', $this->adminInfo['id'], serialize($_GET));
                $this->success('删除成功', 'Authority/authGroup');;
            } else {
                $this->error('删除失败');
            }
        } else {
            $this->error('请求错误');
        }
    }

    //管理员日志列表
    public function userLog()
    {
    	$map = [];
        if (isset($_GET['keyword'])) {
            $map['a.action_name|u.username|a.action_name'] = array('like', "%" . addslashes($_GET['keyword']) . "%");
        }
        $list = Db::table('admin_action_log')
            ->alias('a')
            ->where($map)
            ->join('admin_user u', 'a.userid = u.id')
            ->field('a.*,u.username')
            ->order('a.id desc')
            ->paginate($this->pageSize, false, ['query' => request()->param()]);
        $this->assign('list', $list);
        return $this->fetch();
    }

    //后台管理员列表
    public function userList()
    {
        $map = [];
        if (isset($_GET['keyword'])) {
            $map['a.username|g.group_name'] = array('like', "%" . addslashes($_GET['keyword']) . "%");
        }
        $userList = Db::table('admin_user')
            ->alias('a')
            ->where($map)
            ->join('admin_user_group g', 'a.group_id = g.id', 'LEFT')
            ->field('a.*,g.group_name')
            ->order('last_login_time desc')
            ->paginate($this->pageSize, false, ['query' => request()->param()]);

        $this->assign('userList', $userList);
        return $this->fetch();
    }

    //新增管理员
    public function addUser()
    {
        $groups = Db::table('admin_user_group')->where('status', 1)->select();

        $this->assign('groups', $groups);
        return $this->fetch();
    }

    //编辑管理员
    public function editUser(Request $request)
    {
        $data = $request->param('', '', 'trim');

        if (empty($data['id'])) {
            return $this->error('请求错误');
        }
        $map['a.id'] = $data['id'];

        $user = Db::table('admin_user')
            ->alias('a')
            ->where($map)
            ->join('admin_user_group g', 'a.group_id = g.id')
            ->field('a.*,g.group_name')
            ->find();

        $groups = Db::table('admin_user_group')->where('status', 1)->select();

        $this->assign('user', $user);
        $this->assign('groups', $groups);
        return $this->fetch();
    }

    //保存管理员
    public function saveUser()
    {
        //$user = array();
        $data['tel'] = addslashes($_POST['tel']);
        $data['realname'] = addslashes($_POST['realname']);
        $data['username'] = addslashes($_POST['username']);
        $data['group_id'] = intval($_POST['group_id']);
        if (!empty($_POST['id'])) {
            $map['id'] = (int)$_POST['id'];
            if (!empty($_POST['password'])) {
                $user = Db::table('admin_user')->where($map)->find();
                $data['password'] = md5(md5($user['salt']) . $_POST['password']);
            }
            if (Db::table('admin_user')->where($map)->update($data)) {
                $this->actionLog('修改管理员', 'Admin', $this->adminInfo['id'], serialize($_POST));
                $this->success('修改成功', 'Authority/userList');;
            } else {
                $this->error('修改失败');
            }
        } else {
            if (empty($_POST['password'])) {
                $this->error('请输入密码');
            }
            $data['salt'] = $this->randStr(4);
            $data['status'] = 1;
            $data['password'] = md5(md5($data['salt']) . addslashes($_POST['password']));

            $user = Db::table('admin_user')->where("username", $_POST['username'])->find();
            if ($user) {
                return $this->error('该用户名已经存在');
            }
            if (Db::table('admin_user')->insert($data)) {
                $this->actionLog('添加管理员', 'Admin', $this->adminInfo['id'], serialize($_POST));
                $this->success('添加成功', 'Authority/userList');
            } else {
                $this->error('添加失败');
            }
        }
    }

    //删除管理员
    public function delUser()
    {
        if (!empty($_GET['id'])) {
            $map['id'] = (int)$_GET['id'];
            $info = Db::table('admin_user')->where($map)->find();
            $_GET['content'] = '删除管理员：' . $info['username'];
            if (Db::table('admin_user')->where($map)->delete()) {
                $this->actionLog('删除管理员', 'Admin', $this->adminInfo['id'], serialize($_GET));
                $this->success('删除成功', 'Authority/userList');;
            } else {
                $this->error('删除失败');
            }
        } else {
            $this->error('请求错误');
        }
    }
}
