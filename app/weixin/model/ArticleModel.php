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
use app\admin\model\ThemesModel;

class ArticleModel extends Model
{
    //获取banner图片
    public function getBanner($id,$m,$bid)
    {
        //$id为幻灯图片列表ID，1位首页，2为主题日记，3为图片日记，4热门图片，5孕期知识
        switch ($id) {
            case 1:
                $pid = 0;  //默认值
                break;
            case 2:
                $pid = 7;  //活动主题日记的分类ID
                break;
            case 3:
                $pid = 8;  //活动图片日记的分类ID
                break;
            case 4:
                $pid = 9;  //活动热门图片的分类ID
                break;
            case 5:
                $pid = 10;  //活动热门话题的分类ID
                break;
            case 6:
                $pid = 12;  //活动孕期知识的分类ID
                break;
            default:
                $pid = 0;
        }

        $where = [
            'slide_id' => $id
        ];
        $field = 'id,user_id,image,title,url';
        $banner = Db::name("slide_item");
        $lists    = $banner
            ->field($field)
            ->where($where)
            ->limit($m)
            ->order('id', 'DESC')
            ->select();

        if (!empty($bid))
        {
            $bimglist = Db::name("portal_post")
                ->alias("a")
                ->join([["__PORTAL_CATEGORY_POST__ b", "a.id=b.post_id"]])
                ->field("a.id,a.post_title,a.more")
                ->where(["a.user_id" => $bid, "b.category_id" => $pid])
                ->order("a.id desc")
                ->find();

            if($bimglist){
                $more = $bimglist["more"];
                $more = \Qiniu\json_decode($more, true);
                $thumb = $more["thumbnail"];
                $array = [];
                if($thumb){
                   $array["id"] = $lists[0]["id"];
                   $array["user_id"] = $lists[0]["user_id"];
                   $array["image"] = $more["thumbnail"];
                   $array["title"] = $bimglist["post_title"];
                   $array["url"] = $bimglist["id"];
                   $lists[0] = $array;
                 }
            }

            return $lists;
        }
        else
        {
            return $lists;
        }

    }

    //获取主题日记、图片日记列表
    public function getPiclist($id,$m,$p,$cate)   //$m每页几条，$p第几页
    {
        $where['theme_class'] = $id;
        $where['status'] = 1;
        if($id==2){
            if(isset($cate)){
                $where["theme_cate"] = $cate;
            }
        }
        $count = Db::name("user_theme")->where($where)->count();
        $start = $m*($p-1);
        $field = 'id,title,bg_img,price,theme_cate';
        $banner = Db::name("user_theme");
        $lists    = $banner
            ->field($field)
            ->where($where)
            ->limit($start,$m)
            ->order('id', 'DESC')
            ->select();
        return $lists;
    }

    //获取主题日记、图片日记列表
    public function pagePiclist($id,$m,$p,$cate)   //$m每页几条，$p第几页
    {
        $where['theme_class'] = $id;
        $where['status'] = 1;
        if($id==2){
            if(isset($cate)){
                $where["theme_cate"] = $cate;
            }
        }
        $count = Db::name("user_theme")->where($where)->count();
        $pnum = ceil($count/$m);  //向上取整，计算页数
        if($p>$pnum){
            $p = 1;
        }
        if($p<=0)
        {
            $p  = 1;
        }
        $start = $m*($p-1);
        $field = 'id,title,bg_img,price,theme_cate';
        $banner = Db::name("user_theme");
        $lists    = $banner
            ->field($field)
            ->where($where)
            ->limit($start,$m)
            ->order('id', 'DESC')
            ->select();
        $arras = [
            "count"=> $count,
            "list"=>$lists
        ];
        return $arras;
    }

    //获取热门图片列表
    public function  getHotpics($m,$uid=0){
        $where['a.status'] = 1;
        if(!empty($uid)){
            $where['a.uid'] = $uid;
        }
        $theme_cate = config("cmf_themes_cate");
        $field = 'a.id,a.uid,a.thumb,a.piclist,a.theme,c.editimg,b.icon,b.nickname';
        $join = [
            ["__WEIXIN__ b","a.uid = b.id"],
            ["__USER_THEME__ c","a.theme = c.id"]
        ];
        $banner = Db::name("blog");
        $lists    = $banner->alias("a")
            ->field($field)
            ->where($where)
            ->join($join)
            ->limit($m)
            ->order('id', 'DESC')
            ->select();
        $arrs = [];
        foreach($lists as $key => $vo)
        {
            $morepic = htmlspecialchars_decode($vo["piclist"]);
            $morepic = json_decode($morepic,true);
            $array = [];
            $array["id"] = $vo["id"];
            $array["uid"] = $vo["uid"];
            $array["theme"] = $vo["theme"];

            $editimg = $vo["editimg"];
            $editarray = explode(",",$editimg);
            foreach($editarray as $k =>$v)
            {
                  if($v=="1")
                    {
                        $array["image"] = $morepic["img".$k];
                        break;
                    }
            }
            if($vo["thumb"])
            {
                $array["image"] = $vo["thumb"];
            }
           /* if(empty($array["image"]))
            {
                $array["image"] = $morepic["img1"];
            }*/

            $array["nickname"] = $vo["nickname"];
            $array["avatar"] = $vo["icon"];

            array_push($arrs,$array);
        }
        return $arrs;
    }

    //获取主题日记、图片日记详情
    public function getPicDetails($id,$uid)
    {
        $where['id'] = $id;
        $field = 'id,title,styletext,bg_img,price,pic_url,editimg,contant,theme_class,theme_cate,remark,copyright';
        $banner = Db::name("user_theme");
        $lists    = $banner
            ->field($field)
            ->where($where)
            ->order('id', 'DESC')
            ->find();

        if($lists){
            $arrimg=[];
            $theme_cate = config("cmf_themes_cate");
            $styletext = htmlspecialchars_decode($lists["styletext"]);

            if($lists["theme_class"]==2) {

                $styletext = str_replace(array("\r\n", "\r", "\n", " "), "", $styletext);
                //$stylearray = explode(",\r\n",$styletext);

                $styletext2 = str_replace("rpx", "px", $styletext);
                $styletext2 = str_replace("margin-", "", $styletext2);
                $styletext2 = str_replace("padding-", "", $styletext2);
                $styletext2 = str_replace("-", "_", $styletext2);
                $styletext2 = json_decode($styletext2, true);

                $titleWhxy = $styletext2["title"];
                $imageWhxy = $styletext2["container_box"];
                $textWhxy = $styletext2["container_p"];
                $bottomWhxy = $styletext2["container_bottom"];
                $container_z = $styletext2["container_z"];

                $titleArray = getTextWHTL($titleWhxy); //日期天气宽度高度
                $textArray = getTextWHTL($textWhxy);     //段落文字的宽度高度
                $bottomArray = getTextWHTL($bottomWhxy);  //落款宽度高度
                $imageArray = getImageWHTL($imageWhxy, "");  //图片宽高和坐标和形状
                $container = getTextWHTL($container_z);  //图片宽高和坐标和形状

                $imageData = [
                    "container" => $container,
                    "titleArray" => $titleArray,
                    "textArray" => $textArray,
                    "bottomArray" => $bottomArray,
                    "imageArray" => $imageArray
                ];

                $contant = json_decode($lists["contant"], true);

                if (in_array("1", $contant)) {
                    $array["datetime"] = date("Y/m/d", time());
                }
                if (in_array("2", $contant)) {
                    $lunarobj = new ThemesModel();
                    $tm = time();
                    $year = date("Y", $tm);
                    $mouth = date("m", $tm);
                    $day = date("d", $tm);
                    $nongli = $lunarobj->getLunar($year, $mouth, $day);

                    $array["nongli"] = $nongli[1] . $nongli[2];
                }
                if (in_array("3", $contant)) {
                    $array["week"] = "星期" . mb_substr("日一二三四五六", date("w"), 1, "utf-8");
                }
                if (in_array("4", $contant)) {
                    /*
                    $weather = getWeatherXml();
                    $weather = xmlToArray($weather);
                    $wthstr = $weather["city"][10]["@attributes"]["stateDetailed"];
                    $array["weather"] = $wthstr;
                    */
                    $array["weather"] = "晴";
                }
                $array["imageData"] = $imageData;
                $array["copyright"] = $lists["copyright"];
            }
            $array["id"] = $lists["id"];
            $array["title"] = $lists["title"];
            $array["sort"] = mb_substr($lists["title"],1,30,"UTF-8");
            $array["cate"] = $lists["theme_cate"];
            $array["catetext"] = $theme_cate[$lists["theme_cate"]];
            $array["styletext"] = $styletext;

            $array["remark"] = htmlspecialchars_decode($lists["remark"]);

            $array["price"] = $lists["price"];
            $piclist = json_decode($lists["pic_url"],true);
            if(count($piclist)>0){
                foreach($piclist as $key => $vo)
                {
                    array_push($arrimg,$vo);
                }
            }
            $array["image"] = $arrimg;
            $array["editimg"] = $lists["editimg"];
            $array["thumb"] = $lists["bg_img"];

            $guanzhu = Db::name("guanzhu")->where(["uid"=>$uid,"sts"=>1])->count();
            $fans = Db::name("guanzhu")->where(["gzid"=>$uid,"sts"=>1])->count();
            $array["guanzhu"] = $guanzhu;
            $array["fans"] = $fans;
        }
        else
        {
            $array = [];
        }
        return $array;
    }


    //获取主题日记、图片日记详情
    public function buyPicDetails($id,$uid)
    {
        $where['id'] = $id;
        $field = 'id,title,styletext,bg_img,price,pic_url,editimg,contant,theme_class,theme_cate,remark,copyright';
        $banner = Db::name("user_theme");
        $lists    = $banner
            ->field($field)
            ->where($where)
            ->order('id', 'DESC')
            ->find();
        $userInfo = Db::name("photolist")->field("uname,utel,uaddr")->where(["uid"=>$uid])->order("id desc")->find();
        if($lists){
            $arrimg=[];
            $theme_cate = config("cmf_themes_cate");
            $styletext = htmlspecialchars_decode($lists["styletext"]);

            $array["id"] = $lists["id"];
            $array["title"] = $lists["title"];
            $array["sort"] = mb_substr($lists["title"],1,30,"UTF-8");
            $array["cate"] = $lists["theme_cate"];
            $array["catetext"] = $theme_cate[$lists["theme_cate"]];
            $array["styletext"] = $styletext;

            $array["remark"] = htmlspecialchars_decode($lists["remark"]);

            $array["price"] = $lists["price"];
            $piclist = json_decode($lists["pic_url"],true);
            if(count($piclist)>0){
                foreach($piclist as $key => $vo)
                {
                    array_push($arrimg,$vo);
                }
            }
            $array["image"] = $arrimg;
            $array["editimg"] = $lists["editimg"];
            $array["thumb"] = $lists["bg_img"];

            $array["uname"] = $userInfo["uname"];
            $array["utel"] = $userInfo["utel"];
            $array["uaddr"] = $userInfo["uaddr"];
        }
        else
        {
            $array = [];
        }
        return $array;
    }

    //保存图片日记用户数据

    public function savePiclist(){

    }

    //获取热门话题

    public function getHuati($id,$m)
    {
       $where["a.status"] = 1;
       if(!empty($id)){
           $where['a.uid'] = $id;
       }
        $field = 'a.id,a.uid,a.title,a.content,a.image,b.icon as avatar,b.nickname as usernick';
        $lists    = Db::name("huati")->alias("a")->join([["__WEIXIN__ b","a.uid = b.id"]])
            ->field($field)
            ->where($where)
            ->limit($m)
            ->order('a.id', 'DESC')
            ->select();
        return $lists;
    }

    //读取文章列表
    public function getArticle($id,$m,$n)
    {
        $where = [
            'a.delete_time' => 0,
            'b.category_id' => $id
        ];

        $join = [
            ['__PORTAL_CATEGORY_POST__ b', 'a.id = b.post_id']
        ];

        $field = 'a.id,a.post_title,a.more';

        $portalPostModel = Db::name("portal_post");
        $articles        = $portalPostModel->alias('a')->join($join)->field($field)
            ->where($where)
            ->limit($m)
            ->order('update_time', 'DESC')
            ->select();

        $arrs = [];
        foreach($articles as $key => $vo)
        {
            $morepic = json_decode($vo["more"],true);
            $array = [];
            $array["id"] = $vo["id"];
            $array["title"] = $vo["post_title"];
            $array["sort"] = mb_substr($vo["post_title"],1,$n,"UTF-8");
            $array["image"] = $morepic["thumbnail"];

            array_push($arrs,$array);
        }

        return $arrs;
    }

    //读取文章详情页

    public function getArticleInfo($id){
        $where = [
            'id' => $id
        ];
        $field = 'id,post_title,post_excerpt,post_content,more,zan,zhuan';
        $portalPostModel = Db::name("portal_post");
        $articles    = $portalPostModel
            ->field($field)
            ->where($where)
            ->order('update_time', 'DESC')
            ->find();
        $images = json_decode($articles["more"],true);
        if(isset($images["photos"]))
        {
            $photos = $images["photos"];
        }
        else
        {
            $photos = [];
        }

        $htobj = new HuatiModel();
        $comment =  $htobj->getComment($id,"a");
        $arra = [
            "id"=>$articles["id"],
            "title"=>$articles["post_title"],
            "excerpt"=> $articles["post_excerpt"],
            "content"=> htmlspecialchars_decode($articles["post_content"]),
            "image"=> $images["thumbnail"],
            "photos"=> $photos,
            "comment"=> $comment,
            "dianzan" => $articles["zan"],
            "zhuanfa" => $articles["zhuan"]
        ];
        return $arra;
    }

    //读取文章分类列表
    public function getCategory($id,$m)
    {
        $where = [
            'delete_time' => 0,
            'parent_id' => $id
        ];

        $field = 'id,name,description,more';

        $portalPostModel = Db::name("portal_category");
        $categary        = $portalPostModel->field($field)
            ->where($where)
            ->limit($m)
            ->order('id', 'ASC')
            ->select();
        $arrs = [];
        foreach($categary as $key => $vo)
        {
            $morepic = json_decode($vo["more"],true);
            $array = [];
            $array["id"] = $vo["id"];
            $array["title"] = $vo["name"];
            $array["descr"] = $vo["description"];
            $array["image"] = $morepic["thumbnail"];

            array_push($arrs,$array);
        }
        return $arrs;
    }

    public function updateWeixin($status,$id,$data){

        if(isset($id))
        {
            $where['wid'] =  $id;
            $weixin = Db::name("status")->where($where)->count();
            if($weixin>0){
                $result        = Db::name("status")->where($where)->update($data);
            }
            else
            {
                $data["wid"] = $id;
                $result        = Db::name("status")->insert($data);
            }
            $wxstatus = Db::name("weixin")->where(['id' => $id])->update(["status"=>$status]);
        }
        else
        {
            $result=0;
        }
        return $weixin;
    }

    public function uploadPics($root,$dir,$file){
        $uploadstr = [];
        if ($file["error"] > 0)
        {
            $uploadstr[0] = "Error: " . $file["error"] . "<br>";//输出文件上传错误提示
        }
        else
        {
            $uploadstr[0] = $file["name"] . "<br>"; //获取上传的文件名称
            $uploadstr[1] =  $file["type"] . "<br>"; //获取上传的文件类型
            $uploadstr[2] =  ($file["size"] / 1024) . " Kb"; //获取上传的文件大小
        }

        if(is_uploaded_file($file['tmp_name'])) {
            $uploaded_file = $file['tmp_name'];
            $dirname = "/".date("Ymd",time());
            $user_path = $_SERVER['DOCUMENT_ROOT']."/upload/".$dir.$dirname;
            //判断该用户文件夹是否已经有这个文件夹
            if(!file_exists($user_path)) {
                mkdir($user_path);
            }
            $file_true_name = $file['name'];
            $filename = date("Ymd").rand(1,1000000).substr($file_true_name,strrpos($file_true_name,"."));

            $move_to_file = $user_path."/".$filename;
            if(move_uploaded_file($uploaded_file,iconv("utf-8","gb2312",$move_to_file))) {
                $uploadimgname = $root.$dir.$dirname."/".$filename;
                $saveDir = $dir.$dirname."/".$filename;
            } else {
                $uploadimgname = "";
                $saveDir = "";
            }
        } else {
            $uploadimgname = "";
            $saveDir = "";
        }
       return ["url"=>$uploadimgname,"dirurl"=>$saveDir];
    }

    public function getBloglist($uid,$m,$p){

        $arrs = [];
        $where = [
            'a.status' => 1,
        ];
        if(!empty($uid))
        {
            $where["a.uid"] = $uid;
        }
        $field = "a.*,b.title,b.styletext,b.remark,b.editimg,b.contant,b.copyright";
        $join = [
            ["__USER_THEME__ b","a.theme=b.id"]
        ];

        $count = Db::name("blog")->alias("a")->join($join)->field($field)->where($where)->count();
        if($count<1){
            $wheredo = ['a.status' => 1];
        }
        else
        {
            $wheredo = $where;
        }

        $pnum = ceil($count/$m);  //向上取整，计算页数
        if($p > $pnum){
            $p = 1;
        }
        if($p<=0)
        {
            $p  = 1;
        }
        $start = $m*($p-1);

        $result = Db::name("blog")
            ->alias("a")
            ->join($join)
            ->field($field)
            ->where($where)
            ->limit($start,$m)
            ->order("a.id","desc")
            ->select();
        $i = 0;
        foreach($result as $key => $vo)
        {
            $array = [];
            $array["id"] = $vo["id"];
            $array["uid"] = $vo["uid"];
            $array["footer"] = $vo["footer"];
            $array["textarea"] = $vo["textarea"];
            $array["piclist"] = htmlspecialchars_decode($vo["piclist"]);
            $array["photos"] = htmlspecialchars_decode($vo["photos"]);
            $array["theme"] = $vo["theme"];
            $array["zan"] = $vo["zan"];
            $array["zhuan"] = $vo["zhuan"];
            $array["weather"] = $vo["weather"];
            $array["editimg"] = $vo["editimg"];
            $array["styletext"] = htmlspecialchars_decode($vo["styletext"]);
            $arrs["data".$i] = $array;
            $i++;
        }
        return ["count"=>$count,"datas"=>$arrs];
    }

    //获取图片博客日记详情
    public function getBlogDetails($wxid,$id)
    {
        $where = ["a.id"=>$id];
        //$theme_cate = config("cmf_themes_cate");
        $field = 'a.*,b.icon,b.nickname,b.level,b.status,b.hiden,c.styletext,c.editimg';
        $join = [
            ["__WEIXIN__ b","a.uid = b.id"],
            ["__USER_THEME__ c","a.theme = c.id"]
        ];
        $banner = Db::name("blog");
        $vo  = $banner->alias("a")
            ->field($field)
            ->where($where)
            ->join($join)
            ->order('a.id', 'DESC')
            ->find();

        $array = [];
        $uid = $vo["uid"];
        $array["id"] = $vo["id"];
        $array["uid"] = $vo["uid"];
        $array["footer"] = $vo["footer"];
        $array["textarea"] = $vo["textarea"];
        $array["piclist"] = htmlspecialchars_decode($vo["piclist"]);
        $array["photos"] = htmlspecialchars_decode($vo["photos"]);
        $array["theme"] = $vo["theme"];
        $array["weather"] = $vo["weather"];
        $array["styletext"] = htmlspecialchars_decode($vo["styletext"]);
        $array["editimg"] = $vo["editimg"];
        $array["nickname"] = $vo["nickname"];
        $array["avatar"] = $vo["icon"];
        $array["lv"] = $vo["level"];
        $array["status"] = getYuStatus($vo["status"]);
        $array["hiden"] = $vo["hiden"];
        $array["gzor"] = getGuanzhuOr($wxid,$uid);

        $gzobj = new HuatiModel();
        $array["myguanzhu"] = $gzobj->getGuanzhu($uid,0);
        $array["fans"] = $gzobj->getGuanzhu($uid,1);
        $array["zan"] = $vo["zan"];
        $array["zhuan"] = $vo["zhuan"];

        return $array;
    }
}