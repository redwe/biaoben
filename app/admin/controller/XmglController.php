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
        $where['isdel']   = 0;
        $request = input('request.');
        if (!empty($request['uname'])) {
            $where['uname'] = $request['uname'];
        }
        if (!empty($request['xid'])) {
            $where['xid'] = $request['xid'];
        }
        if(isset($request['status']) && $request['status']!="all"){
            if(empty($request['status'])){
                $status = 0;
            }
            else
            {
                $status = 1;
            }
            $where['status'] = $status;     //intval($request['status']);
        }
        $usersQuery = Db::name('bbjs');
        if (!empty($request['date'])) {
            $dt = $request['date'];
            if($dt == '0'){
                $list = $usersQuery->where($where)->order("id DESC")->paginate(10);
            }
            else
            {
                $list = $usersQuery->where($where)->whereTime('create','>','-'.$dt.' days')->order("id DESC")->paginate(10);
            }
        }
        else
        {
            $list = $usersQuery->where($where)->order("id DESC")->paginate(10);
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
        $id    = $this->request->param('id', 0, 'intval');
        $member = DB::name('bbjs')->where(["id" => $id])->find();
        $this->assign($member);
        return $this->fetch();
    }

    public function djlb()
    {
        $where['isdel']   = 0;
        $where['status']   = array("<",2);
        $request = input('request.');

        $where2 = ['adddo'=>"com"];
        $coms = Db::name("options")->where($where2)->order("id DESC")->find();
        $where2 = ['adddo'=>"pro"];
        $pros = Db::name("options")->where($where2)->order("id ASC")->select();
        $this->assign('coms', $coms);
        $this->assign('pros', $pros);

        if (!empty($request['pid'])) {
            $where['pid'] = array("like","%".$request['pid']."%");
        }
        if (!empty($request['com'])) {
            $where['com'] = $request['com'];
        }
        $usersQuery = Db::name('bbjs');
        $list = $usersQuery->where($where)->order("id DESC")->paginate(10);
        // 获取分页显示
        $page = $list->render();
        $this->assign('list', $list);
        $this->assign('page', $page);
        // 渲染模板输出
        return $this->fetch();
    }

    public function jcz()
    {
        $where['isdel']   = 0;
        $where['status']   = 2;
        $request = input('request.');

        $where2 = ['adddo'=>"com"];
        $coms = Db::name("options")->where($where2)->order("id DESC")->find();
        $where2 = ['adddo'=>"pro"];
        $pros = Db::name("options")->where($where2)->order("id ASC")->select();
        $this->assign('coms', $coms);
        $this->assign('pros', $pros);

        if (!empty($request['pid'])) {
            $where['pid'] = array("like","%".$request['pid']."%");
        }
        if (!empty($request['com'])) {
            $where['com'] = $request['com'];
        }
        $usersQuery = Db::name('bbjs');
        $list = $usersQuery->where($where)->order("id DESC")->paginate(10);
        // 获取分页显示
        $page = $list->render();
        $this->assign('list', $list);
        $this->assign('page', $page);
        // 渲染模板输出
        return $this->fetch();
    }

    public function jcwc()
    {
        $where['isdel']   = 0;
        $where['status']   = 3;
        $request = input('request.');

        $where2 = ['adddo'=>"com"];
        $coms = Db::name("options")->where($where2)->order("id DESC")->find();
        $where2 = ['adddo'=>"pro"];
        $pros = Db::name("options")->where($where2)->order("id ASC")->select();
        $this->assign('coms', $coms);
        $this->assign('pros', $pros);

        if (!empty($request['pid'])) {
            $where['pid'] = array("like","%".$request['pid']."%");
        }
        if (!empty($request['com'])) {
            $where['com'] = $request['com'];
        }
        $usersQuery = Db::name('bbjs');
        $list = $usersQuery->where($where)->order("id DESC")->paginate(10);
        // 获取分页显示
        $page = $list->render();
        $this->assign('list', $list);
        $this->assign('page', $page);
        // 渲染模板输出
        return $this->fetch();
    }

    public function jdcx()
    {
        $where['isdel']   = 0;
        $request = input('request.');
        if (!empty($request['uname'])) {
            $where['uname'] = $request['uname'];
        }
        if (!empty($request['xid'])) {
            $where['xid'] = $request['xid'];
        }
        if(isset($request['status']) && $request['status']!="all"){
            if(empty($request['status'])){
                $status = 0;
            }
            else
            {
                $status = 1;
            }
            $where['status'] = $status;     //intval($request['status']);
        }
        $usersQuery = Db::name('bbjs');
        if (!empty($request['date'])) {
            $dt = $request['date'];
            if($dt == '0'){
                $list = $usersQuery->where($where)->order("id DESC")->paginate(10);
            }
            else
            {
                $list = $usersQuery->where($where)->whereTime('create','>','-'.$dt.' days')->order("id DESC")->paginate(10);
            }
        }
        else
        {
            $list = $usersQuery->where($where)->order("id DESC")->paginate(10);
        }
        // 获取分页显示
        $page = $list->render();
        $this->assign('list', $list);
        $this->assign('page', $page);
        // 渲染模板输出
        return $this->fetch();
    }

}
