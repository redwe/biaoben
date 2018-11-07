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

class IndexModel extends Model
{
    //获取banner图片
    public function getBanner($id,$m)
    {
        $where = [
            'slide_id' => $id
        ];
        $field = 'id,image,title,url';
        $banner = Db::name("slide_item");
        $lists    = $banner
            ->field($field)
            ->where($where)
            ->limit($m)
            ->order('id', 'DESC')
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
}