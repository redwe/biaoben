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

class WeixinModel extends Model
{
    public function getWeixinInfo(){
        $weixin = [
            "appid" => "wxd1d25b4e8a62d8fe",
            "secret" => "388749244a61261e927a06e49135a289",
            "grant_type" => "authorization_code",
            "mch_id" => "1501733711",
            "key" => "5XK8bZZkP4nzc3Z4yXHnyNknyMcDpY8z"
        ];
        return $weixin;
    }

    function getRealIp()
    {
        $ip=false;
        if(!empty($_SERVER["HTTP_CLIENT_IP"])){
            $ip = $_SERVER["HTTP_CLIENT_IP"];
        }
        if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ips = explode (", ", $_SERVER['HTTP_X_FORWARDED_FOR']);
            if ($ip) { array_unshift($ips, $ip); $ip = FALSE; }
            for ($i = 0; $i < count($ips); $i++) {
                if (!eregi ("^(10│172.16│192.168).", $ips[$i])) {
                    $ip = $ips[$i];
                    break;
                }
            }
        }
        return ($ip ? $ip : $_SERVER['REMOTE_ADDR']);
    }

    public function postCurl($url,$data){
        //$url = "http://ip-api.com/json";
        //$data = '{"msg_id":"id007"}';
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        // post数据
        curl_setopt($ch, CURLOPT_POST, 1);
        // post的变量
        curl_setopt($ch, CURLOPT_POSTFIELDS,$data);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        //设置头部信息
        curl_setopt($ch, CURLOPT_HTTPHEADER, Array("Content-Type:text/xml; charset=utf-8",'Content-Length: '.strlen($data)));
        //$headers = array('Content-Type:application/json; charset=utf-8','Content-Length: '.strlen($data));
        //curl_setopt($ch, CURLOPT_HTTPHEADER,$headers);
        //执行请求
        $output = curl_exec($ch);
        header('Content-Type:text/xml; charset=utf-8');
        //打印获得的数据
        curl_close($ch);
        return $output;
        //$ip = file_get_contents("http://ip-api.com/json");
        //return $ip;
    }

    public static function getCurl($url, $second = 3)
    {
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); //严格校验
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //post提交方式
        //curl_setopt($ch, CURLOPT_POST, TRUE);
        //curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_TIMEOUT, 40);
        set_time_limit(10);
        //运行curl
        $data = curl_exec($ch);
        //返回结果
        if ($data) {
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            return "";
            //return "网络出错，错误码:$error";
        }
    }

    public function getWeixinUser($wxid,$where){

        $field = "a.id,a.status,a.level,a.nickname,a.city,a.icon,a.bid,a.phone,a.hiden,b.yuchanqi as yuqi";
        $wxobj = Db::name("weixin");
        $lists = $wxobj->alias("a")->join([["__STATUS__ b", "a.id=b.wid","left"]])
            ->field($field)
            ->where($where)
            ->order('a.id', 'DESC')
            ->find();

        $array = [];
        $uid = $lists["id"];
        $array["id"] = $lists["id"];
        $array["bid"] = $lists["bid"];
        $array["status"] = getYuStatus($lists["status"]);
        $array["nickname"] = $lists["nickname"];
        $array["city"] = $lists["city"];
        $array["avatar"] = $lists["icon"];
        $array["level"] = $lists["level"];
        $array["phone"] = $lists["phone"];
        $array["hiden"] = $lists["hiden"];
        $array["yuqi"] = date("Y-m-d", strtotime($lists["yuqi"]));

        if(!empty($wxid))
        {
          $array["gzor"] = getGuanzhuOr($wxid,$uid);
        }

        $gzobj = new HuatiModel();
        $array["myguanzhu"] = $gzobj->getGuanzhu($uid,0);
        $array["fans"] = $gzobj->getGuanzhu($uid,1);
        $msg = Db::name("guestbook")->where(["msg_type"=>1])->whereTime('createtime', 'd')->count();
        $comm = Db::name("comment")->where(["user_id | pl_id"=>$wxid])->whereTime('create_time', 'd')->count();
        $dianzan = Db::name("dianzan")->where(["uid | zanid | jubaoid | zhuanid"=>$wxid])->whereTime('datetime', 'd')->count();
        $array["msg"] = $msg + $comm + $dianzan;

        return $array;
    }

    public function getRandCode($m)
    {
        $charts = "ABCDEFGHJKLMNPQRSTUVWXYZabcdefghjkmnpqrstuvwxyz0123456789";
        $max = strlen($charts)-1;
        $noncestr = "";
        for($i = 0; $i < $m; $i++)
        {
            $noncestr .= $charts[mt_rand(0, $max)];
        }
        return $noncestr;
    }

    function getSign($array,$pay_key)
    {
        unset($array['sign']);
        ksort($array);
        $stringA = urldecode(http_build_query($array));
        $stringSignTemp="$stringA&key=".$pay_key;
        return strtoupper(md5($stringSignTemp));
    }

    //作用：生成签名
    function getSigns($Obj,$key) {
        unset($Obj['sign']);
        foreach ($Obj as $k => $v) {
            $Parameters[$k] = $v;
        }
        //签名步骤一：按字典序排序参数
        ksort($Parameters);
        $String = $this->formatBizQueryParaMap($Parameters, false);
        //签名步骤二：在string后加入KEY
        $String = $String . "&key=" . $key;
        //签名步骤三：MD5加密
        $String = md5($String);
        //签名步骤四：所有字符转为大写
        $result_ = strtoupper($String);
        return $result_;
    }

    ///作用：格式化参数，签名过程需要使用
    function formatBizQueryParaMap($paraMap, $urlencode) {
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v) {
            if ($urlencode) {
                $v = urlencode($v);
            }
            $buff .= $k . "=" . $v . "&";
        }
        $reqPar = "";
        if (strlen($buff) > 0) {
            $reqPar = substr($buff, 0, strlen($buff) - 1);
        }
        return $reqPar;
    }

    //xml转换成数组
     function xmlToArray($xml) {

        //禁止引用外部xml实体
        libxml_disable_entity_loader(true);
        $xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
        $val = json_decode(json_encode($xmlstring), true);
        return $val;
    }

    //数组转换成xml
    function arrayToXml($arr) {
        $xml = "<root>";
        foreach ($arr as $key => $val) {
            if (is_array($val)) {
                $xml .= "<" . $key . ">" . arrayToXml($val) . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            }
        }
        $xml .= "</root>";
        return $xml;
    }

    private static function postXmlCurl($data, $url)
    {
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE); //严格校验
        //设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        //post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);

        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 20);
        curl_setopt($ch, CURLOPT_TIMEOUT, 40);
        set_time_limit(0);
        //运行curl
        $data = curl_exec($ch);
        //返回结果
        if ($data) {
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            curl_close($ch);
            //throw new WxPayException("curl出错，错误码:$error");
        }
    }


    //获取access_token
    public function getAccessToken(){
        $weixin = $this->getWeixinInfo();
        $appid = $weixin["appid"];
        $secret = $weixin["secret"];
        $url = "https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid=".$appid."&secret=".$secret;
        //$curl = new \Curl();
        //$curl->get($url);
        //$result = $curl->response;
        $result = $this->getCurl($url, 60);
        return $result;
    }

    //获取小程序二维码
    public function getQrCode($bid){
        $wxObject = new WeixinModel();
        $access_token = $wxObject->getAccessToken();
        //return $access_token;
        $token = json_decode($access_token,true);
        //return $token["access_token"];
        //$access_token="9_rdePnWCUtRTNcT2Rb3sEBtFoa7NT6EloIr-7wXYfD4DWCJmjlRAuuTyqHnlRMUhiDXaHldDjoxxd4pxnsVr0vtxK-RGlxEO3ROG2mCYdHofA0nyBzzMYfMCm86llwBMyhCRZx08LWFGXcMhXWEFfAJACKL";

        $url =  "https://api.weixin.qq.com/cgi-bin/wxaapp/createwxaqrcode?access_token=".$token["access_token"];
        //$curl = new \Curl\Curl();
        //$path = "pages/Land/Land?scene=".$bid;
        //$curl->post($url,json_encode(['path' => $path,'width' => 430]));
        $data = '{"width":430,"path":"pages/Land/Land?scene='.$bid.'"}';
        $data = urldecode($data);
        $result = $this->postXmlCurl($data, $url);
        //存储图片
        //return $result;
        $rootdir = $_SERVER['DOCUMENT_ROOT'].'/upload/';
        $filepath = 'qrcode/' . date('Ymd',time()).'/';
        $filename = date('Ymd',time()).time().rand(1000,9999) . '.jpg';
        $saveDir = $rootdir.$filepath;

        if(!file_exists($saveDir)) {
            mkdir($saveDir);
        }
        file_put_contents($saveDir.$filename, $result);
        //move_uploaded_file($result,iconv("utf-8","gb2312",$saveDir.$filename));
        //return substr($filepath.$filename, 1);
        return $filepath.$filename;
    }

}