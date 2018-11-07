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

namespace app\admin\controller;

use cmf\controller\AdminBaseController;
use think\Db;
use app\admin\Model\ExcelModel;

/**
 * Class AdminIndexController
 * @package app\user\controller
 *
 * @adminMenuRoot(
 *     'name'   =>'用户管理',
 *     'action' =>'default',
 *     'parent' =>'',
 *     'display'=> true,
 *     'order'  => 10,
 *     'icon'   =>'group',
 *     'remark' =>'用户管理'
 * )
 *
 * @adminMenuRoot(
 *     'name'   =>'用户组',
 *     'action' =>'default1',
 *     'parent' =>'user/AdminIndex/default',
 *     'display'=> true,
 *     'order'  => 10000,
 *     'icon'   =>'',
 *     'remark' =>'用户组'
 * )
 */
class MemberController extends AdminBaseController
{

    /**
     * 后台本站用户列表
     * @adminMenu(
     *     'name'   => '本站用户',
     *     'parent' => 'default1',
     *     'display'=> true,
     *     'hasView'=> true,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '本站用户',
     *     'param'  => ''
     * )
     */
    public function index()
    {
        $where   = ["user_status"=>1];
        $request = input('request.');
        if (!empty($request['uid'])) {
            $where['id'] = intval($request['uid']);
        }
        $keywordComplex = [];
        if (!empty($request['keyword'])) {
            $keyword = $request['keyword'];
            $keywordComplex['user_com|user_name|user_email|mobile']    = ['like', "%$keyword%"];
        }
        $usersQuery = Db::name('member');
        $list = $usersQuery->whereOr($keywordComplex)->where($where)->order("id DESC")->paginate(10);
        // 获取分页显示
        $page = $list->render();
        $this->assign('list', $list);
        $this->assign('page', $page);
        // 渲染模板输出
        return $this->fetch();
    }


    public function add()
    {
        return $this->fetch();
    }


    public function addPost()
    {
        if ($this->request->isPost()) {
                $result = $this->validate($this->request->param(), 'Member');
                if ($result !== true) {
                    $this->error($result);
                } else {
                    $result    = DB::name('Member')->insertGetId($_POST);
                   $this->success("添加成功！", url("member/index"));
                }
        }
    }

    public function edit()
    {
        $id    = $this->request->param('id', 0, 'intval');
        $member = DB::name('member')->where(["id" => $id])->find();
        $this->assign($member);
        return $this->fetch();
    }


    public function editPost()
    {
        if ($this->request->isPost()) {
                $result = $this->validate($this->request->param(), 'Member.edit');

                if ($result !== true) {
                    // 验证失败 输出错误信息
                    $this->error($result);
                } else {
                    $result = DB::name('member')->update($_POST);
                    if ($result !== false) {
                        $this->success("保存成功！");
                    } else {
                        $this->error("保存失败！");
                    }
                }
        }
    }
    /**
     * 本站用户拉黑
     * @adminMenu(
     *     'name'   => '本站用户拉黑',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '本站用户拉黑',
     *     'param'  => ''
     * )
     */
    public function del()
    {
        $id = input('param.id', 0, 'intval');
        if ($id) {
            $result = Db::name("member")->where(["id" => $id])->setField('user_status', 0);
            if ($result) {
                $this->success("客户删除成功！", "member/index");
            } else {
                $this->error('客户删除失败,会员不存在或参数错误！');
            }
        } else {
            $this->error('数据传入失败！');
        }
    }
    //php原生方式导出excel
    public function toExcel(){
        //vendor('phpexcel.PHPExcel');
        $objPHPExcel = new ExcelModel();
        $where["user_status"] = 1;
        $i = 0;
        $title = "客户管理";
        $dtime = date("Ymdhis",time());
        $field = [1=>'ID',2=>'公司名称',3=>'用户名称',4=>'Email',5=>'手机号'];
        $result     = Db::name("member")->where($where)->select();// 查询满足要求的总记录数
        if($result){
            $data = [];
            foreach($result as $vo){
                $temp = [];
                $temp["A"] = $vo["id"];
                $temp["B"] = $vo["user_name"];
                $temp["C"] = $vo["user_com"];
                $temp["D"] = $vo["user_email"];
                $temp["E"] = "\t".$vo["mobile"];
                array_push($data,$temp);
            }
            $objPHPExcel->exportToExcel($title.$dtime.'.csv',$field,$data);
            exit();
            //$this->success("导出Excel成功！", "member/index");
        }
    }
//PHPExcel方式导出excel
    public function getExcel(){
        //vendor('phpexcel.PHPExcel');
        import('PHPExcel', EXTEND_PATH);
        $objPHPExcel = new \PHPExcel();
        $title = "客户管理";
        $xlsTitle = iconv('utf-8', 'gb2312', $title);//文件名称
        $fileName = $xlsTitle.date('_YmdHis');//文件名称
        //$field = ['A1'=>'ID','B1'=>'公司名称','C1'=>'用户名称','D1'=>'Email','E1'=>'手机号'];

        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('A1', 'ID');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('B1', '公司名称');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('C1', '用户名称');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('D1', 'Email');
        $objPHPExcel->setActiveSheetIndex(0)->setCellValue('E1', '手机号');

        $objPHPExcel->getActiveSheet()->settitle('sheet' . date('Ymd'));
        $objPHPExcel->setActiveSheetIndex(0);
        $objPHPExcel->getActiveSheet()->getDefaultRowDimension()->setRowHeight(15);
        $objPHPExcel->getActiveSheet()->freezePane('A2');
        //处理数据
        $where["user_status"] = 1;
        $result     = Db::name("member")->where($where)->select();// 查询满足要求的总记录数
        $data = [];
        $i = 2;
        if($result) {
            foreach ($result as $vo) {
                $temp = [];
                $temp["A"] = $vo["id"];
                $temp["B"] = $vo["user_name"];
                $temp["C"] = $vo["user_com"];
                $temp["D"] = $vo["user_email"];
                $temp["E"] = "\t" . $vo["mobile"];
                array_push($data, $temp);
                $objPHPExcel->getActiveSheet()->setCellValue('A' . $i, $temp["A"]);
                $objPHPExcel->getActiveSheet()->setCellValue('B' . $i, $temp["B"]);
                $objPHPExcel->getActiveSheet()->setCellValue('C' . $i, $temp["C"]);
                $objPHPExcel->getActiveSheet()->setCellValue('D'. $i, $temp["D"]);
                $objPHPExcel->getActiveSheet()->setCellValue('E' . $i, $temp["E"]);
                $i++;
            }
        }
        $objActSheet = $objPHPExcel->getActiveSheet();
        $objActSheet->getColumnDimension('A')->setWidth(18.5);
        $objActSheet->getColumnDimension('B')->setWidth(23.5);
        $objActSheet->getColumnDimension('C')->setWidth(12);
        $objActSheet->getColumnDimension('D')->setWidth(12);
        $objActSheet->getColumnDimension('E')->setWidth(12);
        //导出execl
        ob_end_clean();//清除缓冲区,避免乱码
        ob_start();
        header('Content-type:application/vnd.ms-excel;');
        header('Content-Disposition: attachment;filename="'.$fileName.'.xlsx"');
        header('Cache-Control: max-age=0');
// If you're serving to IE 9, then the following may be needed
        header('Cache-Control: max-age=1');
// If you're serving to IE over SSL, then the following may be needed
        header('Expires: Mon, 26 Jul 1997 05:00:00 GMT'); // Date in the past
        header('Last-Modified: ' . gmdate('D, d M Y H:i:s') . ' GMT'); // always modified
        header('Cache-Control: cache, must-revalidate'); // HTTP/1.1
        header('Pragma: public'); // HTTP/1.0
        $objWriter = \PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output');
        exit;
    }

    /**
     * 本站用户启用
     * @adminMenu(
     *     'name'   => '本站用户启用',
     *     'parent' => 'index',
     *     'display'=> false,
     *     'hasView'=> false,
     *     'order'  => 10000,
     *     'icon'   => '',
     *     'remark' => '本站用户启用',
     *     'param'  => ''
     * )
     */
    public function cancelBan()
    {
        $id = input('param.id', 0, 'intval');
        if ($id) {
            Db::name("member")->where(["id" => $id, "user_type" => 2])->setField('user_status', 1);
            $this->success("会员启用成功！", '');
        } else {
            $this->error('数据传入失败！');
        }
    }
}
