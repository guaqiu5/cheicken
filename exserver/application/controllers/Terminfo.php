<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use \QCloud_WeApp_SDK\Mysql\Mysql as DB;

class Terminfo extends CI_Controller{
    public function searchInfo(){
        $res = DB::select('terminfo',['*']);
        if($res){
            $this->json([
                'code' => 1,
                'msg' =>  "信息获取成功",
                'data' => $res,
            ]);
        }else{
             $this->json([
                'code' => 0,
                'msg' =>  "信息获取失败",
                'data' => [],
            ]);
        }
    }

    public function addTerminfo(){
        $name = $_POST['name'];
        $termRow = DB::select('terminfo',['*'],['name' => $name]);
        if(count($termRow) == 0){
            $res = DB::insert('terminfo',['name' => $name]);
            if($res){
                $this->json([
                    'code' => 200,
                    'msg' =>  "添加成功",
                    'data' => $res,
                ]);
            }else{
                 $this->json([
                    'code' => 201,
                    'msg' =>  "添加失败",
                    'data' => [],
                ]);
            }
        }else{
            $this->json([
                'code' => 201,
                'msg' =>  "名称存在，请重新输入",
                'data' => [],
            ]);
        }
    }

    public function removeTerm(){
        $id = $_POST['id'];
        $res = DB::delete('terminfo',['id' => $id]);
        if($res){
            $this->json([
                'code' => 200,
                'msg' =>  "删除成功",
                'data' => $res,
            ]);
        }else{
             $this->json([
                'code' => 201,
                'msg' =>  "删除失败",
                'data' => [],
            ]);
        }
    }

    public function editTerminfo(){
        $id = $_POST['id'];
        $name= $_POST['name'];
        $termRow = DB::select('terminfo',['*'],['name' => $name]);
        if(count($termRow) == 0){
            $res = DB::update('terminfo',['name' => $name],['id' => $id]);
            if($res){
                $this->json([
                    'code' => 200,
                    'msg' =>  "修改成功",
                    'data' => $res,
                ]);
            }else{
                 $this->json([
                    'code' => 201,
                    'msg' =>  "修改失败",
                    'data' => [],
                ]);
            }
        }else{
            $this->json([
                'code' => 201,
                'msg' =>  "名称存在，请重新输入",
                'data' => [],
            ]);
        }
    }

    public function addTermClass(){
        $termid = $_POST['termid'];
        $termname = $_POST['termname'];
        $classname = $_POST['classname'];

        $termRow = DB::select('termclassinfo',['*'],['termid' => $termid, 'classname' => $classname]);

        if(count($termRow) == 0){
            $res = DB::insert('termclassinfo',['termid' => $termid, 'termname' => $termname, 'classname' => $classname]);
            if($res){
                $this->json([
                    'code' => 200,
                    'msg' =>  "添加成功",
                    'data' => $res,
                ]);
            }else{
                 $this->json([
                    'code' => 201,
                    'msg' =>  "添加失败",
                    'data' => [],
                ]);
            }
        }else{
             $this->json([
                'code' => 201,
                'msg' =>  "名称存在，请重新输入",
                'data' => [],
            ]);
        }
    }

    /**
     * 查询学期课程信息表
     */
    public function getSearchTermClass(){
        try {
			$termName = $_POST['termname'];
			$termClassInfos = $this->getSearchTermClass0($termName);
			$this->successHandler('学期课程信息 查询成功', $termClassInfos);
			return;
		} catch (Exception $ex) {
			$this->errorHandler('接口异常：'.$ex->getMessage());
			return;
		}
    }

    private function getSearchTermClass0($termName) {
        $termClassInfos = DB::select('termclassinfo',[
            'id', 'termid', 'termname', 'classname', 'experimentids as experimentIds'
        ],['termname' => $termName]);
        foreach ($termClassInfos as $key => $value) {
            if (empty($termClassInfos[$key]->experimentIds)) {
                $termClassInfos[$key]->experimentIds = array();
            } else {
                $termClassInfos[$key]->experimentIds = $this->formatSeparatorStringToArray($termClassInfos[$key]->experimentIds);
            }
        }
        return $termClassInfos;
    }

    public function delTermClass(){
        $id = $_POST['id'];

        $res = DB::delete('termclassinfo',['id' => $id]);
        if($res){
            $this->json([
                'code' => 200,
                'msg' =>  "删除成功",
                'data' => $res,
            ]);
        }else{
             $this->json([
                'code' => 201,
                'msg' =>  "删除失败",
                'data' => [],
            ]);
        }
    }

    /**
	 * 保存某学期下的某班级的实验
	 */
	public function saveClassExperiments() {
		try {
            $termClassId = $_POST['termClassId'];
			$experimentIds = $_POST['experimentIds'];
			if (!$this->saveClassExperiments0($termClassId, $experimentIds)) {
				$this->errorHandler('实验信息 保存失败');
				return;
			}
			$this->successHandler('实验信息 保存成功', null);
			return;
		} catch (Exception $ex) {
			$this->errorHandler('接口异常：'.$ex->getMessage());
			return;
		}
	}

    private function saveClassExperiments0($termClassId, $experimentIds) {
        if (!$experimentIds) {
			return false;
		}
		$updateOk = DB::update('termclassinfo', [
            'experimentids' => $this->formatArrayToOrderlyString($experimentIds)
        ], [ 'id' => $termClassId ]);
        if ($updateOk < 0){
            return false;
        }
		return true;
    }

    /**
	 * 将数组元素升序排列，并组合成字符串
	 */
	private function formatArrayToOrderlyString($array) {
		if (!is_array($array)) {
			return $array;
		}
		sort($array); // 升序排列选项值，如：0,1,2,3
		return implode(',', $array); // 组合数组元素为字符串
	}

    /**
	 * 将带分隔符的字符串转成数组
	 */
	private function formatSeparatorStringToArray($separatorString) {
		if (!is_string($separatorString)) {
			return $separatorString;
		}
		return array_map('intval', explode(',', $separatorString)); // 转成int数组
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

?>