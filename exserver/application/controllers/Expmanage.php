<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use \QCloud_WeApp_SDK\Mysql\Mysql as DB;

class Expmanage extends CI_Controller{
	public function index(){
		// $res1=DB::select('courseinfo',['id','coname']);
		// foreach ($res1 as $key => $value) {
		// 	$res2 = DB::select('chapterinfo',['id','caname'],['coid' => $value->id]);
		// 	foreach ($res2 as $ke => $va) {
		// 		$res3 = DB::select('experimentinfo',['ename','flag','createtime']
		// 							,['caid' => $va->id]);
		// 			foreach ($res3 as $k => $v) {
		// 				$arr3[$k] = $v;//[实验名]
		// 				$arr2[$ke] = $va->caname;//[章节名]
		// 				$arr1[$key] = [
		// 					'projectname' => $value->coname,
		// 					'chaptername' => $arr2[$ke],
		// 					'ename' => $arr3[$k]->ename,
		// 					'flag' => $arr3[$k]->flag,
		// 					'createtime' => $arr3[$k]->createtime
		// 				];
		// 				print_r($arr1[$key]);
		// 			}	
		// 	}	
		// }
		// $this->json([
		// 	'data' => $arr1
		// ]);
		$res = DB::select('experimentinfo',['*']);
		if($res){
			foreach ($res as $key => $value) {
				$coursename = DB::select('courseinfo',['coname'],['id' => $value->coid]);
				$chaptername = DB::select('chapterinfo',['caname'],['id' => $value->caid]);
				$value->coursename = $coursename[0]->coname;
				$value->chaptername = $chaptername[0]->caname;
				$arr[$key] = $value;
			}
			$this->json([
				'code' => 1,
				'msg'=>"获取成功",
				'data' => $arr,
			]);
		}else{
			$this->json([
				'code' => 0,
				'msg'=>"获取失败",
				'data' => $res,
			]);
		}

	}


	public function delete(){
		$id=$_POST['id'];

		$ok=DB::delete('experimentinfo',['id'=>$id]);
		
		if($ok){
			$this->json([
				'code'=>1,
				'msg'=>'数据删除成功',
				'data'=>''				
			]);
		}
	}


}


?>