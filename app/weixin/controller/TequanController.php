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
use think\Session;
use think\Db;
use think\Model;


class TequanController extends HomeBaseController
{
    public function _initialize()
    {
        parent::_initialize();
        $this->checkTokenId();
    }
    /**
     *文章列表
     */
    public function index()
    {
        $param = $this->request->param();
        if (isset($param['id'])) {
            $id = $this->request->param('id', 0, 'intval');
        } else {
            $id = 7;
        }
       return "";
    }

    public function loginBuser(){
        //$param = $this->request->param();
        $uname = $this->request->param('uname', "", 'string');
        $upwd0 = $this->request->param('upwd', "", 'string');
        $tequan = "";
        if(!empty($uname) && !empty($upwd0))
        {
            $upwd = cmf_password($upwd0);
            $join = [
                ["__BANKS__ b","a.id=b.uid"]
            ];
            $field="a.id,b.bank_com,b.bank_user,b.bank_tel,b.bank_card";
            $where = ["a.user_type"=>2,"a.user_status"=>1,"a.user_pass"=>$upwd,"a.user_login|a.mobile|a.user_email"=>$uname];
            $tequan = Db::name("user")->alias("a")->join($join)->field($field)->where($where)->find();
            if($tequan){
                $result = $tequan["id"]."_".$tequan["bank_user"]."_".$tequan["bank_com"]."_".$tequan["bank_tel"]."_".$tequan["bank_card"];
                /*
                $result = [
                    "result_code" => "success",
                    "bid"=>$tequan["id"],
                    "bank_com" => $tequan["bank_com"],
                    "bank_user" => $tequan["bank_user"],
                    "bank_tel" => $tequan["bank_tel"],
                    "bank_card" => $tequan["bank_card"],
                ];
                */
            }else{
                $result = "用户名或密码错误！";
                /*
                $result = [
                    "result_code" => "fail",
                    "bid" => 0
                ];
                */
            }
        }
        else
        {
            $result = "请输入用户名和密码！";
        }
        return $result;
    }

    public function tequanlist(){
        $tequan = Db::name("levels")->select();
        $arrs = [];
        foreach($tequan as $key => $vo)
        {
            $array = [
                "id" => $vo["id"],
                "title" => $vo["title"],
                "num" => $vo["num"],
                "image" => $vo["image"],
                "remark" => $vo["remark"],
                "jifen" => $vo["jifen"]
            ];
            array_push($arrs,$array);
        }
        return json($arrs);
    }

    public function get_tequan()
    {
        $param = $this->request->param();
        $id = $this->request->param('id', 0, 'intval');

        $ids = Db::name("levels")->field("tequan")->where(["id"=>$id])->find();
        $id_arrs = explode(",",$ids["tequan"]);
        //dump($id_arrs[0]);
        $where["status"] = 1;
        $where["id"] =["in",$id_arrs];
        $tequan = Db::name("tequan")->where($where)->select();
        return json($tequan);
    }

    public function tequanDetails(){

        $param = $this->request->param();
        $id = $this->request->param('id', 0, 'intval');

        $where["id"] = $id;
        $where["status"] = 1;

        $tequan   = Db::name("tequan")->where($where)->find();
        $arrs = [];
        if($tequan){
            $content = htmlspecialchars_decode($tequan['content']);
            $content = str_replace("<br/>","",$content);
            $arrs = [
                "content" => $content,
                "image" => $tequan['image'],
                "endtime" => $tequan['endtime'],
                "num" => $tequan['num'],
                "tiaojian" => $tequan['tiaojian'],
                "status" => $tequan['status'],
                "remark" => $tequan['remark'],
                "id" => $tequan['id']
            ];
        }
        return json($arrs);
    }

    public function download(){

        $param = $this->request->param();
        $id = $this->request->param('uid', 0, 'intval');
        $status = $this->request->param('status');

        $where["uid"] = $id;
        $where["status"] = $status;
        $download   = Db::name("films")->where($where)->select();

        return json($download);
        /*
        $arrs = [];
        foreach($download as $vo){
            //$content = htmlspecialchars_decode($tequan['content']);
            //$content = str_replace("<br/>","",$content);
            //$piclist = json_decode($vo["picurl"]);
            $downData = [
                    "id"=> $vo["id"],
                    "imagelist" => $vo["picurl"]
                ];
           array_push($arrs,$downData);
        }
        return json($arrs);
        */
    }

    public function setDown(){
        $param = $this->request->param();
        $id = $this->request->param('id', 0, 'intval');
        $where["id"] = $id;
        $result   = Db::name("films")->where($where)->update(["status"=>1]);
        return $result;
    }
}