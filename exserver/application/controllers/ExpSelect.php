<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use \QCloud_WeApp_SDK\Mysql\Mysql as DB;
error_reporting(E_ALL&~E_NOTICE);

class ExpSelect extends CI_Controller{
	public function index(){
        $termName = $_POST['termname']; // 获取请求参数：学期名称
        $className = $_POST['classname']; // 获取请求参数：班级名称
        $experimentinfos = DB::select('experimentinfo',['id','ename','class']);
        $experimentinfosTmp = array(); // 定义临时变量
        // 如果班级名称指定，则过滤出开设班级的实验
        if ($className && $termName) {
            $termClassInfos = DB::select('termclassinfo',[
                'id', 'termid', 'termname', 'classname', 'experimentids as experimentIds'
            ],['termname' => $termName, 'classname' => $className]);
            if ($termClassInfos) {
                $experimentIds = $termClassInfos[0]->experimentIds;
                foreach ($experimentinfos as $key => $experimentinfo) {
                    if ($this->inSeparatorString($experimentinfo->id, $experimentIds) == true) {
                        array_push($experimentinfosTmp, $experimentinfo);
                    }
                }
            }
        }
        if (count($experimentinfosTmp) > 0) {
            $experimentinfos = $experimentinfosTmp;
        }
        if($experimentinfos){
			$this->json([
				'code' => 200,
				'msg' => '数据获取成功',
				'data' => $experimentinfos
			]);
		}else{
			$this->json([
				'code' => 201,
				'msg' => '数据获取失败',
				'data' => ''
			]);
		}
	}

    /**
	 * 判断目标字符串是否在带分隔符的字符串子串中
	 */
	private function inSeparatorString($target, $separatorString) {
		if (!is_string($target) || $target == '') {
			return false;
		}
		return in_array($target, explode(',', $separatorString));
	}

	public function projectindex(){
		$res = DB::select('courseinfo',['id','coname']);
		if($res){
			$this->json([
				'code' => 200,
				'msg' => '数据获取成功',
				'data' => $res
			]);
		}else{
			$this->json([
				'code' => 201,
				'msg' => '数据获取失败',
				'data' => ''
			]);
		}
		
	}

    public function checkstr($str,$needle){
        // 判断$str中是否包含$needle字符串
        //  $needle ='a';//判断是否包含a这个字符
        // if($str == NULL){
        // 	return false;
        // }
        if($str == null){
            return false;
        }else if($needle == null){
            return false;
        }
        $tmparray = explode($needle,$str);
        if(count($tmparray)>1){
            return true;
        } else{
            return false;
        }
    }

    /**
     * 查询实验信息
     */
    public function queryAllExperiments(){
        try {
			$experiments = $this->queryAllExperiments0();
			$this->successHandler('实验信息 查询成功', $experiments);
			return;
		} catch (Exception $ex) {
			$this->errorHandler('接口异常：'.$ex->getMessage());
			return;
		}
    }

    private function queryAllExperiments0() {
        $experiments = DB::select('experimentinfo',[
            'id', 'coid', 'ename as name'
        ]);
        foreach ($experiments as $key => $value) {
            $experiments[$key]->id = intval($experiments[$key]->id);
        }
        return $experiments;
    }

    private function successHandler($message, $data) {
		$this->json([
			'code' => 200,
			'msg' =>  $message,
			'data' => ($data != null ? $data : []),
		]);
	}

	private function errorHandler($message) {
		$this->json([
			'code' => 201,
			'msg' =>  $message,
			'data' => [],
		]);
	}

}
