<?php
namespace app\admin\controller;

use think\Db;
use think\Request;

/**
 * 每日一句控制器
 */
class DaySentence extends Common
{
    public function api()
    {
        $url = "http://sentence.iciba.com/index.php?c=dailysentence&m=getdetail&title=";

        $err = [];

        $i = 1;
        /*只有2018--*/
        while($i <= 365) {
            $time = strtotime('2018-01-01');
            $date = date('Y-m-d', $time - $i * 86400);
            $urls  = $url.$date;

            $res  = file_get_contents($urls);
            $res  = json_decode($res, true);

            if (Db::table('day_sentence')->where('content', $res['note'])->find()) {
                $err[] = $res['note'];
                $i++;
                continue;
            }

            if ($res) {
                $data = array(
                    'uid'           =>1,
                    'type'          =>1,
                    'status'        =>1,
                    'examine_time'  =>time(),
                    'create_time'   =>time(),
                    'content'       =>!empty($res['note']) ? $res['note'] : 1,
                    'content_en'    =>!empty($res['content']) ? $res['content'] : 1,
                    'sentence_date' =>!empty($res['title']) ? $res['title'] : 1
                );

                Db::table('day_sentence')->insert($data);
            }

            $i++;
        }

        dunm($err);
    }

    public function index()
    {
    	$data = request()->get('', '', 'trim');

        $map = [];
        if (isset($data['keyword'])) {
            $map['content|content_en'] = array('like', "%" . addslashes($_GET['keyword']) . "%");
        }

    	$res = Db::table('day_sentence')
            ->alias('ds')
            ->join('users u', 'u.id = ds.uid and ds.type = 2', 'LEFT')
            ->join('admin_user au', 'au.id = ds.uid and ds.type = 1', 'LEFT')
            ->where($map)
            ->where('ds.status', '<>', 3)
            ->field('ds.*, u.name, au.username')
            ->order('ds.id DESC')
            ->paginate($this->pageSize, false, ['query' => request()->param()]);

    	$this->assign('res',$res);
        return $this->fetch();
    }

    public function add()
    {
        return $this->fetch();
    }

    public function save()
    {
        $data = request()->post('', '', 'trim');

        $data['uid']          = $this->adminInfo['id'];
        $data['type']         = 1;
        $data['status']       = 0;
        $data['create_time']  = time();

        $insert_id = Db::table('day_sentence')->insert($data);
        if ($insert_id) {
            $this->actionLog('新增每日一句', 'day_sentence', $this->adminInfo['id'], serialize($data));
            $this->success('操作成功', 'DaySentence/Index');
        } else {
            $this->error('操作失败');
        }
    }

    public function edit()
    {
        $id = (int)$_GET['id'];

        $res = Db::table('day_sentence')
            ->alias('ds')
            ->join('users u', 'u.id = ds.uid and ds.type = 2', 'LEFT')
            ->join('admin_user au', 'au.id = ds.uid and ds.type = 1', 'LEFT')
            ->join('admin_user examine', 'examine.id = ds.examine_user', 'LEFT')
            ->field('ds.*, u.name, au.username, examine.username as examine_user_name')
            ->where(['ds.id' => $id])
            ->find();

        if (!$res) {
            $this->error('数据错误！');
        }

        $this->assign('res', $res);
        return $this->fetch();
    }

    public function examine()
    {
        $param = request()->post('', '', 'trim');

        if (!isset($param['id'])) {
            $this->error('参数丢失');
        } else {
            $id           = (int)$param['id'];
            $status       = (int)$param['status'];
            $examine_user = (int)$this->adminInfo['id'];

            $result = Db::table('day_sentence')->where(['id' => $id])->update(['status'=>$status,'examine_time'=>time(),'examine_user'=>$examine_user]);

            if ($result === false) {
                return json(['status'=>0, 'msg'=>'审核失败']);
            }
            $this->actionLog('审核每日一句', 'day_sentence', $this->adminInfo['id'], serialize($param));
        }

        return json(['status'=>1, 'msg'=>'审核成功']);
    }

    public function delete($id)
    {
        $res = Db::table('day_sentence')->where('id', $id)->update(['status'=>3]);

        if ($res) {
            $this->actionLog('删除每日一句', 'DaySentence', $this->adminInfo['id'], serialize($id));
            $this->success('删除成功');
        } else {
            $this->error('删除失败');
        }
    }
}
