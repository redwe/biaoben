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
use app\weixin\model\OrderModel;
use app\weixin\model\WeixinModel;

class OrderController extends HomeBaseController
{
    //登录
    public function index()
    {
        return $this->fetch();
    }

    //保存订单
    public function gotoBuy(){
        $param = $this->request->param();
        if(isset($param["id"]))
        {
            $id = $param["id"];
        }
        else
        {
            $id = 0;
        }
        if(isset($param["uid"]))
        {
            $uid = $param["uid"];
        }
        else
        {
            $uid = 0;
        }
        if(isset($param["pay"]))
        {
            $pay = $param["pay"];
        }
        else
        {
            $pay = 0;
        }

        if(empty($id) || empty($uid)){
            $result = "-1";
        }
        else
        {
           $orderObj = new OrderModel();
           $result = $orderObj->saveOrder($id,$uid,$pay,1,"主题日记");
        }
        return $result;

    }

//微信支付统一下单
    public function gotoPay(){

        Session::set('prepayid', "");
        Session::set('out_trade_no', "");

        $param = $this->request->param();
        if(isset($param["id"]))
        {
            $id = $param["id"];
        }
        else
        {
            $id = "";
        }
        if(isset($param["openid"]))
        {
            $openid = $param["openid"];
        }
        else
        {
            $openid = "";
        }
        if(isset($param["uid"]))
        {
            $uid = $param["uid"];
        }
        else
        {
            $uid = "";
        }
        if(isset($param["total_fee"]))
        {
            $paynum =  intval($param["total_fee"])*100;
        }
        else
        {
            $paynum = 0;
        }
        if(isset($param["out_trade_no"]))
        {
            $ordercode = $param["out_trade_no"];
        }
        else
        {
            $ordercode = "";
        }
        if(isset($param["attach"]))
        {
            $attach = $param["attach"];
        }
        else
        {
            $attach = "";
        }
        $wxobj = new WeixinModel();
        $ip = $wxobj->getRealIp();
        $Weixin = $wxobj->getWeixinInfo();
        $nonce_str = $wxobj->getRandCode(32);
        $appid = $Weixin["appid"];
        $mch_id = $Weixin["mch_id"];
        $pay_key = $Weixin["key"];

        $array["appid"] = $appid;
        $array["mch_id"] = $mch_id;
        $array["nonce_str"] = $nonce_str;
        $array["pay_key"] = $pay_key;

        $data = [
            "appid" => $appid,
            "attach" => $attach,
            "body" => $uid,
            "mch_id" => $mch_id,
            "detail" => $id,
            "nonce_str" => $nonce_str,
            "notify_url" => "https://wx.redwe.cn/weixin/order/payOver",
            "openid" => $openid,
            "out_trade_no" =>  $ordercode,
            "spbill_create_ip" => $ip,
            "total_fee" =>  $paynum,
            "trade_type" => "JSAPI"
      ];
        //统一下单签名
        $data['sign'] = $wxobj->getSigns($data,$pay_key);
        $xmlData = $wxobj->arrayToXml($data);
        $url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
        $request = $wxobj->postCurl($url,$xmlData);
        header('Content-Type:text/xml; charset=utf-8');
        $result = $wxobj->xmlToArray($request);
        if(isset($result["result_code"]) && $result["result_code"]=="SUCCESS"){
            $res = Db::name("finance")->where(["out_trade_no"=>$ordercode])->count();
            if($res==0){
                $paynum1 = $paynum/100;
                $paynum2 = round($paynum1,2);
                $tm = time();
                Db::name("finance")->insert(["fa_type"=>1,"fa_in"=>$paynum2,"out_trade_no"=>$ordercode,"create_time"=>$tm,"uid"=>$uid,"status"=>0]);
                Session::set('prepayid', $result["prepay_id"]);
                Session::set('out_trade_no', $ordercode);
            }
            return json($result);
        }
        else
        {
            return '0';
        }
    }
//更改支付状态
    public function setpayStatus(){
        $param = $this->request->param();
        if(isset($param["tradecode"]))
        {
            $out_trade_no = $param["tradecode"];
        }
        else
        {
            $out_trade_no = "";
        }
        if(isset($param["prepayid"]))
        {
            $prepayid = $param["prepayid"];
        }
        else
        {
            $prepayid = "";
        }
        $pid = Session::get('prepayid');
        $trade_no = Session::get('out_trade_no');

        if($prepayid == $pid && $out_trade_no == $trade_no)
        {
            $result = Db::name("orders")->where(["order_code"=>$trade_no])->find();
            if($result)
            {
                $res = Db::name("orders")->where(["order_code"=>$trade_no])->update(["status"=>2]);
                $res = Db::name("finance")->where(["out_trade_no"=>$trade_no])->update(["status"=>1]);
                $res = Db::name("user_themlist")->where(["order_code"=>$trade_no])->update(["status"=>1]);
            }

        }
        else
        {
            $res = "";
        }
        return $res;
        //return $pid."|||".$trade_no;
    }
//支付完成后的异步回调
    public function payOver(){   //小程序C端支付回调
        $params = $this->request->param();
        if(!empty($params))
        {
            $res = Db::name("pay")->insert(["params"=>$params]);

            $wxobj = new WeixinModel();
            $result = $wxobj->xmlToArray($params);

            if ($result['return_code'] == 'success' && $result['result_code'] == 'success') {
                //此处会处理一些支付成功的业务代码
                $out_trade_no = $result['out_trade_no'];
                $res = Db::name("orders")->where(["order_code"=>$out_trade_no])->update(["status"=>2]);
                $res = Db::name("finance")->where(["out_trade_no"=>$out_trade_no])->update(["status"=>1]);
            }
            else
            {
                $res = "";
            }
        }
        else
        {
            $res = "";
        }
        if($res){
            $xml= "<xml>";
            $xml = $xml."<return_code>SUCCESS</return_code>";
            $xml = $xml."<return_msg>OK</return_msg>";
            $xml = $xml."</xml>";
        }
        else
        {
            $xml= "<xml>";
            $xml = $xml."<return_code>FAIL</return_code>";
            $xml = $xml."<return_msg>支付通知失败</return_msg>";
            $xml = $xml."</xml>";
        }
        return $xml;
    }

    public function getOrderlist(){
        $param = $this->request->param();
        if(isset($param["pay"])){
            if(!empty($param["pay"]))
            {
                $pay = $param["pay"];
            }
            else
            {
                $pay = "1";
            }
        }
        else
        {
            $pay = "1";
        }
        if(isset($param["status"])) {
            $status = $param["status"];
        }
        else
        {
            $status ="";
        }
        if (!empty($param["uid"]))
        {
            $uid = $param["uid"];
            $orderlist = new OrderModel();
            $result = $orderlist->orderLists($uid,$pay,$status);
        } else {
            $result = '';
        }
        return json($result);
    }
//返回订单详情
    public  function orderDetails(){
        $param = $this->request->param();
        if(isset($param["id"])){
           $id = $param["id"];
        }
        else
        {
            $id = "";
        }
        if(isset($param["uid"])) {
            $uid = $param["uid"];
        }
        else
        {
            $uid ="";
        }
        if (!empty($uid) && !empty($id))
        {
            $where["a.id"] = $id;
            $where["a.uid"] = $uid;
            $where["a.status"] = array(">",0);
            $orderlist = new OrderModel();
            $result = $orderlist->findOrder($where);
        } else {
            $result = '';
        }
        return json($result);
    }
//取消订单
    public function cancelOrder(){
        $param = $this->request->param();
        if(isset($param["id"])){
            $id = $param["id"];
        }
        else
        {
            $id = "";
        }
        if(isset($param["uid"])) {
            $uid = $param["uid"];
        }
        else
        {
            $uid ="";
        }
        if (!empty($uid) && !empty($id))
        {
            $where["id"] = $id;
            $where["uid"] = $uid;
            $data["status"] = 0;

            $lists = Db::name("orders")->field("status")->where($where)->find();
            if($lists["status"]>1){
                $lists = "";
            }
            else
            {
                $lists    = Db::name("orders")->where($where)->update($data);
            }
        }
        else
        {
            $lists = "";
        }
        return $lists;
    }
}