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

class OrderModel extends Model
{
    //返回订单列表
    public function orderLists($uid,$pay,$status){

        $where['a.uid'] = $uid;
        $where["a.status"] = array(">",0);
        if(!empty($pay)){
            $where["a.order_type"] = array(['=',1],['=',2],'or');
        }
        if(!empty($status)) {
            $where["a.status"] = $status;
        }
        $lists = $this->selectOrder($where);
        return $lists;
    }
//读取符合条件的订单信息
    public function selectOrder($where){
        $join = [["__WEIXIN__ b","a.uid = b.id"],["__USER_THEME__ c","a.objectid = c.id"]];
        $field = 'a.id,a.uid,a.order_code,a.order_num,a.order_pay,a.status as stat,a.order_time,b.icon as avatar,b.nickname as usernick,b.phone,c.title,c.bg_img,c.price,c.theme_cate';
        $orderlist = Db::name("orders");

         $lists    = $orderlist->alias("a")->join($join)
                ->field($field)
                ->where($where)
                ->order('a.id', 'DESC')
                ->select();

        $listArray = [];
        $theme_cate = config("cmf_themes_cate");
        $order_cate = config('cmf_orders_status');
        if($lists)
        {
            foreach ($lists as $key => $vo) {
                $array = [];
                $num = $vo["order_num"];
                $price = $vo["price"];
                $paynums = $num*$price;

                $array["id"] = $vo["id"];
                $array["uid"] = $vo["uid"];
                $array["title"] = $vo["title"];
                $array["order_code"] = $vo["order_code"];
                $array["num"] = $num;
                $array["pay"] = $vo["order_pay"];
                $array["paynums"] = $paynums;
                $array["stat"] = $vo["stat"];
                $array["status"] = $order_cate[$vo["stat"]];
                $array["datetime"] = date("Y-m-d h:i:s",$vo["order_time"]);
                $array["avatar"] = $vo["avatar"];
                $array["image"] = $vo["bg_img"];
                $array["nickname"] = $vo["usernick"];
                $array["title"] = $vo["title"];
                $array["price"] = $price;
                $array["cates"] = $theme_cate[$vo["theme_cate"]];

                array_push($listArray, $array);
            }
        }
        return $listArray;
    }
//根据条件查询单条订单信息
    public function findOrder($where){
        $join = [
            ["__WEIXIN__ b","a.uid = b.id"],
            ["__USER_THEME__ c","a.objectid = c.id"],
            ["__PHOTOLIST__ d","a.uid = d.uid"]
        ];
        $field = 'a.id,a.uid,a.order_code,a.order_num,a.order_pay,a.status as stat,a.order_time,b.icon as avatar,b.nickname as usernick,b.phone,c.title,c.price,c.bg_img,c.theme_cate,d.uname,d.utel,d.uaddr';
        $orderlist = Db::name("orders");

        $vo    = $orderlist->alias("a")->join($join)
            ->field($field)
            ->where($where)
            ->order('a.id', 'DESC')
            ->find();

        $array = [];
        $theme_cate = config("cmf_themes_cate");
        $order_cate = config('cmf_orders_status');
        if($vo)
        {
                $array = [];
                $num = $vo["order_num"];
                $price = $vo["price"];
                $paynums = $num*$price;
                $array["id"] = $vo["id"];
                $array["uid"] = $vo["uid"];
                $array["title"] = $vo["title"];
                $array["order_code"] = $vo["order_code"];
                $array["num"] = $num;
                $array["pay"] = $vo["order_pay"];
                $array["paynums"] = $paynums;
                $array["stat"] = $vo["stat"];
                $array["status"] = $order_cate[$vo["stat"]];
                $array["datetime"] = date("Y-m-d h:i:s",$vo["order_time"]);
                $array["avatar"] = $vo["avatar"];
                $array["nickname"] = $vo["usernick"];
                $array["title"] = $vo["title"];
                $array["price"] = $price;
                $array["image"] = $vo["bg_img"];
                $array["cates"] = $theme_cate[$vo["theme_cate"]];

                $array["uname"] = $vo["uname"];
                $array["utel"] = $vo["utel"];
                $array["uaddr"] = $vo["uaddr"];
        }
        return $array;
    }

    public function sendMessage($phone, $msg, $sendtime = '', $port = '', $needstatus = '')
    {
        $username = "shanda"; //在这里配置你们的发送帐号
        $passwd = "shanda888"; //在这里配置你们的发送密
        $ch = curl_init();
        $post_data =
            "account=" . $username . "&password=" . $passwd . "&mobile=" . $phone . "&content=" . urlencode($msg) . "&needstatus=true&port=" . $port . "&sendtime=" . $sendtime;
        curl_setopt($ch, CURLOPT_URL, "http://sms.kingbooe.com/SMSAPI/Send");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
        $file_contents = curl_exec($ch);
        curl_close($ch);
        return json_decode($file_contents);
    }

    public function saveOrder($id,$uid,$pay,$type,$remark){

        $randnums = mt_rand(100000,999999);
        $utm = time();
        $tm = date("Ymdhis",$utm);
        if($type == "1"){
            $qz = "ZT";
        }
        else
        {
            $qz = "PH";
        }
        $rands = $qz.$tm.$randnums;
        $data=[
            "objectid" => $id,
            "uid" => $uid,
            "order_code" =>$rands,
            "order_num" => 1,
            "order_type" => $type,
            "status" => 1,
            "order_pay" => $pay,
            "remarks" => $remark,
            "order_time" => $utm
        ];
        $result = Db::name("orders")->where(["order_code"=>$rands])->select();
        if(count($result)==0){
            $res = Db::name("orders")->insert($data);
        }
        else
        {
            $res = "0";
        }
        return json(["code:"=>$res]);
    }
}