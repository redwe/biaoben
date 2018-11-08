<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use cmf\controller\AdminBaseController;
use think\Db;
use app\admin\model\Menu;

class XmglController extends AdminBaseController
{

    public function _initialize()
    {
        parent::_initialize();
    }

    //项目管理
    public function index()
    {
        $where['b.isdel']   = 0;
        $request = input('request.');

        $where2 = ['adddo'=>"com"];
        $coms = Db::name("options")->where($where2)->order("id DESC")->find();
        $this->assign('coms', $coms);

        $where2 = ['adddo'=>"pro"];
        $pros = Db::name("options")->where($where2)->order("id ASC")->select();
        $this->assign('pros', $pros);

        if (!empty($request['uname'])) {
            $where['b.uname'] = $request['uname'];
        }
        if (!empty($request['xid'])) {
            $where['b.xid'] = $request['xid'];
        }

        if (!empty($request['com'])) {
            $where['b.com'] = $request['com'];
        }

        $join=[
            ["project p","b.xid = p.xid"]
        ];
        $field = "b.*,p.project,p.mid";
        $usersQuery = Db::name('bbjs');
        if (!empty($request['date'])) {
            $dt = $request['date'];
            if($dt == '0'){
                $list = $usersQuery
                    ->alias('b')
                    ->field($field)
                    ->join($join)
                    ->where($where)
                    ->order("b.id DESC")
                    ->paginate(10);
            }
            else
            {
                $list = $usersQuery
                    ->alias('b')
                    ->field($field)
                    ->join($join)
                    ->where($where)
                    ->whereTime('create','>','-'.$dt.' days')
                    ->order("b.id DESC")
                    ->paginate(10);
            }
        }
        else
        {
            $list = $usersQuery
                ->alias('b')
                ->field($field)
                ->join($join)
                ->where($where)
                ->order("b.id DESC")
                ->paginate(10);
        }
        // 获取分页显示
        $page = $list->render();
        $this->assign('list', $list);
        $this->assign('page', $page);
        // 渲染模板输出
        return $this->fetch();
    }

    //修改页面
    public function edit()
    {
        $where = ['adddo'=>"com"];
        $coms = Db::name("options")->where($where)->order("id DESC")->find();
        $where = ['adddo'=>"type"];
        $ptype = Db::name("options")->where($where)->order("id DESC")->find();
        $where = ['adddo'=>"pro"];
        $pros = Db::name("options")->where($where)->order("id ASC")->select();

        $this->assign('coms', $coms);
        $this->assign('ptype', $ptype);
        $this->assign('pros', $pros);

        $join=[
            ["project p","b.xid = p.xid"]
        ];
        $field = "b.*,p.project,p.mid";

        $id    = $this->request->param('id', 0, 'intval');
        $member = DB::name('bbjs')
            ->alias('b')
            ->field($field)
            ->join($join)
            ->where(["b.id" => $id])
            ->find();
        $this->assign($member);
        return $this->fetch();
    }
//保存编辑的记录
    public function editPost()
    {
        if ($this->request->isPost()) {
            $id = $this->request->param('id', 0, 'intval');
            $_POST['create'] = date("Y-m-d H:i:s",time());
            $_POST['isdel'] = 0;
            $_POST['status'] = '';
            $result = $this->validate($this->request->param(), 'Bbjs');
            //dump($_POST);
            if ($result !== true) {
                $this->error($result);
            } else {
                $where["id"]=$id;
                $data0 = [
                    "xid"=>$_POST['xid'],
                    "uname"=>$_POST['uname'],
                    "sex"=>$_POST['sex'],
                    "age"=>$_POST['age'],
                    "mobile"=>$_POST['mobile'],
                    "com"=>$_POST['com'],
                    "linc"=>$_POST['linc'],
                    "mark"=>$_POST['mark'],
                    "code"=>$_POST['code'],
                    "block"=>$_POST['block'],
                    "btype"=>$_POST['btype'],
                    "isdel"=>0,
                    "status"=>0,
                    "create"=>date("Y-m-d h:i:s")
                ];
                $result    = DB::name('Bbjs')->where($where)->update($data0);
                $mids = $_POST['mid'];
                $pids = $_POST['pid'];
                $pname = $_POST['pname'];
                $project = $_POST['project'];
                $marray1 = [];
                $marray2 = [];
                for($i = 0;$i<count($pids);$i++){
                    $marray1[$pids[$i]] = $mids[$i];
                    $marray2[$pname[$i]] = $project[$i];
                }
                $data2 = [
                    "xid"=>$_POST['xid'],
                    "mid"=>json_encode($marray1,JSON_UNESCAPED_UNICODE),
                    "project"=>json_encode($marray2,JSON_UNESCAPED_UNICODE)
                ];
                $where1["bid"]=$id;
                DB::name('project')->where($where1)->update($data2);
                $this->success("编辑成功！", url("bbjs/bbgl"));
            }
        }
    }

    //删除一条记录
    public function del()
    {
        $id = input('param.id', 0, 'intval');
        if ($id) {
            $result = Db::name("bbjs")->where(["id" => $id])->setField('isdel', 1);
            if ($result) {
                $this->success("删除成功！", "bbjs/bbgl");
            } else {
                $this->error('删除失败,信息不存在或参数错误！');
            }
        } else {
            $this->error('数据传入失败！');
        }
    }

    public function djlb()
    {
        $where['b.isdel']   = 0;
        $where['b.status']   = array("<",2);
        $request = input('request.');

        $where2 = ['adddo'=>"com"];
        $coms = Db::name("options")->where($where2)->order("id DESC")->find();
        $where2 = ['adddo'=>"pro"];
        $pros = Db::name("options")->where($where2)->order("id ASC")->select();
        $this->assign('coms', $coms);
        $this->assign('pros', $pros);

        if (!empty($request['pid'])) {
            $where['p.pid'] = array("like","%".$request['pid']."%");
        }
        if (!empty($request['com'])) {
            $where['b.com'] = $request['com'];
        }
        $join=[
            ["project p","b.xid = p.xid"]
        ];
        $field = "b.*,p.project,p.mid";
        $usersQuery = Db::name('bbjs');
        $list = $usersQuery
            ->alias('b')
            ->field($field)
            ->join($join)
            ->where($where)
            ->order("b.id DESC")
            ->paginate(10);
        // 获取分页显示
        $page = $list->render();
        $this->assign('list', $list);
        $this->assign('page', $page);
        // 渲染模板输出
        return $this->fetch();
    }

    public function jcz()
    {
        $where['b.isdel']   = 0;
        $where['b.status']   = 2;
        $request = input('request.');

        $where2 = ['adddo'=>"com"];
        $coms = Db::name("options")->where($where2)->order("id DESC")->find();
        $where2 = ['adddo'=>"pro"];
        $pros = Db::name("options")->where($where2)->order("id ASC")->select();
        $this->assign('coms', $coms);
        $this->assign('pros', $pros);

        if (!empty($request['pid'])) {
            $where['p.pid'] = array("like","%".$request['pid']."%");
        }
        if (!empty($request['com'])) {
            $where['b.com'] = $request['com'];
        }
        $join=[
            ["project p","b.xid = p.xid"]
        ];
        $field = "b.*,p.project,p.mid";
        $usersQuery = Db::name('bbjs');
        $list = $usersQuery
            ->alias('b')
            ->field($field)
            ->join($join)
            ->where($where)
            ->order("b.id DESC")
            ->paginate(10);
        // 获取分页显示
        $page = $list->render();
        $this->assign('list', $list);
        $this->assign('page', $page);
        // 渲染模板输出
        return $this->fetch();
    }

    public function jcwc()
    {
        $where['b.isdel']   = 0;
        $where['b.status']   = 3;
        $request = input('request.');

        $where2 = ['adddo'=>"com"];
        $coms = Db::name("options")->where($where2)->order("id DESC")->find();
        $where2 = ['adddo'=>"pro"];
        $pros = Db::name("options")->where($where2)->order("id ASC")->select();
        $this->assign('coms', $coms);
        $this->assign('pros', $pros);

        if (!empty($request['pid'])) {
            $where['p.pid'] = array("like","%".$request['pid']."%");
        }
        if (!empty($request['com'])) {
            $where['b.com'] = $request['com'];
        }
        $join=[
            ["project p","b.xid = p.xid"]
        ];
        $field = "b.*,p.project,p.mid";
        $usersQuery = Db::name('bbjs');
        $list = $usersQuery->alias('b')
            ->field($field)
            ->join($join)
            ->where($where)
            ->order("b.id DESC")
            ->paginate(10);
        // 获取分页显示
        $page = $list->render();
        $this->assign('list', $list);
        $this->assign('page', $page);
        // 渲染模板输出
        return $this->fetch();
    }

    public function jdcx()
    {
        $where['b.isdel']   = 0;
        $request = input('request.');
        if (!empty($request['uname'])) {
            $where['b.uname'] = $request['uname'];
        }
        if (!empty($request['xid'])) {
            $where['b.xid'] = $request['xid'];
        }
        if(isset($request['status']) && $request['status']!="all"){
            if(empty($request['status'])){
                $status = 0;
            }
            else
            {
                $status = 1;
            }
            $where['b.status'] = $status;     //intval($request['status']);
        }
        $join=[
            ["project p","b.xid = p.xid"]
        ];
        $field = "b.*,p.project,p.mid";
        $usersQuery = Db::name('bbjs');
        if (!empty($request['date'])) {
            $dt = $request['date'];
            if($dt == '0'){
                $list = $usersQuery
                    ->alias('b')
                    ->field($field)
                    ->join($join)
                    ->where($where)
                    ->order("b.id DESC")
                    ->paginate(10);
            }
            else
            {
                $list = $usersQuery
                    ->alias('b')
                    ->field($field)
                    ->join($join)
                    ->where($where)
                    ->whereTime('create','>','-'.$dt.' days')
                    ->order("b.id DESC")
                    ->paginate(10);
            }
        }
        else
        {
            $list = $usersQuery
                ->alias('b')
                ->field($field)
                ->join($join)
                ->where($where)
                ->order("b.id DESC")
                ->paginate(10);
        }
        // 获取分页显示
        $page = $list->render();
        $this->assign('list', $list);
        $this->assign('page', $page);
        // 渲染模板输出
        return $this->fetch();
    }

}
