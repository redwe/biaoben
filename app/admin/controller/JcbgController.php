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

class JcbgController extends AdminBaseController
{

    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     *  后台欢迎页
     */
    public function index()
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
    public function bgsh()
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

    public function bgcx()
    {
        return $this->fetch();
    }

    public function fsyj()
    {
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

}
