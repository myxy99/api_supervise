<?php

namespace App\Http\Controllers\ProjectAdmin;

use App\Model\Error;
use App\Model\InterfaceTable;
use App\Model\Project;
use App\Model\ProjectModule;
use App\Model\RequestTable;
use App\Model\ResponseTable;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use PhpOffice\PhpWord\PhpWord;

class WordController extends Controller
{
    public function getWord()
    {
        $project_id = 1;
        $projectMsg = Project::findProjectMsg($project_id);
        $phpword = new PhpWord();
        $section = $phpword->addSection();
        $phpword->addParagraphStyle('pageStyle', array('spaceAfter' => 240));
        $paragraphStyleName = 'pStyle';
        $phpword->addParagraphStyle('pStyle', array('spacing' => 100));
        $fontStyle = [
            'name' => '宋体',
            'size' => 12,
            'color' => '#000000',
            'bold' => false,
        ];
        $fontStylebold = [
            'name' => '宋体',
            'size' => 12,
            'color' => '#000000',
            'bold' => true,
        ];
        $oneTitileFontStyle = [
            'name' => '等线',
            'size' => 22,
            'color' => '#000000',
            'bold' => true,
        ];
        $twoTitileFontStyle = [
            'name' => '等线',
            'size' => 16,
            'color' => '#000000',
            'bold' => true,
            'align' => 'left',
        ];
        //应用层标题
        $applicationTitle = "应用返回数据层级";
        //数据层级信息
        $applicationMess = array(
            'data' => '应用数据',
            'msg' => '应用信息',
            'code' => '状态码',
            '200' => '成功',
            '100' => '失败',
            '422' => '参数错误',
            '404、500' => '异常需要跳转异常页面!',
            'ps' => ''
        );
        //错误码表格
        $errorTable = "错误码表格";
        //标题
        foreach ($projectMsg as $projectmsg) {
            $titile = $projectmsg->name;
            $phpword->addTitleStyle(1, $oneTitileFontStyle, array('align' => 'center', 'spaceAfter' => 240));
            $section->addTitle($titile, 1);
            $phpword->addTitleStyle(2, $twoTitileFontStyle, array('align' => 'left', 'spaceAfter' => 240));
            $section->addTitle($applicationTitle, 2);
            $textrun = $section->addTextRun($paragraphStyleName);
            foreach ($applicationMess as $key => $vale) {
                $textrun->addText("  " . $key . ':' . $vale, $fontStyle);
                $textrun->addTextBreak();
            }
            $phpword->addTitleStyle(2, $twoTitileFontStyle, array('align' => 'left', 'spaceAfter' => 240));
            $section->addTitle($errorTable, 2);
            $styleTable = array('borderSize' => 6, 'borderColor' => 'black', 'cellMargin' => 80);//表格整体样式
            $phpword->addTableStyle('talentSystem', $styleTable);
            $table = $section->addTable('talentSystem');
            //第一行
            $table->addRow();
            $table->addCell(2000)->addText('', $fontStylebold);
            $table->addCell(2666, array('gridSpan' => 3))->addText($getinfterfaceinfo->interface_name, $fontStyle);
            $textrun = $section->addTextRun($paragraphStyleName);

            //模块部分
            $projectModule = ProjectModule::findModules($project_id);
            foreach ($projectModule as $projectmodule) {
                $section->addTitle($projectmodule->modules_name, 2, array('align' => 'left', 'spaceAfter' => 240));
                $textrun = $section->addTextRun($paragraphStyleName);
                $textrun->addText("♦ 类名：" . $projectmodule->class_name, $fontStyle);
                $textrun->addTextBreak();
                $textrun->addText("♦ 全类名：" . $projectmodule->full_class_name, $fontStyle);
                $textrun->addTextBreak();
                $textrun->addText("♦ 作用：" . $projectmodule->utility, $fontStyle);
                $textrun->addTextBreak();
                $textrun->addText("♦ 类方法：", $fontStyle);
                $modulesAllMet = InterfaceTable::findModulesAllMet($projectmodule->id);
                foreach ($modulesAllMet as $modulesallmet1) {
                    $textrun = $section->addTextRun($paragraphStyleName);
                    $textrun->addText('    public function ' . $modulesallmet1->function_name, $fontStyle);
                }
                    $textrun = $section->addTextRun($paragraphStyleName);
                foreach ($modulesAllMet as $modulesallmet) {
                    $getInfterfaceInfo = InterfaceTable::getInfterfaceInfo($modulesallmet->interface_id);
                    //表格部分
                    foreach ($getInfterfaceInfo as $getinfterfaceinfo) {
                        $textrun = $section->addTextRun($paragraphStyleName);
                        $InterfaceRequestMsg = RequestTable::getInterfaceRequestMsg($getinfterfaceinfo->id);
                        $textrun->addText("♦ " . $getinfterfaceinfo->function_name, $fontStyle, array('spaceAfter' => 240));
                        $styleTable = array('borderSize' => 6, 'borderColor' => 'black', 'cellMargin' => 80);//表格整体样式
                        $phpword->addTableStyle('talentSystem', $styleTable);
                        $table = $section->addTable('talentSystem');
                        //第一行
                        $table->addRow();
                        $table->addCell(2000)->addText('作用', $fontStylebold);
                        $table->addCell(6000, array('gridSpan' => 3))->addText($getinfterfaceinfo->interface_name, $fontStyle);
                        //第二行
                        foreach ($InterfaceRequestMsg as $interfacerequestmsg) {
                            $table->addRow();
                            $table->addCell(2000)->addText("请求方式", $fontStylebold);
                            $table->addCell(1000)->addText($interfacerequestmsg->request_mode, $fontStyle);
                            $table->addCell(1000)->addText("路由", $fontStylebold);
                            $table->addCell(4000)->addText($getinfterfaceinfo->route_path, $fontStyle);
                            //第三行
                            $table->addRow();
                            $table->addCell(2000)->addText("入参参数名", $fontStylebold);
                            $table->addCell(2000, array('gridSpan' => 2))->addText("类型", $fontStylebold);
                            $table->addCell(4000)->addText("说明", $fontStylebold);
                            //第四行
                            $swetchArray = json_decode($interfacerequestmsg->params, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
                            if(is_null($swetchArray['interface_request'])){
                                $table->addRow();
                                $table->addCell(2000)->addText(" ", $fontStylebold);
                                $table->addCell(2000, array('gridSpan' => 2))->addText(" ", $fontStylebold);
                                $table->addCell(4000)->addText(" ", $fontStylebold);
                            }else {
                                foreach ($swetchArray['interface_request'] as $value) {
                                    $table->addRow();
                                    $table->addCell(2000)->addText($value['request_name'], $fontStyle);
                                    $table->addCell(2000, array('gridSpan' => 2))->addText($value['request_type'], $fontStyle);
                                    $table->addCell(4000)->addText($value['request_remark'], $fontStyle);
                                }
                            }
                        }
                        //第五行
                        $InterResposeMsgSucc = ResponseTable::getInterResposeMsg($getinfterfaceinfo->id, 1);
                        $InterResposeMsgFail = ResponseTable::getInterResposeMsg($getinfterfaceinfo->id, 0);
                        foreach ($InterResposeMsgSucc as $interresposemsgsucc) {
                            $table->addRow();
                            $table->addCell(2000)->addText("返回值类型", $fontStylebold);
                            $table->addCell(6000, array('gridSpan' => 3))->addText($interresposemsgsucc->response_data_type, $fontStyle);
                            //第六行
                            $table->addRow(4000);
                            $table->addCell(2000)->addText("成功返回示例", $fontStylebold);
                            $cell1 = $table->addCell(6000, array('gridSpan' => 3));
                            $celltext = $cell1->addTextRun($paragraphStyleName);
                            foreach ($this->mb_str_split($interresposemsgsucc->response_data) as $responsesucc_data) {
                                if ($responsesucc_data == '[') {
                                    do {
                                        $celltext->addText($responsesucc_data, $fontStyle);
                                        $celltext->addTextBreak();
                                        $celltext->addText("        ", $fontStyle);
                                    } while ($responsesucc_data == ']');
                                } elseif ($responsesucc_data == ',') {
                                    $celltext->addText($responsesucc_data, $fontStyle);
                                    $celltext->addTextBreak();
                                    $celltext->addText("    ", $fontStyle);
                                } elseif ($responsesucc_data == '{') {
                                    $celltext->addText($responsesucc_data, $fontStyle);
                                    $celltext->addTextBreak();
                                    $celltext->addText("    ", $fontStyle);
                                } elseif ($responsesucc_data == '}') {
                                    $celltext->addTextBreak();
                                    $celltext->addText("    ", $fontStyle);
                                    $celltext->addText($responsesucc_data, $fontStyle);
                                } else {
                                    $celltext->addText($responsesucc_data, $fontStyle);
                                }
                            }
                        }
                        //第七行
                        foreach ($InterResposeMsgFail as $interresposemsgfail) {
                            $table->addRow(4000);
                            $table->addCell(2000)->addText("失败返回示例", $fontStylebold);
                            $cell1 = $table->addCell(6000, array('gridSpan' => 3));
                            $celltext = $cell1->addTextRun($paragraphStyleName);
                            foreach ($this->mb_str_split($interresposemsgfail->response_data) as $responsefail_data) {
                                if ($responsefail_data == '[') {
                                    do {
                                        $celltext->addText($responsefail_data, $fontStyle);
                                        $celltext->addTextBreak();
                                        $celltext->addText("        ", $fontStyle);
                                    } while ($responsefail_data == ']');
                                } elseif ($responsefail_data == ',') {
                                    $celltext->addText($responsefail_data, $fontStyle);
                                    $celltext->addTextBreak();
                                    $celltext->addText("    ", $fontStyle);
                                } elseif ($responsefail_data == '{') {
                                    $celltext->addText($responsefail_data, $fontStyle);
                                    $celltext->addTextBreak();
                                    $celltext->addText("    ", $fontStyle);
                                } elseif ($responsefail_data == '}') {
                                    $celltext->addTextBreak();
                                    $celltext->addText("    ", $fontStyle);
                                    $celltext->addText($responsefail_data, $fontStyle);
                                } else {
                                    $celltext->addText($responsefail_data, $fontStyle);
                                }
                            }
                        }
                        $textrun = $section->addTextRun($paragraphStyleName);
                        $textrun->addTextBreak();
                    }
                }
                }
            }
            $filename = $titile . ".docx";
            header("Content-Description: File Transfer");
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
            header('Content-Transfer-Encoding: binary');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Expires: 0');
            $xmlWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpword, 'Word2007');
            $xmlWriter->save("php://output");

    }

    //获取项目信息
    public function findProjectMsg($project_id)
    {
        $project_id = 1;
        $result = Project::where('id', $project_id)->select('id', 'name', 'discribe')->get();
        return $result;
    }

    //获取所有的模型名
    public function findModules($project_id)
    {
        $project_id = 1;
        $result = ProjectModule::where('project_id', $project_id)
            ->select('id', 'project_id', 'modules_name', 'class_name', 'full_class_name', 'utility')
            ->get();
        return $result;
    }

    //获取模型对应的所有方法名
    public function findModulesAllMet($module_id)
    {
        $module_id = 1;
        $result = InterfaceTable::where('module_id', $module_id)
            ->where('state', 1)
            ->select('id as interface_id', 'interface_name')->get();
        return $result;
    }

    //获取错误全部错误码
    public function getStatusCode($project_id)
    {
        $project_id = 1;
        $result = Error::where('project_id', $project_id)->select('id', 'error_code', 'error_info', 'http_code')->get();
        return $result;
    }

    //获取接口详情
    public function getInfterfaceInfo($interface_id)
    {
        $interface_id = 1;
        $result = InterfaceTable::where('id', $interface_id)->select('id', 'interface_name', 'function_name', 'route_path')
            ->where('state', 1)
            ->get();
        return $result;
    }

    //获取接口的请求表信息
    public function getInterfaceRequestMsg()
    {
        $interface_id = 1;
        $result = RequestTable::where('interface_id', $interface_id)
            ->select('id', 'request_mode', 'params')
            ->get();
        foreach ($result as $item) {
            $swetch = json_decode($item->params, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
            foreach ($swetch['interface_request'] as $value) {
                    echo $value['request_name'];
                    echo $value['request_type'];
                    echo $value['request_remark'];
                    echo "<br>";

//            }
            echo "<br>";
        }
    }
//        return $result;
}
    //获取接口的响应表信息
    public function getInterResposeMsg(){
        $interface_id = 1;
        $stat = 1;
        $result = ResponseTable::where('interface_id',$interface_id)->where('state',$stat)->select('response_data_type','response_data')->get();
        return $result;
    }
    //把字符串分割为数组
    function mb_str_split($string) {
        // /u表示把字符串当作utf-8处理，并把字符串开始和结束之前所有的字符串分割成数组
        return preg_split('/(?<!^)(?!$)/u', $string );
    }
}
