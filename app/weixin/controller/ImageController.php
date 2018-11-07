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

use cmf\lib\Storage;
use cmf\controller\HomeBaseController;
use think\Image;
use think\Db;
use think\Request;
use app\admin\model\ThemesModel;
use think\File;
use app\weixin\model\WeixinModel;
use think\Loader;
use Dompdf\Dompdf;

class ImageController extends HomeBaseController
{
    public function  save_image(){
        $request = Request::instance();
        $data   = $this->request->param();
        $id = $data["id"];

        $id = $data['id'];
        $showtheme   = Db::name("user_theme")->where('id',$id)->find();

        if($showtheme) {
            $remark = $showtheme["remark"];  //段落文字内容
            $copyright = $showtheme["copyright"];  //落款文字
            $piclist = json_decode($showtheme["pic_url"]); //图片列表
            $contant = json_decode($showtheme["contant"]);  //日期天气等
            $shapes = $showtheme["shapes"];  //图片形状
            $editimg = $showtheme["editimg"];  //是否编辑

            $array = $this->getContant($contant);

            $styletext = htmlspecialchars_decode($showtheme["styletext"]);
            $styletext = str_replace("rpx", "px", $styletext);
            $styletext = str_replace("margin-", "", $styletext);
            $styletext = str_replace("padding-", "", $styletext);
            $styletext = str_replace("-", "_", $styletext);
            $styletext = json_decode($styletext, true);

            $dataArray = [
                "imagelist" => $piclist,
                "contant" => $array,
                "styletext" => $styletext,
                "remark" => $remark,
                "copyright" => $copyright,
                "shapes" => $shapes
            ];

            $titleWhxy = $dataArray["styletext"]["title"];
            $imageWhxy = $dataArray["styletext"]["container_box"];
            $textWhxy = $dataArray["styletext"]["container_p"];
            $bottomWhxy = $dataArray["styletext"]["container_bottom"];
            $container_z = $dataArray["styletext"]["container_z"];

            $titleArray = $this->getTextWHTL($titleWhxy); //日期天气宽度高度
            $textArray = $this->getTextWHTL($textWhxy);     //段落文字的宽度高度
            $bottomArray = $this->getTextWHTL($bottomWhxy);  //落款宽度高度
            $imageArray = $this->getImageWHTL($imageWhxy, $shapes);  //图片宽高和坐标和形状
            $container = $this->getTextWHTL($container_z);  //图片宽高和坐标和形状

            $imageData = [
                "id" => $id,
                "imagelist" => $piclist,
                "contant" => $array,
                "remark" => $remark,
                "copyright" => $copyright,
                "titleArray" => $titleArray,
                "textArray" => $textArray,
                "bottomArray" => $bottomArray,
                "imageArray" => $imageArray,
                "shapes" => $shapes,
                "editimg" => $editimg,
                "container" => $container
            ];
        }
        else
        {
            $imageData = 0;
        }
        return json($imageData);
    }


    public function getdompdf(){
        //vendor('dompdf.src.Dompdf','','.php');
        //Loader::import('Dompdf\Dompdf', EXTEND_PATH,'.php');
        $html = "<html><head><title>测试dompdf</title></head><body><h1>这是一个标题！</h1></body></html>";
        $Dompdf=new Dompdf();
        $Dompdf->loadHtmlFile($html);;
        $Dompdf->render();
        $Dompdf->stream("sample.pdf", array("Attachment"=>0));
        exit;
    }


    public function  made_image(){
        $request = Request::instance();
        $data   = $this->request->param();
        $id = $data["id"];
        $where["a.status"] = 1;
        $where["b.id"] = $id;
        $id = $data['id'];
        $join = [
            ["__BLOG__ b","a.id=b.theme"]
        ];
        $field = "a.id,a.remark,a.copyright,a.contant,a.shapes,a.editimg,a.styletext,b.uid,b.footer,b.textarea,b.piclist,b.photos,b.theme,b.weather";
        $showtheme   = Db::name("user_theme")->alias("a")->join($join)->field($field)->where($where)->find();

        if($showtheme) {
            if($showtheme["textarea"])
            {
                $remark = $showtheme["textarea"];  //段落文字内容
            }
            else
            {
                $remark = $showtheme["remark"];  //段落文字内容
            }
            if($showtheme["footer"])
            {
                $copyright = $showtheme["footer"];  //落款文字
            }
            else
            {
                $copyright = $showtheme["copyright"];  //落款文字
            }
           $piclist = htmlspecialchars_decode($showtheme["piclist"]); //图片列表
           $photos = htmlspecialchars_decode($showtheme["photos"]);

            $contant = $showtheme["weather"];  //日期天气等
            $shapes = $showtheme["shapes"];  //图片形状
            $editimg = $showtheme["editimg"];  //是否编辑

            //$array = $this->getContant($contant);

            $styletext = htmlspecialchars_decode($showtheme["styletext"]);
            $styletext = str_replace("rpx", "px", $styletext);
            $styletext = str_replace("margin-", "", $styletext);
            $styletext = str_replace("padding-", "", $styletext);
            $styletext = str_replace("-", "_", $styletext);
            $styletext = json_decode($styletext, true);

            $dataArray = [
                "imagelist" => $piclist,
                "contant" => $contant,
                "styletext" => $styletext,
                "remark" => $remark,
                "copyright" => $copyright,
                "shapes" => $shapes,
                "photos" => $photos
            ];

            $titleWhxy = $dataArray["styletext"]["title"];
            $imageWhxy = $dataArray["styletext"]["container_box"];
            $textWhxy = $dataArray["styletext"]["container_p"];
            $bottomWhxy = $dataArray["styletext"]["container_bottom"];
            $container_z = $dataArray["styletext"]["container_z"];

            $titleArray = $this->getTextWHTL($titleWhxy); //日期天气宽度高度
            $textArray = $this->getTextWHTL($textWhxy);     //段落文字的宽度高度
            $bottomArray = $this->getTextWHTL($bottomWhxy);  //落款宽度高度
            $imageArray = $this->getImageWHTL($imageWhxy, $shapes);  //图片宽高和坐标和形状
            $container = $this->getTextWHTL($container_z);  //图片宽高和坐标和形状

            $imageData = [
                "id" => $id,
                "imagelist" => $piclist,
                "contant" => $contant,
                "remark" => $remark,
                "copyright" => $copyright,
                "titleArray" => $titleArray,
                "textArray" => $textArray,
                "bottomArray" => $bottomArray,
                "imageArray" => $imageArray,
                "shapes" => $shapes,
                "editimg" => $editimg,
                "container" => $container,
                "photos" => $photos
            ];
        }
        else
        {
            $imageData = 0;
        }
        return json($imageData);
    }

    public function getImageWHTL($style,$shapes){
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

    public function getTextWHTL($style){

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

    public function getContant($contant){
        $array = [];
        $lunarobj = new ThemesModel();
        $tm = time();
        $year = date("Y",$tm);
        $mouth = date("m",$tm);
        $day = date("d",$tm);
        $nonglis = $lunarobj->getLunar($year,$mouth,$day);

        if (in_array("1", $contant)) {
            $array["datetime"] = date("Y年m月d日",$tm);
        }
        if (in_array("2", $contant)) {
            $array["nongli"] = "农历：".$nonglis[1].$nonglis[2];
        }
        if (in_array("3", $contant)) {
            $weekarray = array("日", "一", "二", "三", "四", "五", "六");
            $array["week"] = "星期" . $weekarray[date("N", time())];
        }
        if (in_array("4", $contant)) {
            $weather = getWeatherXml();
            $weather = xmlToArray($weather);
            $array["weather"] = $weather["city"][10]["@attributes"]["stateDetailed"];
        }
        return $array;
    }

}