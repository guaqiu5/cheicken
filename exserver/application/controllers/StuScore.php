<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use \QCloud_WeApp_SDK\Mysql\Mysql as DB;
error_reporting(E_ALL&~E_NOTICE);

class StuScore extends CI_Controller{
	public function index(){
		$stuid = $_POST['stuid'];
		$exid = $_POST['exid'];
		$titleid = $_POST['titleid'];
		//学生答题内容
		//$conres1 = DB::select('stureport',['content'],['stuid' => $stuid,'exid' => $exid]);
		//答案关键词
		$anares = DB::select('exconclusion',['id','analysis','score'],['exid' => $exid]);
		//print_r($anares);

		foreach ($anares as $key => $value) {
			//获取关键词数组
			$ana[$key] = explode(',', $value->analysis);
			//print_r($ana[$key]);
			
			//获取每个关键词的得分
			$eachscore[$key] = floor(($value->score) / count($ana[$key]));
			//print_r($eachscore[$key]);  
		
			//关键词数组循环模糊查询
			foreach ($ana[$key] as $k => $v) {
				//print_r($v);
				$suffix = "content LIKE '%".$v."%'";
				// $conres = DB::select('stureport',['content'],$suffix);
				// $conres = DB::select('stureport',['*'],['stuid' => $stuid,'exid' => $exid
				// 										,'titleid' => $value->id,$suffix]);
				$select = 'stuid = '.$stuid . ' and exid = '.$exid. ' and titleid = '. $value->id .' and '.$suffix;
				$conres = DB::select('stureport',['*'],$select);
				if($conres){
					$score[$key] += $eachscore[$key];
				}else{
					$score[$key] += 0;
				}
			}
			//print_r($score[$key]);
	}

			

		$this->json([
				'code' => 200,
				'msg' => '数据获取成功',
				'data' => $score			
		]);
	}

}

?>