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

class YwtjController extends AdminBaseController
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
        $where1 = ['adddo'=>"type"];
        $types = Db::name("options")->where($where1)->order("id DESC")->find();
        $this->assign('types', $types);

        $where2 = ['adddo'=>"com"];
        $coms = Db::name("options")->where($where2)->order("id DESC")->find();
        $this->assign('coms', $coms);

        if (!empty($request['com'])) {
            $where['b.com'] = $request['com'];
        }
        if (!empty($request['btype'])) {
            $where['b.btype'] = $request['btype'];
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

    public function edit()
    {
        $dashboardWidgets = [];
        $widgets          = $this->request->param('widgets/a');
        if (!empty($widgets)) {
            foreach ($widgets as $widget) {
                if ($widget['is_system']) {
                    array_push($dashboardWidgets, ['name' => $widget['name'], 'is_system' => 1]);
                } else {
                    array_push($dashboardWidgets, ['name' => $widget['name'], 'is_system' => 0]);
                }
            }
        }

        cmf_set_option('admin_dashboard_widgets', $dashboardWidgets, true);

        $this->success('更新成功!');

    }

}
