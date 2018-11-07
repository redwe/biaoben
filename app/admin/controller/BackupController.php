<?php
// +----------------------------------------------------------------------
// | ThinkCMF [ WE CAN DO IT MORE SIMPLE ]
// +----------------------------------------------------------------------
// | Copyright (c) 2013-2018 http://www.thinkcmf.com All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: 小夏 < 449134904@qq.com>
// +----------------------------------------------------------------------
namespace app\admin\controller;

use cmf\controller\AdminBaseController;
use think\Db;

class BackupController extends AdminBaseController
{

    public function _initialize()
    {
        parent::_initialize();
    }

    /**
     *  后台欢迎页
     */
    public function index()
    {
        $root = $_SERVER['DOCUMENT_ROOT'];
        $file_name = $root.'/data/backup/';
        $files = scandir($file_name);
        $i = 0;
        $temp_file = [];
        foreach($files as $f){
            $arr_file = [];
            $ftime = filectime($file_name.$f);
            $arr_file = ["fname"=>$f,"ftime"=>$ftime];
            if($i>1) {
                array_push($temp_file, $arr_file);
            }
            $i++;
        }
        $config = $this->getdbobj();
        import('Backup', EXTEND_PATH);
        $dbback= new \Backup($config);
        $dblist = $dbback->dataList();
        //dump($list);
        $this->assign('dblist', $dblist);
        $this->assign('files', $temp_file);
        return $this->fetch();
    }

    public function getdbobj(){
        $root = $_SERVER['DOCUMENT_ROOT'];
        $path = '/data/backup/';
        $config=array(
            'path'     => $root.$path,//数据库备份路径
            'part'     => 20971520,//数据库备份卷大小
            'compress' => 0,//数据库备份文件是否启用压缩 0不压缩 1 压缩
            'level'    => 9 //数据库备份文件压缩级别 1普通 4 一般  9最高
        );
        return $config;
    }

    public function dblist(){
        $config = $this->getdbobj();
        import('Backup', EXTEND_PATH);
        $dbback= new \Backup($config);
        //$file=['name'=>date('Ymd-His'),'part'=>1];
        $dblist = $dbback->dataList();
        //dump($list);
        $this->assign('dblist', $dblist);
        return $this->fetch();
    }

    public function backtable(){

        $tablename  = $this->request->param('tablename');
        $config = $this->getdbobj();
        import('Backup', EXTEND_PATH);
        $dbback= new \Backup($config);
        $dbback->backup($tablename, 0, $tablename);
        $this->success("数据库备份成功！");
    }

    public function import(){

        $tablename  = $this->request->param('url');
        $config = $this->getdbobj();
        import('Backup', EXTEND_PATH);
        $dbback= new \Backup($config);
        //echo $config["path"].$tablename;
        $dbback->import($config["path"].$tablename, 0);
        $this->success("数据库恢复成功！");
    }

    public function backups()
    {
        //1.获取数据库信息
        $info = config();
        $dbname = $info['database'];

        $hostname = $dbname["hostname"];
        $database = $dbname["database"];   //获取当前数据库
        $username = $dbname["username"];
        $password = $dbname["password"];

        //2.获取数据库所有表
        $tables = Db::query("show tables");
        //3、组装头部信息
        header("Content-type:text/html;charset=utf-8");
        $path  = $this->request->param('dirname');
        if(empty($path)){
            $path = ROOT_PATH.'/data/backup/';
        }
        $root = $_SERVER['DOCUMENT_ROOT'];
        $filedir = $root.$path;
        //$file_name = $root.$path.date('Ymd_His').'.sql';
        //dump($dbname);

        //$host = 'localhost', $username = 'root', $password = '', $database = 'ltcs', $charset = 'utf8'
        import('Dbback', EXTEND_PATH);
        $backups = new \DBbackup($hostname,$username,$password,$database);
        //dump($backups);die();
        $backups -> backup('',$filedir,'');
 /*
        $info .= "-- 日期：".date("Y-m-d H:i:s",time())."\r\n";
        $info .= "-- MySQL - 5.5.52-MariaDB : Database - ".$database."\r\n";
        $info .= "SET NAMES utf8;\r\nSET FOREIGN_KEY_CHECKS = 0;\r\n\r\n";
        //4、检查目录是否存在
        if (is_dir($path)) {
            if (is_writable($path)) {
            } else {
                echo '目录不可写'; exit();
            }
        } else {
            mkdir($path,0777,true);
        }

        //5、保存的文件名称

        /*
        file_put_contents($file_name, $info, FILE_APPEND);
        //6、循环表，写入数据
        foreach ($tables as $k => $v) {
            $val = $v["Tables_in_$database"];
            $sql = "SELECT * FROM INFORMATION_SCHEMA.COLUMNS WHERE TABLE_NAME='$val' AND TABLE_SCHEMA='$database'";
            $res = Db::query($sql);
            $max_num = Db::table("$val")->order('id desc')->value('id');
            //查询表结构
            $info_table .= "-- Table structure for `$val`\r\n";
            $info_table .= "DROP TABLE IF EXISTS `$val`;\r\n";
            if (count($res) < 1) {
                continue;
            }
            $info_table .= "CREATE TABLE `$val` (\n\r\t";
            foreach ($res as $kk => $vv) {
                $info_table .= " `".$vv['COLUMN_NAME']."` ";
                $info_table .= $vv['COLUMN_TYPE'];
                //是否允许空值
                if ($vv['IS_NULLABLE'] == 'NO') {
                    $info_table .= " NOT NULL ";
                }
                //判断主键
                if ($vv['EXTRA']) {
                    $info_table .= " AUTO_INCREMENT ";
                    $key = $vv['COLUMN_NAME'];
                }
                //编码
                if ($vv['CHARACTER_SET_NAME']) {
                    $info_table .= " CHARACTER SET ".$vv['CHARACTER_SET_NAME'];
                }
                //字符集
                if ($vv['COLLATION_NAME']) {
                    $info_table .= " COLLATE ".$vv['COLLATION_NAME'];
                }
                //默认数值
                if ($vv['COLUMN_DEFAULT']) {
                    $info_table .= " DEFAULT ".$vv['COLUMN_DEFAULT'];
                }
                //注释
                if ($vv['COLUMN_COMMENT']) {
                    $info_table .= " COMMENT '".$vv['COLUMN_COMMENT']."',\n\r\t";
                }
            }
            $info_table .= " PRIMARY KEY (`$key`) USING BTREE";
            $info_table .= "\n\r) ENGINE = MyISAM AUTO_INCREMENT $max_num CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;\r\n\r\n";

            //查询表数据
            $info_table .= "-- Data for the table `$val`\r\n";
            file_put_contents($file_name,$info_table,FILE_APPEND);
            $sql_data = "select * from $val";
            $data = Db::query($sql_data);
            $count= count($data);
            if ($count < 1) {
                continue;
            }
            foreach ($data as $key => $value) {
                $sqlStr = "INSERT INTO `$val` VALUES (";
                foreach($value as $v_d){
                    $v_d = str_replace("'","\'",$v_d);
                    $sqlStr .= "'".$v_d."', ";
                }
                //需要特别注意对数据的单引号进行转义处理
                //去掉最后一个逗号和空格
                $sqlStr = substr($sqlStr,0,strlen($sqlStr)-2);
                $sqlStr .= ");\r\n";
                file_put_contents($file_name,$sqlStr,FILE_APPEND);
            }
            $info = "\r\n";
            file_put_contents($file_name,$info,FILE_APPEND);

        }
*/
        //$this->success("数据库备份成功！");
        //redirect($_SERVER['HTTP_REFERER']);//看业务逻辑  这里是如果文件不存在  避免调到空白页面
    }


    public function recovery(){

        $info = config();
        $dbname = $info['database'];

        $hostname = $dbname["hostname"];
        $database = $dbname["database"];   //获取当前数据库
        $username = $dbname["username"];
        $password = $dbname["password"];

        import('Dbback', EXTEND_PATH);
        $backups = new \DBbackup($hostname,$username,$password,$database);
        //dump($backups);die();

        $root = $_SERVER['DOCUMENT_ROOT'];
        $path  = $this->request->param('url');
        $dir = $root.'/data/backup/'.$path;

        // 以下开始数据还原操作
        //$dir= dirname(dirname(dirname(dirname(__FILE__)))).'\backup\\'.$_POST['restore'];
        //dump($dir);die();
        //$backups -> restore($dir);
        /*
        $arr = file($file_name);
        // 移除注释
        $sql_str = array_filter($arr,'remove_comment');
        $sql_str = str_replace("\r", "",implode('',$sql_str));
        $ret = explode(";\n", $sql_str);
        foreach($ret as $val)
        {
            $val = trim($val, " \r\n;");
            Db::query($val);
        }
        */
       $this->success("数据恢复成功！");
    }

    public function delete(){
        $root = $_SERVER['DOCUMENT_ROOT'];
        $path  = $this->request->param('url');
        $file_name = $root.'/data/backup/'.$path;
        unlink($file_name);
        $this->success("文件删除成功！");
    }

    public function download(){
        //7、下载数据到本地
        $root = $_SERVER['DOCUMENT_ROOT'];
        $path  = $this->request->param('url');
        $file_name = $root.'/data/backup/'.$path;
        //echo $file_name;
        /*
        ob_end_clean();
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Length: ' . filesize($file_name));
        header('Content-Disposition: attachment; filename=' . basename($file_name));
        readfile($file_name);
        DownloadFile($file_name);
*/
        $this->download_file($file_name);
        $this->success("数据下载成功！");
        redirect($_SERVER['HTTP_REFERER']);//看业务逻辑  这里是如果文件不存在  避免调到空白页面
    }

    function download_file($filePath, $downloadFileName = null) {
        if (file_exists($filePath)) {
            $downloadFileName = $downloadFileName !== null ? $downloadFileName : basename($filePath);
            header('Content-Description: File Transfer');
            header('Content-Type: application/octet-stream');
            header('Content-Disposition: attachment; filename=' . $downloadFileName);
            header('Content-Transfer-Encoding: binary');
            header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            header('Content-Length: ' . filesize($filePath));

            ob_clean();//清空php的缓冲区
            flush();//清空web服务器的缓存区php的缓冲区超过限制php脚本还没有结束就会输出到服务器的缓存区或者浏览器的缓冲区  清除他？
            readfile($filePath);
            exit;
        }else{
            redirect($_SERVER['HTTP_REFERER']);//看业务逻辑  这里是如果文件不存在  避免调到空白页面
        }
    }
}
