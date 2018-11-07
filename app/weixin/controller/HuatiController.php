<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: Powerless < wzxaini9@gmail.com>
// +----------------------------------------------------------------------
namespace app\weixin\controller;

use think\Validate;
use cmf\controller\HomeBaseController;
use app\user\model\UserModel;
use think\Session;
use think\Db;
use app\weixin\model\ArticleModel;
use app\weixin\model\HuatiModel;
use think\Request;

class HuatiController extends HomeBaseController
{
    //登录
    public function index()
    {
        return $this->fetch();
    }

    public function getHtbanner(){
        $bid = $this->request->param('bid', 0, 'intval');
        $picobj = new ArticleModel();
        $piclist3 = $picobj->getBanner(4,3,$bid); //热门图片
        return json($piclist3);
    }

    //获取热门话题
    public function getHtlist()
    {
        $param = $this->request->param();
        $page = $this->request->param('page', 1, 'intval');
        $PageNum = $this->request->param('PageNum', 10, 'intval');
        $where = [];
        if ($param["id"]=="0") {
            $order = ['zan'=>'DESC'];
        }
        else
        {
            $order = ['id'=>'DESC'];
        }
           $huatiobj = new HuatiModel();
           $huatilist = $huatiobj->getHuatilist($where,$order,"",$PageNum,$page); //话题列表

           return json($huatilist);
    }

    //获取我的话题
    public function getMyhuati()
    {
        $param = $this->request->param();
        $page = $this->request->param('page', 1, 'intval');
        $PageNum = $this->request->param('PageNum', 10, 'intval');

        if (isset($param["uid"])) {
            $uid = $param["uid"];
            $where = [
                "uid" => $uid
            ];
            $order = ['id'=>'DESC'];
            $huatiobj = new HuatiModel();
            $huatilist = $huatiobj->getHuatilist($where,$order,"1",$PageNum,$page); //话题列表

            $result = json($huatilist);
        }
        else
        {
            $result = "";
        }
        return $result;
    }

    //获取参与的话题
    public function participation()
    {
        $param = $this->request->param();
        $page = $this->request->param('page', 1, 'intval');
        $PageNum = $this->request->param('PageNum', 10, 'intval');
        $huatilist = [];

        if (isset($param["uid"])) {
            $uid = $param["uid"];
            $where = [
                "uid" => $uid
            ];
            $arrs = [];
            $str = "";
            $commarray = [];
            $zanids = Db::name("dianzan")
                ->alias("a")
                ->join([["__HUATI__ b","a.articleid=b.id"]])
                ->group('a.articleid')
                ->field("a.objectid,a.articleid,b.title")
                ->where(["a.zanid"=>$uid,"a.objectid"=>"huati"])
                ->select();

            if($zanids){
                foreach($zanids as $key0 => $vo0)
                {
                    $artid = $vo0["articleid"];
                    array_push($commarray,$vo0["title"]);
                    array_push($arrs,$artid);
                }
            }

            $canyuid = Db::name("comment")
                ->alias("a")
                ->join([["__HUATI__ b","object_id=b.id"]])
                ->group('a.object_id')
                ->field("a.object_id,b.title")
                ->where(["user_id"=>$uid,"a.object_id"=>[">",0]])
                ->select();
            if($canyuid){

                foreach($canyuid as $key => $vo)
                {
                    array_push($commarray,$vo["title"]);
                    array_push($arrs,$vo["object_id"]);
                }
            }
            $arrs = array_unique($arrs);
            $commarray = array_unique($commarray);
            foreach($arrs as $as){
                if($str==""){
                    $str = $as;
                }
                else
                {
                    $str = $str.",".$as;
                }
            }
            $where =["a.id"=>['in', $str]];
            $order = ['a.id'=>'DESC'];

            $huatiobj = new HuatiModel();
            $huatilist = $huatiobj->getHuatilist($where,$order,"",$PageNum,$page); //话题列表
            $result = json(["huati"=>$huatilist,"comment"=>$commarray,"ids"=>$arrs]);
        }
        else
        {
            $result = "";
        }
        return $result;
    }

    //保存话题信息
    public function saveHuati()
    {
        $param = $this->request->param();

        if (!empty($param["id"]) && !empty($param["title"])) {
            $uid = $param["id"];
            $title = $param["title"];
            $content = $param["content"];
            $image = $param["image"];
            $field = [
                "uid" => $uid,
                "title" => $title,
                "content" => $content,
                "zan" => 0,
                "pinglun" => 0,
                "jubao" => 0,
                "zhuan" => 0,
                "image" => $image,
                "vadio" => "",
                "face" => "",
                "status" => 1
            ];
            $guestObj = Db::name("huati");
            $result = $guestObj->insert($field);
        } else {
            $result = "";
        }
        return $result;
    }

//上传话题图片
    public function uploadHuatiPic()
    {
        $param = $this->request->param();
        if (isset($param["dir"])) {
            $dir = $param["dir"];
            $file = $_FILES["file"];
            $root = "/";
            $updateWx = new ArticleModel();
            $result = $updateWx->uploadPics($root,$dir, $file);
        } else
        {
            $result = '';
        }
        return json($result);
    }

    //话题点赞
    public function dianZan(){
        $param = $this->request->param();
        if (isset($param["id"]) && isset($param["zid"])) {
            $id = $param["id"];
            $zid = $param["zid"];
            $public = true;
            if(isset($param["uid"]))
            {
                $uid = $param["uid"];
            }
            else
            {
                $uid = 0;
            }
            if(isset($param["table"])){
                $table = $param["table"];
            }
            else
            {
                $table = "huati";
            }
            $act = $param["act"];
            if($act=="1")
            {
                $field = "zhuan";
                $data["zhuanid"] = $zid;
            }
            else if($act=="2")
            {
                $field = "jubao";
                $data["jubaoid"] = $zid;
            }
            else
            {
                $field = "zan";
                $data["zanid"] = $zid;
                $rels = Db::name("dianzan")->where(["uid"=>$uid,"zanid"=>$zid,"articleid"=>$id])->count();
                if($rels>0){
                    $public = false;
                }
            }
            $data["articleid"] = $id;
            $data["objectid"] = $table;
            if($public){
                    $updateWx = new HuatiModel();
                    $result = $updateWx->setDianZan($id,$table,$field);
                    $data["uid"] = $uid;
                    $res = Db::name("dianzan")->insert($data);
           }
            else
            {
                return -1;
            }
        } else
        {
            $result = '';
        }
        return $result;
    }

    public function guanzhu(){
        $param = $this->request->param();
        if (isset($param["gzid"])) {
            $gzid = $param["gzid"];  //被关注者uid
            $uid = $param["uid"]; //关注者uid
            $data = ["uid"=>$uid,"gzid"=>$gzid,"sts"=>1];
            if(empty($gzid) || empty($uid)){
                $result = '数据不能为空！';
            }
            elseif($uid == $gzid)
            {
                $result = '不能关注自己！';
            }
            else
            {
                $result = Db::name("guanzhu")->where($data)->find();
                if($result){
                    $result = '已经关注过了！';  //已经关注过
                }
                else
                {
                    $result = Db::name("guanzhu")->insert($data);
                }
            }
        } else
        {
            $result = '没有参数！';
        }
        return $result;
    }

    public function getguanzhu(){

        $param = $this->request->param();
        $id = $param['id'];
        $tab = $param["tab"];
        $keyword = $param["keyword"];

        if (isset($id) && isset($tab)) {

            $where["a.sts"] = 1;
            if($tab=="0")
            {
                $where["a.uid"] = $id;
                if(!empty($keyword)){
                    $where["b.nickname"]=["like","%".$keyword."%"];
                }
                $join = [
                    ["__WEIXIN__ b","a.gzid=b.id"]
                ];
                $field = "a.*,b.nickname,b.icon,b.level,b.status";
            }
            else
            {
                $where["a.gzid"] = $id;
                if(!empty($keyword)){
                    $where["b.nickname"]=["like","%".$keyword."%"];
                }
                $join = [
                    ["__WEIXIN__ b","a.uid=b.id"]
                ];
                $field = "a.*,b.nickname,b.icon,b.level,b.status";
            }

            $lists = Db::name("guanzhu")->alias("a")->join($join)->field($field)->where($where)->select();

            $arra = [];
            $gzlist = [];
            $ulist = Db::name("guanzhu")->field("gzid")->where(["sts"=>1,"uid"=>$id])->select();
            foreach($ulist as $u){
                array_push($gzlist,$u["gzid"]);
            }
           foreach($lists as $vo)
           {
                $uid = $vo["uid"];
                $array = [];
                $array["id"] = $vo["id"];
                $array["nickname"] = $vo["nickname"];
                $array["avatar"] = $vo["icon"];
                $array["lv"] = $vo["level"];
                $array["uid"] = $uid;
                $array["gzid"] = $vo["gzid"];
                $array["sts"] = $vo["sts"];
                $array["status"] = getYuStatus($vo["status"]);
                   if(in_array($uid,$gzlist))
                   {
                       $array["gz_or"] = 1;
                   }
                   else
                   {
                       $array["gz_or"] = 0;
                   }
                array_push($arra,$array);
            }
            $result =  json(["datas"=>$arra,"gzids"=>$gzlist]);
        } else
        {
            $result = '';
        }
        return $result;
    }

    public function changeGuanzhu(){
        $param = $this->request->param();
        if (isset($param["uid"]) && isset($param["gzid"])) {
            $uid = $param["uid"];
            $gzid = $param["gzid"];
            $result = Db::name("guanzhu")->where(["uid"=>$uid,"gzid"=>$gzid])->find();
            if($result){
                $sts = $result["sts"];
                if($sts == 1)
                {
                    $sts = 0;
                }
                else
                {
                    $sts = 1;
                }
                $res = Db::name("guanzhu")->where(["uid"=>$uid,"gzid"=>$gzid])->update(["sts"=>$sts]);
                return $res;
            }
            else
            {
                $tab = $param["tab"];
                $data = ["uid"=>$uid,"gzid"=>$gzid,"sts"=>1];
                $result = Db::name("guanzhu")->insert($data);
                return $result;
            }

        }
        else
        {
            return "";
        }

    }

    //话题详情页内容
    public function getDetails(){
        $param = $this->request->param();
        if (isset($param["id"])) {
            $id = $param["id"];
            $uid = $param["uid"];
            $join = [
                ["__WEIXIN__ b","a.uid=b.id"]
            ];
            $field = "a.*,b.nickname,b.icon,b.level,b.status,b.hiden";
            $huatiobj = Db::name("huati");
            $lists = $huatiobj->alias("a")->join($join)->where(["a.id"=>$id])->find();
            if($lists){
                $array = [];
                $userid = $lists["uid"];
                $array["id"] = $id;
                $array["title"] = $lists["title"];
                $array["content"] = $lists["content"];
                $array["image"] = $lists["image"];
                $array["uid"] = $userid;
                $array["zan"] = $lists["zan"];
                $array["zhuan"] = $lists["zhuan"];
                $array["pinglun"] = Db::name("comment")->where(["object_id"=>$id])->count();

                $array["nickname"] = $lists["nickname"];
                $array["avatar"] = $lists["icon"];
                $array["status"] = getYuStatus($lists["status"]);
                $array["lv"] = $lists["level"];
                $array["hiden"] = $lists["hiden"];
                $array["gzor"] = getGuanzhuOr($uid,$userid);

                $array["datetime"] = date("Y-m-d", strtotime($lists["datetime"]));
                $guanzhu = Db::name("guanzhu")->where(["uid"=>$uid,"sts"=>1])->count();
                $fans = Db::name("guanzhu")->where(["gzid"=>$uid,"sts"=>1])->count();
                $array["guanzhu"] = $guanzhu;
                $array["fans"] = $fans;
                $result =  json($array);
            }
            else
            {
                $result = "";
            }
        } else
        {
            $result = '';
        }
        return $result;
    }

    public function getComment(){
        $param = $this->request->param();
        if(isset($param["objectid"]))
        {
            $objectid = $param["objectid"];
        }
        else
        {
            $objectid = "";
        }
        if (isset($param["id"])) {
            $id = $param["id"];
            $htobj = new HuatiModel();
            $result =  $htobj->getComment($id,$objectid);
        } else
        {
            $result = '';
        }
        return json($result);
    }

    public function mycomment(){
        $param = $this->request->param();
        if (isset($param["uid"])) {
            $uid = $param["uid"];
            $tab = $param["tab"];
            if($tab=="1"){
                $where = ["user_id"=>$uid];
            }
            else
            {
                $where = ["pl_id"=>$uid];
            }
            $htobj = new HuatiModel();
            $result =  $htobj->commentlist($where);
        } else
        {
            $result = '';
        }
        return json($result);
    }

    public function myDianzan(){
        $param = $this->request->param();
        if (isset($param["uid"])) {
            $uid = $param["uid"];
            $tab = $param["tab"];
            if($tab=="1"){
                $where = ["uid"=>$uid];
                $join = [["__WEIXIN__ b","a.zanid = b.id"]];
            }
            else
            {
                $where = ["zanid"=>$uid];
                $join = [["__WEIXIN__ b","a.uid = b.id"]];

            }

            $htobj = new HuatiModel();
            $result =  $htobj->dianzanlist($where,$join);
        } else
        {
            $result = '';
        }
        return json($result);
    }

    public function myzhuanfa(){
        $param = $this->request->param();
        if (isset($param["uid"])) {
            $uid = $param["uid"];
            $tab = $param["tab"];
            if($tab=="1"){
                $where = ["uid"=>$uid];
                $join = [["__WEIXIN__ b","a.zhuanid = b.id"]];
            }
            else
            {
                $where = ["zhuanid"=>$uid];
                $join = [["__WEIXIN__ b","a.uid = b.id"]];
            }

            $htobj = new HuatiModel();
            $result =  $htobj->dianzanlist($where,$join);
        } else
        {
            $result = '';
        }
        return json($result);
    }

    public function getMessage(){
        $param = $this->request->param();
        $id = 1;
        $uid = 1;
        $where = [];
        if (isset($param["id"])) {
            $uid = $param["id"];
            $bid = $param["bid"];
            $toid = $param["toid"];
            $htobj = new HuatiModel();
            $result =  $htobj->messagelist($uid,$bid,$toid,10);
        } else
        {
            $result = '';
        }
        return json($result);
    }

    public function saveComment(){
        $param = $this->request->param();
        if (isset($param["id"])) {
            $id = $param["id"];
            $uid = $param["uid"];
            $typeid = $param["typeid"];
            $plid = $param["plid"];
            $act = $param["act"];
            $content = $param["content"];
            if(isset($param["parentid"]))
            {
                $parentid = $param["parentid"];
            }
            else
            {
                $parentid = 0;
            }
            $tm = time();
            $data = [
                "user_id" => $uid,
                "pl_id" => $plid,
                "parent_id" => $parentid,
                "content" => $content,
                "zan" => 0,
                "jubao" => 0,
                "status" => 1,
                "type" => $typeid,
                "create_time" => $tm
            ];
            if($act=="o"){
                $data["object_id"] = $id;
            }
            elseif($act=="a")
            {
                $data["article_id"] = $id;
            }
            else
             {
                $data["zhuti_id"] = $id;
             }
            $result = Db::name("comment")->insert($data);
            if($result)
            {
                $result = 1;
            }
            else
            {
                $result = 0;
            }
        } else
        {
            $result = '';
        }
        return $result;
    }
}