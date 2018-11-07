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

use cmf\controller\HomeBaseController;
use think\Db;
use app\weixin\model\IndexModel;

class IndexController extends HomeBaseController
{
    public function index()
    {
        return $this->fetch(":index");
    }

    //首页banner图片
    public function getBanner(){
        $param = $this->request->param();
        $id = $this->request->param('id', 1, 'intval');
        $picobj = new IndexModel();
        $banners = $picobj->getBanner(1,3); //首页banner
        $login = $picobj->getBanner(2,1); //登录图片

        return json(array('banners'=>$banners,'login'=>$login[0]['image']));
    }

    public function getLists(){
        $param = $this->request->param();
        $id = $this->request->param('id', 1, 'intval');
        $page = $this->request->param('page', 1, 'intval');
        $limit = $this->request->param('limit', 5, 'intval');

        if(!empty($param['keyword']))
        {
            $keyword = $param['keyword'];
            $where["post_title"] = ['like', "%$keyword%"];
        }
        else
        {
            $where = [
                'category_id' => $id,
                'post_status' => 1
            ];
        }
        $join = [
            ['__PORTAL_CATEGORY_POST__ b', 'a.id = b.post_id']
        ];

        $field = 'a.post_title,a.post_excerpt,a.more,a.id as article_id';
        $portalPostModel = Db::name("portal_post");

        $count = $portalPostModel->alias('a')->join($join)->where($where)->count();

        $pnum = ceil($count/$limit);  //向上取整，计算页数
        if($page > $pnum){
            $page = 1;
        }
        if($page<=0)
        {
            $page  = 1;
        }
        $start = $limit*($page-1);

        $articles        = $portalPostModel->alias('a')->join($join)->field($field)
            ->where($where)
            ->order('update_time', 'DESC')
            ->limit($start,$limit)
            ->select();
        $arrs = [];
        foreach($articles as $vo)
        {
            $subarr = [];
            $imglist = json_decode($vo["more"],true);
            $subarr["id"] = $vo["article_id"];
            $subarr["title"] = msubstr($vo["post_title"],0,12,'utf-8',true);
            $subarr["excerpt"] = msubstr($vo["post_excerpt"],0,30,'utf-8',true);
            $subarr["thumb"] = $imglist["thumbnail"];
            array_push($arrs,$subarr);
        }
        return json(array('list'=>$arrs,'count'=>$count));
    }

    public function getArticle(){
        $param = $this->request->param();
        $id = $this->request->param('id', 12, 'intval');
        $where = [
            'id' => $id
        ];

        Db::name('portal_post')->where('id', $id)->setInc('post_hits');

        $field = 'id,post_title,post_excerpt,create_time,post_content,post_hits,more';
        $portalPostModel = Db::name("portal_post");
        $articles    = $portalPostModel
            ->field($field)
            ->where($where)
            ->order('update_time', 'DESC')
            ->find();
        $images = json_decode($articles["more"],true);
        $content = htmlspecialchars_decode($articles["post_content"]);
        $content = str_replace('src="/public/upload/','src="https://'.$_SERVER['SERVER_NAME'].'/public/upload/',$content);
        $arra = [
            "id"=>$articles["id"],
            "title"=>$articles["post_title"],
            "excerpt"=> $articles["post_excerpt"],
            "content"=> $content,
            "image"=> $images["thumbnail"],
            "hist"=>$articles["post_hits"],
            "tm"=>date("n月j日",$articles["create_time"])
        ];
        return json($arra);
    }

    public function getPage(){
        $param = $this->request->param();
        $id = $this->request->param('id', 10, 'intval');
        $where = [
            'id' => $id
        ];
        $field = 'id,post_title,post_excerpt,create_time,post_content,post_hits,more';
        $portalPostModel = Db::name("portal_post");
        $articles    = $portalPostModel
            ->field($field)
            ->where($where)
            ->order('id', 'DESC')
            ->find();
        $images = json_decode($articles["more"],true);
        $content = htmlspecialchars_decode($articles["post_content"]);
        $content = str_replace('src="/public/upload/','src="https://'.$_SERVER['SERVER_NAME'].'/public/upload/',$content);
        $arra = [
            "id"=>$articles["id"],
            "title"=>$articles["post_title"],
            "excerpt"=> $articles["post_excerpt"],
            "content"=> $content,
            "image"=> $images["thumbnail"],
            "hist"=>$articles["post_hits"],
            "tm"=>date("n月j日",$articles["create_time"])
        ];
        return json($arra);
    }

    public function getLastTime(){
        $field = 'create_time';
        $portalPostModel = Db::name("portal_post");
        $articles    = $portalPostModel
            ->field($field)
            ->order('create_time', 'DESC')
            ->find();
        return $articles['create_time'];
    }
}