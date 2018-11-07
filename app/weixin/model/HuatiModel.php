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
namespace app\weixin\model;

use think\Db;
use think\Model;

class HuatiModel extends Model
{

    //获取首页热门话题
    public function getHuati($id,$m)
    {
        $where["a.status"] = 1;
        if(!empty($id)){
            $where['a.uid'] = $id;
        }
        $field = 'a.id,a.uid,a.title,a.content,a.image,b.icon as avatar,b.nickname as usernick';
        $banner = Db::name("huati");
        $lists    = $banner->alias("a")->join([["__WEIXIN__ b","a.uid = b.id"]])
            ->field($field)
            ->where($where)
            ->limit($m)
            ->order('a.id', 'DESC')
            ->select();
        $listArray = [];
        foreach ($lists as $key => $vo) {
            $array = [];
            $id = $vo["id"];
            if($vo["image"]=="undefined"){
                $image = "";
            }
            else
            {
                $image = $vo["image"];
            }
            $array["id"] = $vo["id"];
            $array["title"] = $vo["title"];
            $array["content"] = $vo["content"];
            $array["image"] = $vo["image"];
            $array["uid"] = $vo["uid"];
            $array["avatar"] = $vo["avatar"];
            $array["usernick"] = $vo["usernick"];
            if(!empty($re)){
                $relist = Db::name("comment")->alias("a")
                    ->join([["__WEIXIN__ b", "a.user_id = b.id"]])
                    ->field("a.id,a.content,b.nickname as nickname,b.icon")
                    ->where(["object_id"=>$id])
                    ->select();
                if($relist){
                    $array["comment"] = $relist;
                }
                else
                {
                    $array["comment"] = "";
                }
            }
            else
            {
                $array["comment"] = "";
            }

            array_push($listArray, $array);
        }
        return $listArray;
    }

    public function getHuatilist($where,$order,$re,$limit,$p){

        $field = 'a.id,a.uid,a.title,a.content,a.image,a.zan,a.zhuan,a.pinglun,a.datetime,b.icon as avatar,b.nickname as usernick,b.level as lv,b.status';
        $banner = Db::name("huati");
        $count = $banner->alias("a")
            ->join([["__WEIXIN__ b", "a.uid = b.id","left"]])
            ->where($where)
            ->count();
        $pnum = ceil($count/$limit);  //向上取整，计算页数
        if($p > $pnum){
            $p = 1;
        }
        if($p<=0)
        {
            $p  = 1;
        }
        $start = $limit*($p-1);

        $lists = $banner->alias("a")->join([["__WEIXIN__ b", "a.uid = b.id","left"]])
            ->field($field)
            ->where($where)
            ->limit($start,$limit)
            ->order($order)
            ->select();
            $listArray = [];

            foreach ($lists as $key => $vo) {
                $array = [];
                $id = $vo["id"];
                $array["id"] = $vo["id"];
                $array["title"] = $vo["title"];
                $array["content"] = $vo["content"];
                $array["image"] = $vo["image"];
                $array["uid"] = $vo["uid"];
                $array["zan"] = $vo["zan"];
                $array["zhuan"] = $vo["zhuan"];
                $array["pinglun"] = $vo["pinglun"];
                $array["datetime"] = date("Y-m-d", strtotime($vo["datetime"]));
                $array["avatar"] = $vo["avatar"];
                $array["usernick"] = $vo["usernick"];
                $array["lv"] = $vo["lv"];
                $array["status"] = getYuStatus($vo["status"]);
                if(!empty($re)){
                    $relist = Db::name("comment")->alias("a")
                        ->join([["__WEIXIN__ b", "a.user_id = b.id","left"]])
                        ->field("a.id,a.content,a.create_time,b.nickname as nickname,b.icon,b.level")
                        ->where(["object_id"=>$id])
                        ->select();
                    if($relist){
                        $array["comment"] = $relist;
                    }
                    else
                    {
                        $array["comment"] = "";
                    }
                }
                else
                {
                    $array["comment"] = "";
                }

                array_push($listArray, $array);
            }
        return ["count"=>$count,"datas" =>$listArray];
    }

   public function setDianZan($id,$table,$field){
        if(!empty($id))
        {
            $where = [
                'id' => $id
            ];
            $huatiModel = Db::name($table);
            $huati = $huatiModel->where($where)->select();
            if($huati){
                $result        = $huatiModel->where($where)->setInc($field);
            }
            else
            {
                $result="";
            }
        }
        else
        {
            $result="";
        }
        return $result;
    }

    public function getComment($id,$oid){
        if (isset($id)) {
            $where["parent_id"] = 0;
            if ($oid == "o") {
                $where["object_id"] = $id;
            } elseif($oid == "a") {
                $where["article_id"] = $id;
            }
            else
            {
                $where["zhuti_id"] = $id;
            }
            if (empty($id)) {
                $where = [];
            }
            $result = $this->commentlist($where);
        } else
        {
            $result = '';
        }
        return $result;
    }

    public function dianzanlist($where,$join){

        $zanlist = Db::name("dianzan")
            ->alias("a")
            ->field("a.*,b.nickname,b.level,b.status,b.icon")
            ->join($join)
            ->where($where)
            ->select();
        $resArray = [];
        foreach($zanlist as $key => $vo){
            $array = [];
            $article_id = $vo["articleid"];
            $object_id = $vo["objectid"];

            if($object_id == "blog"){
                $pldata = Db::name("blog")->where(["id"=>$article_id])->field("theme,footer,thumb,piclist")->find();
                $themeid = $pldata["theme"];
                $title = $pldata["footer"];
                $thumb = $pldata["thumb"];
                $piclist =  htmlspecialchars_decode($pldata["piclist"]);
                $morepic = json_decode($piclist,true);
                $image = "";
                $tdata = Db::name("user_theme")->where(["id"=>$themeid])->field("editimg")->find();
                $editimg = $tdata["editimg"];
                $editarray = explode(",",$editimg);
                foreach($editarray as $k =>$v)
                {
                    if($v=="1")
                    {
                        $image = $morepic["img".$k];
                        break;
                    }
                }
                if($thumb){
                    $array["image"]=$thumb;
                }
                else
                {
                    $image = str_replace("https://wx.redwe.cn/upload/","",$image);
                    $array["image"]=$image;
                }
                $array["title"] = $title;
                $array["url"]="/pages/diaryInfo/diaryInfo";
                $array["url_id"]=$article_id;
                $array["class_name"]="图片日记";
            }else{
                $objdata = Db::name("huati")->where(["id"=>$article_id])->field("title,image")->find();
                $array["image"]=$objdata['image'];
                $array["title"] = $objdata["title"];
                $array["url"]="/pages/topicInfo/topicInfo";
                $array["url_id"]=$article_id;
                $array["class_name"]="孕育话题";
            }

            $reid = $vo["id"];
            $array["id"] = $vo["id"];
            $array["article_id"] = $article_id;
            $array["object_id"] = $object_id;
            $array["uid"] = $vo["uid"];
            $array["zanid"] = $vo["zanid"];
            $array["zhuanid"] = $vo["zhuanid"];
            $array["nickname"] = $vo["nickname"];
            $array["avatar"] = $vo["icon"];
            $array["lv"] = $vo["level"];
            $array["status"] = getYuStatus($vo["status"]);
            $array["datetime"] = date("Y-m-d", strtotime($vo["datetime"]));
            array_push($resArray,$array);
        }
        $result =  $resArray;
        return $result;
    }

//返回评论列表
    public function commentlist($where){

         $huatiobj = Db::name("comment");
         $lists = $huatiobj
                ->alias("a")
                ->field("a.*,b.nickname,b.level,b.status,b.icon")
                ->join([["__WEIXIN__ b","a.user_id = b.id"]])
                ->where($where)
                ->order("a.id DESC")
                ->select();
            $resArray = [];
            foreach($lists as $key => $vo){
                $array = [];
                $zhuti_id = $vo["zhuti_id"];
                $article_id = $vo["article_id"];
                $object_id = $vo["object_id"];
                $type_id = $vo["type"];

                if(!empty($zhuti_id)){
                    $pldata = Db::name("blog")->where(["id"=>$zhuti_id])->field("theme,footer,thumb,piclist")->find();
                    $themeid = $pldata["theme"];
                    $title = $pldata["footer"];
                    $thumb = $pldata["thumb"];
                    $piclist =  htmlspecialchars_decode($pldata["piclist"]);
                    $morepic = json_decode($piclist,true);
                    $image = "";
                    $tdata = Db::name("user_theme")->where(["id"=>$themeid])->field("editimg")->find();
                    $editimg = $tdata["editimg"];
                    $editarray = explode(",",$editimg);
                    foreach($editarray as $k =>$v)
                    {
                        if($v=="1")
                        {
                            $image = $morepic["img".$k];
                            break;
                        }
                    }
                    if($thumb){
                        $array["image"]=$thumb;
                    }
                    else
                    {
                        $image = str_replace("https://wx.redwe.cn/upload/","",$image);
                        $array["image"]=$image;
                    }
                    $array["title"] = $title;
                    $array["url"]="/pages/diaryInfo/diaryInfo";
                    $array["url_id"]=$zhuti_id;
                    $array["class_name"]="图片日记";
                }

                if(!empty($object_id)){
                    $objdata = Db::name("huati")->where(["id"=>$object_id])->field("title,image")->find();
                    $array["image"]=$objdata['image'];
                    $array["title"] = $objdata["title"];
                    $array["url"]="/pages/topicInfo/topicInfo";
                    $array["url_id"]=$object_id;
                    $array["class_name"]="孕育话题";
                }

                if(!empty($article_id)){
                    $artdata = Db::name("portal_post")->where(["id"=>$article_id])->field("post_title,more")->find();
                    $more = json_decode($artdata["more"],true);
                    $array["image"]=$more['thumbnail'];
                    $array["title"] = $artdata["post_title"];
                    if($type_id==1){
                        $array["url"]="/pages/articleDetails/articleDetails";
                        $array["class_name"]="孕育知识";
                    }
                    else
                    {
                        $array["url"]="/pages/Return/Return";
                        $array["class_name"]="活动详情";
                    }

                    $array["url_id"]=$article_id;
                }

                $reid = $vo["id"];
                $array["id"] = $vo["id"];
                $array["zhuti_id"] = $zhuti_id;
                $array["article_id"] = $article_id;
                $array["object_id"] = $object_id;
                $array["uid"] = $vo["user_id"];
                $array["nickname"] = $vo["nickname"];
                $array["avatar"] = $vo["icon"];
                $array["lv"] = $vo["level"];
                $array["status"] = getYuStatus($vo["status"]);
                $array["content"] = $vo["content"];
                $array["zan"] = $vo["zan"];
                $array["datetime"] = date("Y-m-d", $vo["create_time"]);

                $array["count"] = Db::name("comment")->where(["parent_id"=>$reid])->count("id");
                $relist = Db::name("comment")
                    ->alias("a")
                    ->field("a.id,a.content,b.nickname")
                    ->join([["__WEIXIN__ b","a.user_id = b.id"]])
                    ->where(["a.parent_id"=>$reid])
                    ->select();
                $array["Reply"] = $relist;
                array_push($resArray,$array);
            }
            $result =  $resArray;
        return $result;
    }

    //返回消息列表
    public function  messagelist($uid,$bid,$toid,$m){
        $huatiobj = Db::name("guestbook");
        $where["uid"] = $uid;
        $whereOr["msg_type"] = $toid;
        $whereOr["a.bid"] = $bid;
        $lists = $huatiobj
            ->alias("a")
            ->field("a.id,a.title,a.msg,a.createtime,b.user_nickname as nickname,b.avatar")
            ->join([["__USER__ b","a.uid = b.id"]])
            ->where($where)
            ->limit($m)
            ->order("a.id desc")
            ->whereOr($whereOr)
            ->select();
        $resArray = [];
        foreach($lists as $key => $vo){
            $array = [];
            $reid = $vo["id"];
            $array["id"] = $vo["id"];
            $array["title"] = $vo["title"];
            $array["msg"] = $vo["msg"];
            $array["createtime"] = date("Y-m-d h:i:s", $vo["createtime"]);
            $array["nickname"] = $vo["nickname"];
            $array["avatar"] = $vo["avatar"];
            array_push($resArray,$array);
        }
        $result =  $resArray;
        return $result;
    }

    public function getGuanzhu($uid,$m){
        if (isset($uid)) {
            $where["sts"]=1;
            if($m==0){
                $where["uid"] = $uid;
            }
            else
            {
                $where["gzid"] = $uid;
            }
            $result = Db::name("guanzhu")->where($where)->count();
        }
        else
        {
            $result = '';
        }
        return $result;
    }

}