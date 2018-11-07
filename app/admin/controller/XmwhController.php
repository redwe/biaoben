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
use app\admin\model\XmwhModel;

class XmwhController extends AdminBaseController
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
        $XmwhModel = new XmwhModel();
        $list     = $XmwhModel->select();
        $this->assign('list', $list);
        return $this->fetch();
    }


    public function add(){
        $database="";
        $this->assign('database', $database);
        //$this->success('添加成功!');
        return $this->fetch('add');
    }

    public function addpost(){
        $param   = $this->request->post();
        $linkModel = new XmwhModel();
        $result    = $linkModel->validate(true)->save($param);
        if ($result === false) {
            $this->error($linkModel->getError());
        }
        $this->success("添加成功！", url("xmwh/index"));
    }

    public function edit()
    {
        $id        = $this->request->param('id', 0, 'intval');
        $XmwhModel = XmwhModel::get($id);
        $this->assign('xmwh', $XmwhModel);
        return $this->fetch();
    }

    public function editPost()
    {
        $data      = $this->request->param();
        $linkModel = new XmwhModel();
        $result    = $linkModel->validate(true)->allowField(true)->isUpdate(true)->save($data);
        if ($result === false) {
            $this->error($linkModel->getError());
        }
        $this->success("添加成功！", url("xmwh/index"));
    }

    public function  del(){
        $id = $this->request->param('id', 0, 'intval');
        XmwhModel::destroy($id);
        $this->success("删除成功！", url("xmwh/index"));
    }

}
