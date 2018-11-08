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
            //$_POST['pname'] = json($_POST['pname']);
            //$_POST['project'] = json($_POST['project']);           //serialize序列化，反序列化unserialize
            $_POST['isdel'] = 0;
            $_POST['status'] = '';
            $result = $this->validate($this->request->param(), 'Bbjs');
            //dump($_POST);
            if ($result !== true) {
                $this->error($result);
            } else {
                $canshu = ["xid"=>$_POST['xid']];
                $isAt = DB::name('Bbjs')->field("xid")->where($canshu)->select();
                if(!empty($isAt["xid"])){
                    //dump($isAt);
                    $this->error("该记录已经存在，不能重复添加！", url("bbjs/index"));
                }
                else
                {
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
                    $bid    = DB::name('Bbjs')->insertGetId($data0);
                    if($bid){
                        $data = ["bid"=>$bid];
                        DB::name('baogao')->insertGetId($data);
                        $data2 = [
                            "bid"=>$bid,
                            "xid"=>$_POST['xid'],
                            "mid"=>json_encode($marray1,JSON_UNESCAPED_UNICODE),
                            "project"=>json_encode($marray2,JSON_UNESCAPED_UNICODE)
                        ];
                        DB::name('project')->insertGetId($data2);
                        $this->success("添加成功！", url("bbjs/index"));
                    }
                }
            }
        }
    }

//标本管理
	public function bbgl()
    {
        $where['isdel']   = 0;
        $request = input('request.');

        $where2 = ['adddo'=>"com"];
        $coms = Db::name("options")->where($where2)->order("id DESC")->find();
        $this->assign('coms', $coms);

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
        $usersQuery = Db::name('bbjs');
        $join=[
            ["project p","b.xid = p.xid"]
        ];
        $field = "b.*,p.project";
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
        $where['b.isdel']   = 0;
        $where['b.status']  = 0;
        $request = input('request.');
        $where2 = ['adddo'=>"com"];
        $coms = Db::name("options")->where($where2)->order("id DESC")->find();
        $this->assign('coms', $coms);
        if (!empty($request['com'])) {
            $where['b.com'] = $request['com'];
        }
        $join=[
            ["project p","b.xid = p.xid"]
        ];
        $field = "b.*,p.project";
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
    //标本分发到各实验室
    public function bbff()
    {
        $where['b.isdel']   = 0;
        $where['b.position']   = '运营中心';
        $request = input('request.');
        $where2 = ['adddo'=>"com"];
        $coms = Db::name("options")->where($where2)->order("id DESC")->find();
        $this->assign('coms', $coms);
        if (!empty($request['com'])) {
            $where['b.com'] = $request['com'];
        }
        $usersQuery = Db::name('bbjs');
        $join=[
            ["project p","b.xid = p.xid"]
        ];
        $field = "b.*,p.project";
        if (!empty($request['project'])) {
            $project = $request['project'];
            $list = $usersQuery
                ->alias('b')
                ->field($field)
                ->join($join)
                ->where($where)
                ->where('p.project','like','%'.$project.'%')
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
                ->order("b.id DESC")
                ->paginate(10);
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
        $where['b.isdel']   = 0;
        $where['b.position']   = array('not in','客户处,运营中心');
        //$where['position']   = array('neq','客户处');
        $where2 = ['adddo'=>"com"];
        $coms = Db::name("options")->where($where2)->order("id DESC")->find();
        $this->assign('coms', $coms);
        $request = input('request.');
        if (!empty($request['com'])) {
            $where['b.com'] = $request['com'];
        }
        if (!empty($request['btype'])) {
            $where['b.btype'] = $request['btype'];
        }
        $join=[
            ["project p","b.xid = p.xid"]
        ];
        $field = "b.*,p.project";
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
                $list = $usersQuery->alias('b')
                    ->field($field)
                    ->join($join)
                    ->where($where)
                    ->whereTime('b.create','>','-'.$dt.' days')
                    ->order("id DESC")
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

    //标本返回客户处
    public function bbfh()
    {
        $where['b.isdel']   = 0;
        $where['b.position']   = '运营中心';
        //$where['position']   = array('neq','客户处');
        $where2 = ['adddo'=>"com"];
        $coms = Db::name("options")->where($where2)->order("id DESC")->find();
        $this->assign('coms', $coms);
        $request = input('request.');
        if (!empty($request['com'])) {
            $where['b.com'] = $request['com'];
        }
        if (!empty($request['btype'])) {
            $where['b.btype'] = $request['btype'];
        }
        $join=[
            ["project p","b.xid = p.xid"]
        ];
        $field = "b.*,p.project";
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
                    ->order("id DESC")
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

	//标本位置
	public function bbwz()
    {
        $where['isdel']   = 0;
        $where2 = ['adddo'=>"com"];
        $coms = Db::name("options")->where($where2)->order("id DESC")->find();
        $this->assign('coms', $coms);
        if (!empty($request['uname'])) {
            $where['b.uname'] = $request['uname'];
        }
        $request = input('request.');
        if (!empty($request['com'])) {
            $where['b.com'] = $request['com'];
        }
        if (!empty($request['btype'])) {
            $where['b.btype'] = $request['btype'];
        }
        $join=[
            ["project p","b.xid = p.xid"]
        ];
        $field = "b.*,p.project";
        $usersQuery = Db::name('bbjs');
        if (!empty($request['date'])) {
            $dt = $request['date'];
            if($dt == '0'){
                $list = $usersQuery
                    ->alias('b')
                    ->field($field)
                    ->join($join)
                    ->where($where)
                    ->order("id DESC")
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
                    ->order("id DESC")
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
                ->order("id DESC")
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
