<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use \QCloud_WeApp_SDK\Mysql\Mysql as DB;
error_reporting(E_ALL&~E_NOTICE);

class StuScore1 extends CI_Controller{
	public function index(){
		$stuid = $_POST['stuid'];
		$exid = $_POST['exid'];

		//学生答题内容
		$conres1 = DB::select('stureport',['titleid','content'],['stuid' => $stuid,'exid' => $exid]);

		foreach ($conres1 as $key => $value) {
			//答案关键词
			$anares = DB::select('exconclusion',['analysis','score'],['id' => $value->titleid]);
			//var_dump($anares);
			foreach ($anares as $ke => $va) {
				//获取关键词数组
				$ana[$ke] = explode(',', $va->analysis);
				//var_dump($ana[$ke]);

				//获取每个关键词的得分
				$eachscore[$ke] = floor(($va->score) / count($ana[$ke]));
				//print_r($eachscore[$ke]);  
				foreach ($ana[$ke] as $k => $v) {
					// exit;
					$pos = strpos($value->content,$v);
					//var_dump($pos);
					if($pos !== false){
						$score[$key] += $eachscore[$ke];
					}else{
						$score[$key] += 0;
					}
				}
			}
		}
		$this->json([
				'code' => 200,
				'msg' => '数据获取成功',
				'data' => $score			
		]);
	}

}

?>