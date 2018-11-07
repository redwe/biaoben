<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2016 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 流年 <liu21st@gmail.com>
// +----------------------------------------------------------------------

// 应用公共文件

use think\Db;
use app\weixin\model\WeixinModel;

function getClassname($id){
	$up = Db::table('newsclass')->where(array('cateid'=>$id))->find();
	return $up['classname'];
}

function getXMCode($m,$n,$x){          //m为起始号，n为0则随机产生，为1则返回m的下一位数，x位总长度，位数不足则前面补零
    if($n==0){
        $rundnum = rand(10*($x-1), 10*$x-1);
    }
    else
    {
        $rundnum = $m;
    }
    $rundnum++;
    $rundnum = sprintf("%0".$x."d", $rundnum);
    //$tm = date("y");        //取年度
    return $rundnum;
}

function remove_comment($arr)
{
    return (substr($arr, 0,2) != '--');
}

function getStatus($id)
{
    $list = config('cmf_orders_status');
    if($id>0){
        return $list[$id];
    }
    else
    {
        return "已删除";
    }
}

function get_theme_cont(){
    $list = config("cmf_themes_contant");
    return $list;
}

function get_jifen($str){
    $list = config("cmf_jifens");
    return $list[$str];
}

function get_status($m){
    switch($m){
        case 1:
            $temp="已接收";break;
        case 2:
            $temp="已分发";break;
        case 3:
            $temp="已回收";break;
        case 4:
            $temp="已返还";break;
        default:
            $temp="未接收";break;
    }
    return $temp;
}

function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=false){
    if(function_exists("mb_substr")){
        if($suffix)
            return mb_substr($str, $start, $length, $charset)."...";
        else
            return mb_substr($str, $start, $length, $charset);
    }elseif(function_exists('iconv_substr')) {
        if($suffix)
            return iconv_substr($str,$start,$length,$charset)."...";
        else
            return iconv_substr($str,$start,$length,$charset);
    }
    $re['utf-8'] = "/[x01-x7f]|[xc2-xdf][x80-xbf]|[xe0-xef][x80-xbf]{2}|[xf0-xff][x80-xbf]{3}/";
    $re['gb2312'] = "/[x01-x7f]|[xb0-xf7][xa0-xfe]/";
    $re['gbk'] = "/[x01-x7f]|[x81-xfe][x40-xfe]/";
    $re['big5'] = "/[x01-x7f]|[x81-xfe]([x40-x7e]|xa1-xfe])/";
    preg_match_all($re[$charset], $str, $match);
    $slice = join("",array_slice($match[0], $start, $length));
    if($suffix) return $slice."…";
    return $slice;
}

function getUserList($id)
{
    $userlist = Db::name("user")->where("id",$id)->find();
    return $userlist;
}

function getUserNickname($id)
{
    $userlist = Db::name("user")->where("id",$id)->find();

    if(empty($userlist['mobile']))
    {
        $usernick = $userlist['user_nickname'];
    }
    else
    {
        $usernick = $userlist['mobile'];
    }
    return $usernick;
}

function getInfoStr($m){
    if($m==1){
        $marray = ['status'=>"结算",'fuhao'=>"+"];
    }
    else
    {
        $marray = ['status'=>"提现",'fuhao'=>"-"];
    }
    return $marray;
}

function getSexStr($m){
    if($m==1){
        $marray = "男";
    }
    else
    {
        $marray = "女";
    }
    return $marray;
}

function getInfoStat($m){
    switch($m){
        case 1:
            $mstr = "已通过";
            break;
        case 2:
            $mstr = "未通过";
            break;
        default:
            $mstr = "未审核";
    }
    return $mstr;
}

function getYuStatus($m){
    switch($m){
        case 0:
            $mstr = "备孕";
            break;
        case 1:
            $mstr = "怀孕";
            break;
        case 2:
            $mstr = "产后";
            break;
        default:
            $mstr = "育儿";
    }
    return $mstr;
}

function getOrderStatus($value)
{
    $list = config('cmf_orders_status');
    return isset($list[$value]) ? $list[$value] : '';
}

function getOrderType($value)
{
    $list = config('cmf_orders_type');
    return isset($list[$value]) ? $list[$value] : '';
}

function getfilelist($path){
    $filelist = opendir($path); //$_SERVER["DOCUMENT_ROOT"]."/public/upload/styles/"
    return $filelist;
}

function getUserYue($userId){
    $shouru = Db::name("finance")->where('uid', $userId)->sum('fa_in');
    $zhichu = Db::name("finance")->where('uid', $userId)->sum('fa_out');
    $yue=$shouru-$zhichu;
    return $yue;
}

function getGuanzhuOr($wxid,$uid){
    $gzor = Db::name("guanzhu")->where(["uid"=>$wxid,"gzid"=>$uid])->find();
    if($gzor){
        $gzor = 1;
    }else{
        $gzor = 0;
    }
    return $gzor;
}

function getBankCom($uid){
    $bankname = Db::name("banks")->where(["uid"=>$uid])->field("bank_com")->find();
    return $bankname["bank_com"];
}

function getFilmNum($oid){
    $nums = Db::name("films")->where(["orderid"=>$oid])->count();
    return $nums;
}

function getJubaoNum($uid){
    $where["jubaoid"] = [">",0];
    $where["uid"] = $uid;
    $nums = Db::name("dianzan")->where($where)->count();
    return $nums;
}

function getTokenid(){
    $token_str = config('cmf_tokenid');
    $token = urlencode($token_str);
    $token = substr($token,1,9);
    return $token;
}


function getCommentNum($id){
    $nums = Db::name("comment")->where(["article_id"=>$id])->count();
    return $nums;
}

function comment($param)
{
    $join   = [
        ['__USER__ u', 'a.user_id = u.id']
    ];
    $where = [];
    $where['status'] = 1;
    $where['article_id'] = $param['object_id'];
    $comments = Db::name('comment')
        ->field('a.*,u.user_login,u.avatar')
        ->alias('a')->join($join)
        ->where($where)
        ->order("id DESC")
        ->paginate(10);
    $page = $comments->render();
    $this->assign("page", $page);
    $this->assign("comments", $comments);
    if(cmf_get_current_user_id()>0){
        $this->assign($param);
        return $this->fetch('comment');
    }else{
        return $this->fetch('nocomment');
    }
}

function getReComment($id){
    $relist = Db::name("comment")
        ->alias("a")
        ->field("a.*,u.id as uid,u.avatar as avatar")
        ->join([["__USER__ u","a.user_id=u.id"]])
        ->where(["a.parent_id"=>$id])
        ->select();
    return $relist;
}

function getStatusAttr($id,$arry){
    return $arry[$id];
}

function getUserName($uid){
    $relist = Db::name("user")->where(["id"=>$uid])->find();
    $nickname = $relist["user_nickname"];
    $mobile = $relist["mobile"];
    if(empty($nickname)){
        return $mobile;
    }
    else
    {
        return $nickname;
    }
}

function getWeatherXml()
{
    $url = "http://flash.weather.com.cn/wmaps/xml/china.xml";
    $curl = new WeixinModel();
    $contentxml = $curl->getCurl($url,1);
    //$contentxml = file_get_contents($url); //simplexml_load_file($url);  //取得xml对象，需要转化为数组
    return $contentxml;
}

//xml转换成数组
function xmlToArray($xml) {

    //禁止引用外部xml实体
    libxml_disable_entity_loader(true);
    $xmlstring = simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA);
    $val = json_decode(json_encode($xmlstring), true);
    return $val;
}

function getImageWHTL($style,$shapes){
    $imageArray = [];
    if(empty($shapes)){
        $shapesArray = ["4","4","4","4","4","4","4"];
    }
    else
    {
        $shapesArray = explode(",",$shapes);
    }
    foreach($style as $key => $vo){
        $tempstr = str_replace('px',"",$vo);
        $tempstr = str_replace(' ',"",$tempstr);
        $temparr = explode(";",$tempstr);
        $arras = [];
        foreach($temparr as $k => $v2){
            $temparr2 = explode(":",$v2);
            if (isset($temparr2[1]))
            {
                $arras[$temparr2[0]] = $temparr2[1];
            }
        }
        if(isset($shapesArray[$key]))
        {
            $arras["shape"] = $shapesArray[$key];
        }
        else
        {
            $arras["shape"] = "4";
        }

        $imageArray[$key] = $arras;
    }
    return $imageArray;
}

function getTextWHTL($style){

    $tempstr = str_replace('px',"",$style);
    $tempstr = str_replace('margin-',"",$tempstr);
    $tempstr = str_replace('padding-',"",$tempstr);
    $tempstr = str_replace(' ',"",$tempstr);
    $temparr = explode(";",$tempstr);

    $arras = [];
    foreach($temparr as $k => $v2){
        $temparr2 = explode(":",$v2);
        if (isset($temparr2[1]))
        {
            $arras[$temparr2[0]] = $temparr2[1];
        }
    }
    return $arras;
}