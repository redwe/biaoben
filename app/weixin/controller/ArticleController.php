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
use app\weixin\model\OrderModel;

class ArticleController extends HomeBaseController
{
    /**
     *文章列表
     */
    public function index()
    {
        $param = $this->request->param();
        if (isset($param['id'])) {
            $id = $this->request->param('id', 0, 'intval');
        }
        else
        {
            $id = 7;
        }
        if (isset($param['uid'])) {
            $uid = $this->request->param('uid', 0, 'intval');
        }
        else
        {
            $uid = 1;
        }
        $where = [
            //'a.user_id' => $uid,
            'a.delete_time' => 0,
            //'b.category_id' => $id
        ];

        if(isset($param['keyword']))
        {
            $keyword = $param['keyword'];
            $where["post_title"] = ['like', "%$keyword%"];
        }

        $join = [
            ['__PORTAL_CATEGORY_POST__ b', 'a.id = b.post_id']
        ];

        $field = 'a.post_title,a.post_excerpt,a.more,a.id as article_id';

        $portalPostModel = Db::name("portal_post");
        $articles        = $portalPostModel->alias('a')->join($join)->field($field)
            ->where($where)
            ->order('update_time', 'DESC')
            ->select();
      $arrs = [];
        foreach($articles as $vo)
        {
            $subarr = [];
            $imglist = json_decode($vo["more"],true);
            $subarr["id"] = $vo["article_id"];
            $subarr["title"] = $vo["post_title"];
            $subarr["thumb"] = $imglist["thumbnail"];
            array_push($arrs,$subarr);
        }
        return json($arrs);
    }

    //显示主题日记图片详情
    public function showZhutidetails(){
        $param = $this->request->param();
        if (isset($param['id'])) {
            $uid = $param['uid'];
            $id = $this->request->param('id', 0, 'intval');
            $picobj = new ArticleModel();
            $result = $picobj->buyPicDetails($id,$uid);
            if($result){
                $result = json($result);
            }
            else
            {
                $result = "";
            }
        }
        else
        {
            $result = "";
        }
        return $result;
    }

    //显示图片日记详情
    public function showPicdetails(){
        $param = $this->request->param();
        if (isset($param['id'])) {
            $uid = $param['uid'];
            $id = $this->request->param('id', 0, 'intval');
            $picobj = new ArticleModel();
            $result = $picobj->getPicDetails($id,$uid);
            if($result){
                $result = json($result);
            }
            else
            {
                $result = "";
            }
        }
        else
        {
            $result = "";
        }
        return $result;
    }

    //获取当前天气
    public  function getWeather(){
        $param = $this->request->param();
        if (isset($param['wxid'])) {
            $wxid = $param['wxid'];
            $weather = getWeatherXml();
            if($weather){
                $weather = xmlToArray($weather);
            }
            else
            {
                $weather = "晴";
            }
            //$wthstr = $weather["city"][10]["@attributes"]["stateDetailed"];
            //$array["weather"] = $wthstr;
            return json($weather);
        }
        return "";
    }

    //显示图片博客详情
    public function showBlogdetails(){
        $param = $this->request->param();
        if (isset($param['id'])) {
            $id = $param['id'];
            $wxid = $param['wxid'];
            //$id = $this->request->param('id', 0, 'intval');
            $picobj = new ArticleModel();
            $result = $picobj->getBlogDetails($wxid,$id);
            if($result){
                $result = json($result);
            }
            else
            {
                $result = "";
            }
        }
        else
        {
            $result = "";
        }
        return $result;
    }

    //显示文章详情页
    public function showarticle(){
        $param = $this->request->param();
        if (!empty($param['id'])) {
            $id = $param['id'];
            $picobj = new ArticleModel();
            $result = $picobj->getArticleInfo($id);
            if($result)
            {
                $articles= json($result);
            }
            else
            {
                $articles= "";
            }
        }
        else
        {
            $articles= "";
        }
        return $articles;
    }

    //首页banner 首页主题日记和图片日记
    public function getbanner(){
        $param = $this->request->param();
        if (isset($param['id'])) {
            $id = $this->request->param('id', 0, 'intval');
        }
        else
        {
            $id = 1;
        }
        //$where["status"] = 1;
        //$order=["id","DESC"];
        $picobj = new ArticleModel();
        $banners = $picobj->getBanner(1,3,0); //首页banner
        $piclist1 = $picobj->getPiclist(1,3,1,""); //主题日记
        $piclist2 = $picobj->getPiclist(2,6,1,""); //图片日记
        $piclist3 = $picobj->getHotpics(3,0); //热门图片
        $huatiobj = new HuatiModel();
        $huati1 = $huatiobj->getHuati(0,2); //孕期话题
        $categary = $picobj->getCategory(1,5); //文章分类

        $arra = [
            "banner"=>$banners,
            "piclist1"=>$piclist1,
            "piclist2"=>$piclist2,
            "piclist3"=>$piclist3,
            "huati"=>$huati1,
            "categary"=>$categary
        ];
        return json($arra);
    }

    //主题日记列表页banner
    public function getZhutiBanner(){
        $param = $this->request->param();
        $id = $this->request->param('id', 1, 'intval');
        $bid = $this->request->param('bid', 0, 'intval');
        $picobj = new ArticleModel();
        $banners = $picobj->getBanner(2,3,$bid); //主题日记banner

        return json($banners);
    }

    //主题日记列表页图片列表
    public function getZhutirj(){
        //$param = $this->request->param();
        $page = $this->request->param('page', 1, 'intval');
        $PageNum = $this->request->param('PageNum', 10, 'intval');
        $id = $this->request->param('id', 1, 'intval');

        $picobj = new ArticleModel();
        $piclist = $picobj->pagePiclist($id,$PageNum,$page,""); //主题日记图片列表

        return json($piclist);
    }

    //图片日记列表页banner
    public function getPicBanner(){
        $param = $this->request->param();
        $id = $this->request->param('id', 2, 'intval');
        $bid = $this->request->param('bid', 0, 'intval');
        $picobj = new ArticleModel();
        $banners = $picobj->getBanner(3,3,$bid); //图片日记banner
        $theme_cate = config("cmf_themes_cate");
        $arra = [
            "banner"=>$banners,
            "theme_cate"=>$theme_cate
        ];
        return json($arra);
    }

    //图片日记列表页图片列表
    public function getPictruerj(){
        $param = $this->request->param();
        $page = $this->request->param('page', 1, 'intval');
        $PageNum = $this->request->param('PageNum', 10, 'intval');
        if (isset($param['id'])) {
            $id = $this->request->param('id', 2, 'intval');
        }
        else
        {
            $id = "";
        }
        if (isset($param['cate'])) {
            $cate = $this->request->param('cate', 0, 'intval');
        }
        else
        {
            $cate = "";
        }
        $picobj = new ArticleModel();
        $piclist = $picobj->pagePiclist(2,$page,$PageNum,$cate); //图片日记图片列表
        return json($piclist);
    }

    public function saveSelectphotos(){

        $param = $this->request->param();
        $uname = isset($param['uname'])?$param['uname']:"";
        $utel = isset($param['utel'])?$param['utel']:"";
        $uaddr = isset($param['uaddr'])?$param['uaddr']:"";
        $themeid = isset($param['themeid'])?$param['themeid']:0;
        $uid = isset($param['uid'])?$param['uid']:0;
        $ids = isset($param['ids'])?$param['ids']:"";
        $price = isset($param['uid'])?$param['price']:0;
        $ptype = isset($param['ptype'])?$param['ptype']:0;
        $saveOr = true;

        if($ptype == "1"){
            $str = "主题日记";
            if(empty($uname) || empty($utel) || empty($uaddr))
            {
                $saveOr = false;
            }
        }
        else
        {
            $str = "相册模板";
            if(empty($themeid) || empty($uid) || empty($ids))
            {
                $saveOr = false;
            }
        }
         if($saveOr)
         {
           $data = [
                    "uname" => $uname,
                    "utel" => $utel,
                    "uaddr" => $uaddr,
                    "themeid" => $themeid,
                    "uid" => $uid,
                    "ids" => $ids,
                    "price" => $price,
                    "status" => 0
                ];
                $result = Db::name("photolist")->insert($data);
                $orderObj = new OrderModel();
                $result = $orderObj->saveOrder($themeid,$uid,$price,$ptype,$str);

              return $result;
         }
        else
        {
            return "";
        }

    }

    //获取相册列表
    public function getPhotolist(){
        $param = $this->request->param();
        if (isset($param['id'])) {
            $id = $this->request->param('id', 2, 'intval');
        }
        else
        {
            $id = "";
        }
        $cate = "";
        $picobj = new ArticleModel();
        $piclist = $picobj->getPiclist(3,100,1,$cate); //图片日记图片列表
        return json($piclist);
    }

    //编辑相册内容
    public function selectPhotos(){
        $param = $this->request->param();
        if (isset($param['id'])) {
            $id = $this->request->param('id', 2, 'intval');
            $uid = $this->request->param('uid', 0, 'intval');
        }
        else
        {
            $id = "";
        }
        $cate = "";
        $picobj = new ArticleModel();
        $piclist = $picobj->getHotpics(30,$uid); //相册图片列表
        return json($piclist);
    }

    //热门图片列表页banner
    public function getHotBanner(){
        $param = $this->request->param();
        $id = $this->request->param('id', 0, 'intval');
        $bid = $this->request->param('bid', 0, 'intval');
        $picobj = new ArticleModel();
        $banners = $picobj->getBanner(4,3,$bid); //热门图片banner
        $where=[
            "status" => [">",0]
        ];
        $field = "id,nickname,icon";
        $order = "datetime DESC";
        $wxlist = Db::name("weixin")->field($field)->where($where)->order($order)->select();
        $arra = [
            "banner"=>$banners,
            "wxlist"=>$wxlist
        ];
        return json($arra);
    }

    //热门图片列表页banner和图片列表
    public function getHotpics(){
        $param = $this->request->param();
        $uid = $this->request->param('uid', 0, 'intval');
        $page = $this->request->param('page', 1, 'intval');
        $PageNum = $this->request->param('PageNum', 10, 'intval');
        if (isset($param['id'])) {
            $id = $this->request->param('id', 0, 'intval');
        }
        else
        {
            $id = 0;
        }
        $picobj = new ArticleModel();
        $piclist = $picobj->getBloglist($uid,$page,$PageNum); //热门图片列表

        return json($piclist);
    }

    public function getKnowledge(){

        $param = $this->request->param();
        $id = $this->request->param('id', 5, 'intval');
        $bid = $this->request->param('bid', 0, 'intval');

        $picobj = new ArticleModel();
        $piclist5 = $picobj->getBanner(6,3,$bid); //孕期知识图片
        $categary = $picobj->getCategory(1,5); //文章分类
        $article = $picobj->getArticle($id,8,4); //孕期知识列表
        $site_info = cmf_get_site_info();
        $arra = [
            "piclist5"=>$piclist5,
            "categary"=>$categary,
            "article" =>$article,
            "site_info" => $site_info
        ];
        return json($arra);
}

    //替换图片日记图片
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
        } else
        {
            $result = "";
        }
        return json($result);
    }

    //替换图片日记图片
    public function uploadHuatiPic()
    {
        $param = $this->request->param();
        if (isset($param["dir"])) {
            $dir = $param["dir"];
            $file = $_FILES["file"];
            $root = "https://".$_SERVER['HTTP_HOST']."/upload/";
            $id = $param["id"];
            $updateWx = new ArticleModel();
            $result = $updateWx->uploadPics($root,$dir,$file);
        } else
        {
            $result = "";
        }
        return json($result);
    }

    //替换图片日记图片
    public function uploadImages()
    {
        $param = $this->request->param();
        if (isset($param["dir"])) {
            $dir = $param["dir"];
            $file = $_FILES["file"];
            $root = "https://".$_SERVER['HTTP_HOST']."/upload/";
            $id = $param["id"];
            $updateWx = new ArticleModel();
            $url = $updateWx->uploadPics($root,$dir,$file);
            if($url){
                $result = Db::name("blog")->where(["id"=>$id])->update(["thumb"=>$url['url']]);
            }
            else
            {
                $result = "";
            }
        } else
        {
            $result = "";
        }
        return $result;
    }

    //保存图片日记编辑内容
    public function saveUserPics(){
        $param = $this->request->param();
        $data = [];
        if (isset($param['id'])) {
            $id = $param['id'];
        }
        else
        {
            $id = "";
        }
        if (isset($param['themeid'])) {
            $tid = $param['themeid'];
        }
        else
        {
            $tid = "";
        }
        if (isset($param['piclist'])) {
            $piclist = $param['piclist'];
        }
        else
        {
            $piclist = "";
        }
        if (isset($param['photos'])) {
            $photos = $param['photos'];
        }
        else
        {
            $photos = "";
        }
        if (isset($param['textarea'])) {
            $textarea = $param['textarea'];
        }
        else
        {
            $textarea = "";
        }
        if (isset($param['remarks'])) {
            $remarks = $param['remarks'];
        }
        else
        {
            $remarks = "";
        }
        if (isset($param['copyright'])) {
            $copyright = $param['copyright'];
        }
        else
        {
            $copyright = "";
        }
        if(!empty($photos) && !empty($id) && !empty($tid))
        {
            $tm = time();
            $data=[
                "uid" => $id,
                "theme" => $tid,
                "footer" => $copyright,
                "textarea" => $textarea,
                "piclist" => $piclist,
                "photos" => $photos,
                "weather" => $remarks,
                "status" => 1
            ];
           $result = Db::name("blog")->insert($data);
            if($result)
            {
                $result = Db::name('blog')->getLastInsID();
            }
            else
            {
                $result = 0;
            }
       }
        else
        {
            $result = 0;
        }
        //dump($remarks);
        return $result;
    }
//个人中心图片列表
    public function getUserPics(){
        $param = $this->request->param();
        $uid = $this->request->param('uid', 0, 'intval');
        $page = $this->request->param('page', 1, 'intval');
        $PageNum = $this->request->param('PageNum', 10, 'intval');
        if(empty($uid)){
            $result = 0;
        }
        else
        {
            $picobj = new ArticleModel();
            $piclist = $picobj->getBloglist($uid,$page,$PageNum); //热门图片列表
            $result = json($piclist);
        }
        return $result;
    }

    public function addFavorite(){
        $param = $this->request->param();

        if (isset($param['id'])) {
            $uid = $param['id'];
        }
        else
        {
            $uid = "";
        }
        if (!empty($param['themeid'])) {
            $tid = $param['themeid'];
            $status = 1;
        }
        else if (!empty($param['articleid'])) {
            $tid = $param['articleid'];
            $status = 2;
        }
        else
        {
            $tid = "";
            $status = 0;
        }

        if(empty($status) || empty($tid) || empty($uid)){
            $result = 0;
        }
        else
        {
            $data = [
                "user_id" => $uid,
                "object_id" => $tid,
                "status" => $status
            ];
            $rels = Db::name("user_favorite")->where($data)->count();
            if($rels){
                $result = 2;
            }
            else
            {
                $result = Db::name("user_favorite")->insert($data);
            }
        }
        return $result;
    }

    public function getFavorite(){
        $param = $this->request->param();
        $arrs = [];
        if (isset($param['id'])) {
            $uid = $param['id'];
        }
        else
        {
            $uid = "";
        }
        if(empty($uid)){
            $result = "";
        }
        else
        {
            $where = [
                "a.user_id" => $uid
            ];
            $field = "a.*,b.nickname,b.icon,b.level,b.status as stus,c.post_title,d.title";
            $join = [
                ["__WEIXIN__ b","a.user_id=b.id","left"],
                ["__PORTAL_POST__ c","a.object_id=c.id","left"],
                ["__USER_THEME__ d","a.object_id=d.id","left"]
            ];
            $result = Db::name("user_favorite")->alias("a")->join($join)->field($field)->where($where)->select();

            foreach($result as $key => $vo)
            {
                if($vo["level"]){
                    $lv = $vo["level"];
                }
                else
                {
                    $lv = 1;
                }
                $array = [];
                $array["id"] = $vo["id"];
                $array["uid"] = $vo["user_id"];
                $array["tid"] = $vo["object_id"];
                $array["status"] = $vo["status"];
                $array["stus"] = getYuStatus($vo["stus"]);
                $array["lv"] = $vo["level"];
                if($vo["status"]==1){
                    $array["title"] = $vo["title"];
                    $array["class"] = "图片日记";
                }
                else
                {
                    $array["title"] = $vo["post_title"];
                    $array["class"] = "孕育知识";
                }
                $array["nickname"] = $vo["nickname"];
                $array["avatar"] = $vo["icon"];
                array_push($arrs,$array);
            }
            $result = json($arrs);
        }
        return $result;
    }
}