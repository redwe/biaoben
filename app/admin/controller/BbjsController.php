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

class BbjsController extends AdminBaseController
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
        $where = ['adddo'=>"com"];
        $coms = Db::name("options")->where($where)->order("id DESC")->find();
        $where = ['adddo'=>"type"];
        $ptype = Db::name("options")->where($where)->order("id DESC")->find();
        $where = ['adddo'=>"pro"];
        $pros = Db::name("options")->where($where)->order("id ASC")->select();

        $codes = Db::name("bbjs")->order("id DESC")->find();
        $codeOld = $codes['xid'];
        if(!empty($codeOld)){
            $codeOld2 = explode("-",$codeOld);
            $m = intval($codeOld2[1]);
        }
        else
        {
            $m = 0;
        }

        $n = 1;
        $x = 6;
        $tempcode = getXMCode($m,$n,$x);   //参数m为起始号，n为0则随机产生，为1则返回m的下一位数，x位总长度，位数不足则前面补零
        $tm = date("y");        //取年度
        $xmcode = "X".$tm."-".$tempcode;

        $this->assign('xmcode', $xmcode);
        $this->assign('coms', $coms);
        $this->assign('ptype', $ptype);
        $this->assign('pros', $pros);
        return $this->fetch();
    }

    public function addPost()
    {
        if ($this->request->isPost()) {
            $_POST['create'] = date("Y-m-d H:i:s",time());
            $_POST['pid'] = serialize($_POST['pid']);
            $_POST['project'] = serialize($_POST['project']);           //serialize序列化，反序列化unserialize
            $_POST['isdel'] = 0;
            $_POST['status'] = '';
            $result = $this->validate($this->request->param(), 'Bbjs');
            //dump($_POST);
            if ($result !== true) {
                $this->error($result);
            } else {
                $result    = DB::name('Bbjs')->insertGetId($_POST);
                $this->success("添加成功！", url("bbjs/index"));
            }
        }
    }

//标本管理
	public function bbgl()
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
//保存编辑的记录
    public function editPost()
    {
        if ($this->request->isPost()) {
            $id = $this->request->param('id', 0, 'intval');
            $_POST['create'] = date("Y-m-d H:i:s",time());
            $_POST['project'] = serialize($_POST['project']);           //serialize序列化，反序列化unserialize
            $_POST['isdel'] = 0;
            $_POST['status'] = '';
            $result = $this->validate($this->request->param(), 'Bbjs');
            //dump($_POST);
            if ($result !== true) {
                $this->error($result);
            } else {
                $result    = DB::name('Bbjs')->update($_POST);
                $this->success("编辑成功！", url("bbjs/bbgl"));
            }
        }
    }
    //选项设置
    public function addcom(){
        $adddo = $this->request->param("adddo");
        $id = $this->request->param("id");
        if($id){
            $where = ['id'=>$id];
            $list2 = Db::name("options")->where($where)->find();
            $this->assign('list2', $list2);
        }
         $where = ['adddo'=>$adddo];
         if($adddo == "pro"){
             $codes = Db::name("options")->order("id DESC")->find();
             $codeOld = $codes['pcode'];
             if(!empty($codeOld)){
                 $codeOld2 = explode("-",$codeOld);
                 $m = intval($codeOld2[1]);
             }
             else
             {
                 $m = 0;
             }
             $n = 1;
             $x = 6;
             $tempcode = getXMCode($m,$n,$x);   //参数m为起始号，n为0则随机产生，为1则返回m的下一位数，x位总长度，位数不足则前面补零
             $tm = date("y");        //取年度
             $mcode = "M".$tm."-".$tempcode;

             $this->assign('mcode', $mcode);
             $list = Db::name("options")->where($where)->order("id DESC")->select();
         }
         else
            {
                $list = Db::name("options")->where($where)->find();
            }
        $this->assign('id', $id);
        $this->assign('list', $list);
        $this->assign('adddo', $adddo);
        return $this->fetch();
    }
    //保存选项
    public function addCompost()
    {
        if ($this->request->isPost()) {
                $adddo = $this->request->param("adddo");
                $pname = $this->request->param("pname");
                $id = $this->request->param("id");

            if($id){
                $where['id'] = $id;
                $res = DB::name('options')->where($where)->update($_POST);
            }
            else
            {
                $where['pname'] = $pname;
                $result = DB::name('options')->where($where)->find();
                if($result){
                    $res = DB::name('options')->where($where)->update($_POST);
                }
                else
                {
                    $res = DB::name('options')->insertGetId($_POST);
                }
            }
            $this->success("设置成功！", url("admin/bbjs/addcom",array("adddo"=>$adddo)));
        }
    }

    public function delcom()
    {
        $id = input('param.id', 0, 'intval');
        if ($id) {
            $result = Db::name("options")->where(["id" => $id])->delete();
            if ($result) {
                $this->success("删除成功！", url("admin/bbjs/addcom",array("adddo"=>"pro")));
            } else {
                $this->error('删除失败,信息不存在或参数错误！');
            }
        } else {
            $this->error('数据传入失败！');
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
//删除所有选中的记录
    public function delall()
    {
        $ids = $_POST['checkid'];
        if ($ids) {
            $result = Db::name("bbjs")->where('id', 'in', $ids)->setField('isdel', 1);
            if ($result) {
                $this->success("批量删除成功！", "bbjs/bbgl");
            } else {
                $this->error('删除失败,信息不存在或参数错误！');
            }
        } else {
            $this->error('数据传入失败！');
        }
    }

	//标本接收到运营中心
	public function bbjs()
    {
        $where['isdel']   = 0;
        $where['status']  = 0;
        $request = input('request.');
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
    //标本分发到各实验室
    public function bbff()
    {
        $where['isdel']   = 0;
        $where['position']   = '运营中心';
        $request = input('request.');
        if (!empty($request['com'])) {
            $where['com'] = $request['com'];
        }
        $usersQuery = Db::name('bbjs');
        if (!empty($request['project'])) {
            $project = $request['project'];
            $list = $usersQuery->where($where)->where('project','like','%'.$project.'%')->order("id DESC")->paginate(10);
        }
        else
        {
            $list = $usersQuery->where($where)->order("id DESC")->paginate(10);
        }

        $where2 = ['adddo'=>"shiyan"];
        $shiyan = Db::name("options")->where($where2)->find();
        $this->assign('shiyan', $shiyan);

        // 获取分页显示
        $page = $list->render();
        $this->assign('list', $list);
        $this->assign('page', $page);
        // 渲染模板输出
        return $this->fetch();
    }

    //更新所有选中的记录
    public function setAll()
    {
        $ids = $_POST['checkid'];
        $labs = $_POST['labs'];
        $i = 0;
        foreach($ids as $id) {
            $data = ['status'=>2,'labs'=> $labs[$i],'position'=>$labs[$i]];
            $result = Db::name("bbjs")->where('id', $id)->update($data);
            $i++;
        }
        $this->success("批量设置成功！", "bbjs/bbff");
    }

    //接收标本所有选中的记录
    public function getAll()
    {
        $ids = $_POST['checkid'];
        $stu = $this->request->param("stu");
        if(empty($stu)){
            $stu = 1;
        }
        $i = 0;
        foreach($ids as $id) {
            $data = ['status'=>$stu,'position'=>'运营中心'];
            $result = Db::name("bbjs")->where('id', $id)->update($data);
            $i++;
        }
        $this->success("批量接收成功！", "bbjs/bbjs");
    }

    //返还客户处标本所有选中的记录
    public function backAll()
    {
        $ids = $_POST['checkid'];
        $i = 0;
        foreach($ids as $id) {
            $data = ['status'=>4,'position'=>'客户处'];
            $result = Db::name("bbjs")->where('id', $id)->update($data);
            $i++;
        }
        $this->success("批量返还成功！", "bbjs/bbfh");
    }

    //标本回收,返回运营中心
    public function bbhs()
    {
        $where['isdel']   = 0;
        $where['position']   = array('not in','客户处,运营中心');
        //$where['position']   = array('neq','客户处');
        $request = input('request.');
        if (!empty($request['com'])) {
            $where['com'] = $request['com'];
        }
        if (!empty($request['btype'])) {
            $where['btype'] = $request['btype'];
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

    //标本返回客户处
    public function bbfh()
    {
        $where['isdel']   = 0;
        $where['position']   = '运营中心';
        //$where['position']   = array('neq','客户处');
        $request = input('request.');
        if (!empty($request['com'])) {
            $where['com'] = $request['com'];
        }
        if (!empty($request['btype'])) {
            $where['btype'] = $request['btype'];
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

	//标本位置
	public function bbwz()
    {
        $where['isdel']   = 0;
        if (!empty($request['uname'])) {
            $where['uname'] = $request['uname'];
        }
        $request = input('request.');
        if (!empty($request['com'])) {
            $where['com'] = $request['com'];
        }
        if (!empty($request['btype'])) {
            $where['btype'] = $request['btype'];
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
