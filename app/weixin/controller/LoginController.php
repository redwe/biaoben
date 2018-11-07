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

use app\weixin\model\HuatiModel;
use app\weixin\model\OrderModel;
use think\Validate;
use cmf\controller\HomeBaseController;
use app\user\model\UserModel;
use think\Session;
use think\Db;
use WXBizDataCrypt;
use ErrorCode;
use app\weixin\model\ArticleModel;
use app\weixin\model\WeixinModel;

class LoginController extends HomeBaseController
{

    //登录
    public function index()
    {
        return $this->fetch();
    }
    // 登录验证提交
    public function doLogin()
    {
        header('content-type:application/json;charset=utf8');
        $param = $this->request->param();
        $data = [];
        $apiData = null;
        $openid = Session::get('openid');
        if (empty($openid) && isset($param['code'])) {
            $code = $param['code'];

            $wxobj = new WeixinModel();
            $Weixin = $wxobj->getWeixinInfo();
            $appid = $Weixin["appid"];
            $secret = $Weixin["secret"];
            $grant_type = $Weixin["grant_type"];

            $url = 'https://api.weixin.qq.com/sns/jscode2session?appid=' . $appid . '&secret=' . $secret . '&js_code=' . $code . '&grant_type=' . $grant_type;

            $apiData = file_get_contents($url);

            if (strpos($apiData, 'openid')) {

                $apiData = json_decode($apiData, true);
                $openid = $apiData['openid'];
                $session_key = $apiData['session_key'];

                Session::set('openid', $openid);
                Session::set('session_key', $session_key);

                $data['openid'] = $openid;
                $data['session_key'] = $session_key;
            } else {
                return $apiData;
            }
            $data["code"] = $code;
        }
        return json($data);
    }

//保存微信用户信息
    public function saveUserinfo()
    {
            $param = $this->request->param();
            $res = "";
            $openid = "";
            $avatarUrl = "";
            $nickname = "";
            $gender = 0;
            $city = "";
            $province = "";
            $country = "";
            $language = "";

            if (isset($param['openid'])) {
                $openid = $param['openid'];
            }
            if (isset($param['nickname'])) {
                $nickname = $param['nickname'];
            }
            if (isset($param['avatarUrl'])) {
                $avatarUrl = $param['avatarUrl'];
            }
            if (isset($param['gender'])) {
                $gender = $param['gender'];
            }
            if (isset($param['city'])) {
                $city = $param['city'];
            }
            if (isset($param['province'])) {
                $province = $param['province'];
            }
            if (isset($param['country'])) {
                $country = $param['country'];
            }
            if (isset($param['language'])) {
                $language = $param['language'];
            }
            $tm = time();

            $insert = [
                //"bid" => $bid,
                "openid" => $openid,
                "nickname" => $nickname,
                "gender" => $gender,
                "city" => $city,
                "province" => $province,
                "country" => $country,
                "language" => $language,
                "icon" => $avatarUrl,
                "status" => 1,
                //"unionid" => "",
                "datetime" => $tm,
                "level" => 1
            ];
            if (!empty($openid)) {
                $isuser = Db::name("weixin")->where(["openid" => $openid])->find();
                if ($isuser) {
                    /* $res = Db::name("weixin")->where(["openid"=>$openid])->update($insert);
                     if($res){$re = 2;}else{$re = 0;}*/
                    $uid = $isuser["id"];
                }
                else
                {
                    $res = Db::name("weixin")->insert($insert);
                    $uid = Db::name('weixin')->getLastInsID();
                   /* if($res){
                        $uid = Db::name('weixin')->getLastInsID();
                        $res = $this->getYouhuiquan($uid,"注册新会员赠送优惠券");
                        $jifenData=[
                            "uid"=>$uid,
                            "jifen"=>$jifen,
                            "remark"=>"注册或登录赠送积分"
                        ];
                        $reg = Db::name("jifens")->insert($jifenData);
                    }*/
                }
            }
            else
            {
                $uid = "";
            }
            return $uid;
    }

    public function getYouhuiquan($uid,$str){
        $tm = time();
        $result = Db::name('tequan')->field("id,endtime")->where(["tiaojian"=>0])->find();
        $data = [
            "uid" => $uid,
            "tid" => $result['id'],
            "endtime" => $result['endtime'],
            "status" => 1,
            "remark" => $str
            ];
        $res = Db::name("youhuiquan")->insert($data);
        return $res;
    }

//退出登录状态
    public function logout()
    {
        Session::delete('session_key');
        Session::delete('openid');
        Session::clear();
        return 1;
    }

//检查登录状态
    public function getCookis()
    {
        $openid = Session::get('openid');
        $session_key = Session::get('session_key');
        if (!empty($openid) && !empty($session_key)) {
            return $openid;
        } else {
            return "";
        }
    }

    //获取微信用户信息
    public function getweixin()
    {
        $isCheck = $this->checkTokenId();
        if($isCheck){
            $openid = Session::get('openid');
            $param = $this->request->param();

            $randnums = mt_rand(100000,999999);
            $utm = date("Ymdhis",time());
            $randnums=$utm.$randnums;

            Session::set('token_id', $randnums);

            if (isset($param["openid"])) {
                $openid = $param["openid"];
                $where = [
                    'a.openid' => $openid
                ];
                $wxobje = new WeixinModel();
                $result = $wxobje->getWeixinUser(0,$where);
                return json($result);
            } else {
                return '';
            }
        }
        else
        {
            return "";
        }
    }

    //获取微信用户信息
    public function getWxuser()
    {
        $isCheck = $this->checkTokenId();
        if($isCheck){
            $param = $this->request->param();
            if (isset($param["id"])) {
                $uid = $param["id"];
                $wxid = $param["wxid"];
                $where = [
                    'a.id' => $uid
                ];
                $wxobje = new WeixinModel();
                $result = $wxobje->getWeixinUser($wxid,$where);
                return json($result);
            } else {
                return '';
            }
        }
        else
        {
            return "";
        }
    }

    //备孕
    public function beiyun()
    {

        $param = $this->request->param();

        if (!empty($param["id"]) && !empty($param["moci"])) {
            $id = $param["id"];
            $moci = $param["moci"];
            $zhouqi = $param["zhouqi"];
            $daynum = $param["daynum"];
            $field = [
                "moci" => $moci,
                "zhouqi" => $zhouqi,
                "daynum" => $daynum
            ];
            $updateWx = new ArticleModel();
            $result = $updateWx->updateWeixin(1, $id, $field);
        } else {
            $result = '';
        }
        return $result;
    }

    //更新预产期
    public function yuchanqi()
    {

        $param = $this->request->param();

        if (!empty($param["id"]) && !empty($param["yuchanqi"])) {
            $id = $param["id"];
            $yuchanqi = $param["yuchanqi"];
            $field = [
                "yuchanqi" => $yuchanqi
            ];
            $updateWx = new ArticleModel();
            $result = $updateWx->updateWeixin(2, $id, $field);
        } else {
            $result = "";
        }
        return $result;
    }

    //更新生产方式及生日
    public function birthday()
    {
        $param = $this->request->param();
        if (isset($param["id"]) && isset($param["method"])) {
            $id = $param["id"];
            $Birthday = $param["Birthday"];
            $method = $param["method"];
            $field = [
                "Birthday" => $Birthday,
                "mode" => $method
            ];
            $updateWx = new ArticleModel();
            $result = $updateWx->updateWeixin(3, $id, $field);
        } else {
            $result = "";
        }
        return $result;
    }

    //更新宝贝信息
    public function babyinfo()
    {
        $param = $this->request->param();
        if (!empty($param["id"]) && !empty($param["babyname"])) {
            $id = $param["id"];
            $babyname = $param["babyname"];
            $birthday = $param["birthday"];
            $sex = $param["sex"];
            $field = [
                "Birthday" => $birthday,
                "babyname" => $babyname,
                "sex" => $sex
            ];
            $updateWx = new ArticleModel();
            $result = $updateWx->updateWeixin(4, $id, $field);
        } else {
            $result = "";
        }
        return $result;
    }

    public function getCode()
    {
        $isCheck = $this->checkTokenId();
        if($isCheck){
            $randnums0 = "";
            $param = $this->request->param();
            $phoneNum = $param["phoneNum"];
            $openid = $param["openid"];
            $nowtime = time();
            $isOk = true;
            $currtime = Session::get('currtime');

            if(!empty($currtime)){
                $timecha = $nowtime - $currtime;
                if($timecha>60){
                    Session::delete('randnums');  //清除验证码
                    Session::delete('currtime');   //清除验证码的时间
                    $isOk = true;
                }
                else
                {
                    $isOk = false;
                    $result = -2;   //请等待60秒后再试。
                }
            }
            if ($isOk)
            {
                $randnums0 = mt_rand(10000, 99999);
                Session::set('randnums', $randnums0);  //存储获取的验证码
                Session::set('currtime', $nowtime);   //获取验证码的时间

                $data = [
                    "openid" => $openid,
                    "code" => $randnums0
                ];

                Db::name("codes")->insert($data);
                $isOk = true;
                $randnums = Session::get('randnums');
                    //发送短信代码
                  $content = "您好,你的验证码:".$randnums."【孕育日记】";
                $senobj = new OrderModel();
                $result = $senobj->sendMessage($phoneNum, $content, '','', '');
                $result = $randnums;
            }
        }
        else
        {
            $result = 0; //身份未通过验证
        }
        return $result;
    }

    //更新手机号码
    public function bindphone()
    {
        $isCheck = $this->checkTokenId();
        if($isCheck){
            $param = $this->request->param();
            if (isset($param["id"]) && isset($param["phone"]) && isset($param["yzm"])) {
                $id = $param["id"];
                $phone = $param["phone"];
                $openid = $param["openid"];
                $yzm = $param["yzm"];
                $randnums = Session::get('randnums');
                $result = Db::name("weixin")->where(["phone"=>$phone])->count();
                if($result==0){
                    $where = [
                        "openid" => $openid
                    ];
                    $codeData = Db::name("codes")->where($where)->order("id desc")->find();
                    $yzmcode = $codeData["code"];
                    if($yzm==$yzmcode)
                    {
                        $field = [
                            "id" => $id,
                            "phone" => $phone
                        ];
                        $where = [
                            "id" => $id
                        ];
                        $result = Db::name("weixin")->where($where)->update($field);
                    }
                    else
                    {
                        $result = -1;   //验证码不正确
                    }
                }
                else
                {
                    $result = -2;   //该手机号码已经被绑定
                }

            } else {
                $result = 0;   //验证码、手机号、或者用户ID不存在
            }
        }
        else
        {
            $result = -3;   //缺少登录身份验证
        }
            return $result;
    }

    public function saveNickname()
    {
        $param = $this->request->param();
        $id = 0;
        $nickname = "";
        if (isset($param["id"]) && isset($param["nickname"])) {
            $id = $param["id"];
            $nickname = $param["nickname"];
            $field = [
                "nickname" => $nickname
            ];
            $where = [
                "id" => $id
            ];
            $result = Db::name("weixin")->where($where)->update($field);
        } else {
            $result = "";
        }
        return $result;
    }

    //上传头像
    public function uploadPhoto()
    {
        $param = $this->request->param();
        if (isset($param["dir"])) {
            $dir = $param["dir"];
            $file = $_FILES["file"];
            $root = "https://".$_SERVER['HTTP_HOST']."/upload/";
            $id = $param["id"];
            $updateWx = new ArticleModel();
            $result = $updateWx->uploadPics($root,$dir,$file);
          if($result)
            {
                $field = [
                    "icon" => $result["url"]
                ];
                $where = [
                    "id" => $id
                ];
                $upWx = Db::name("weixin");
                $res = $upWx->where($where)->update($field);
            }
            else
            {
                $res = "";
            }

        } else
        {
            $result = "";
        }
        return json($result);
    }

//保存留言信息
    public function seveGuestbook()
    {
        $param = $this->request->param();

        if (!empty($param["id"]) && !empty($param["content"])) {
            $id = $param["id"];
            $phone = $param["phone"];
            $content = $param["content"];
            $typeid = $param["typeid"];

            $field = [
                "parent_id" => 0,
                "title" => "反馈信息：" . $id,
                "uid" => $id,
                "msg_type" => 4,
                "tel" => $phone,
                "msg" => $content,
                "createtime" => time(),
                "status" => 1
            ];
            $guestObj = Db::name("guestbook");
            $result = $guestObj->insert($field);
        } else {
            $result = "";
        }
        return $result;
    }

    public function getQrCode(){

        $param = $this->request->param();

        if (!empty($param["bid"])) {
            $bid = $param["bid"];
            $wxObj = new WeixinModel();
            $erweima = $wxObj->getQrCode($bid);
            return $erweima;
        }
        else
        {
            return "";
        }
    }

    //设置个人隐私权限， 0表示所有人可见，1关注的人可见，2只有自己可见。主要控制发布的话题和图片日记
    public function showOrhide(){
        $param = $this->request->param();

        if (isset($param["uid"]) && isset($param["hiden"])) {
            $uid = $param["uid"];
            $hiden = $param["hiden"];
            $result = Db::name("weixin")->field("hiden")->where(["id"=>$uid])->update(["hiden"=>$hiden]);
            return $result;
        }
        else
        {
            return "";
        }
    }

    public function  getBuserQr(){
        $param = $this->request->param();
        if (!empty($param["bid"])) {
            $bid = $param["bid"];
            $erweima = Db::name("banks")->field("erweima")->where(["uid"=>$bid])->find();
            return json($erweima);
        }
        else
        {
            return "";
        }
    }

    public function delpics(){
        $param = $this->request->param();
        if (isset($param["id"])) {
            $id = $param["id"];
            $result = Db::name("blog")->where(["id"=>$id])->update(["status"=>0]);
            return $result;
        }
        else
        {
            return "";
        }
    }
}