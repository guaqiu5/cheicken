<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use \QCloud_WeApp_SDK\Mysql\Mysql as DB;
error_reporting(E_ALL&~E_NOTICE);

class Exreport extends CI_Controller{
	public function index(){
		$content = $_POST['content'];
		$stuid = $_POST['stuid'];
		$exid = $_POST['exid'];
		$titleid = $_POST['id'];

		$experimentInfo = DB::row('experimentinfo',['*'],['id' => $exid]);
		$record = DB::row('record',['*'],['stuid'=>$stuid,'exid'=>$exid]);
		if($record->stime){
			//计算是否超时
			$startTime = $record->stime;//签到时间
			$nowTime = time();//当前时间
			$durTime = ($nowTime - $startTime) / 60 / 60 / 24; //时间间隔(单位：天)
			if($experimentInfo->eclutime >= $durTime){
				$res = DB::select('stureport',['*'],['stuid' => $stuid,'exid' => $exid,'titleid' => $titleid]);
				if(count($res)==0){
					$ok = DB::insert('stureport',['stuid' => $stuid,'exid' => $exid
										,'titleid' => $titleid,'content' => $content]);
					if($ok > 0){
						$this->json([
								'code' => 200,
								'msg' => '数据保存成功',
								'data' => ''
						]);
					}else{
						$this->json([
								'code' => 201,
								'msg' => '数据保存失败',
								'data' => ''
						]);
					}
				}else{
					$ok = DB::update('stureport',['content' => $content],['stuid' => $stuid,'exid' => $exid,'titleid' => $titleid]);
					if($ok > 0){
						$this->json([
								'code' => 200,
								'msg' => '数据更新成功',
								'data' => ''
						]);
					}else{
						$this->json([
								'code' => 202,
								'msg' => '数据更新失败',
								'data' => ''
						]);
					}
				}
			}else{
				$this->json([
					'code' => 202,
					'msg' => '更新失败，已经超出提交时间！！',
					'data' => ''
				]);
			}
		}else{
			$this->json([
				'code' => 202,
				'msg' => '请先签到，再完成实验报告',
				'data' => ''
			]);
		}
		
		
	}
	public function select(){
		$stuid = $_POST['stuid'];
		$exid = $_POST['exid'];

		$res = DB::select('stureport',['*'],['exid' => $exid,'stuid' => $stuid]);
		$this->json([
				'code' => 200,
				'msg' => '数据获取成功',
				'data' => $res
		]);

	}
}
?>