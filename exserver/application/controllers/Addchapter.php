<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use \QCloud_WeApp_SDK\Mysql\Mysql as DB;

class Addchapter extends CI_Controller{
		public function index(){
		$ok=DB::select('chapterinfo',['*']);
		if($ok>0){
			$this->json([
					'code'=>1,
					'msg'=>'数据获取成功',
					'data'=>$ok				
			]);
		}
		
	}
	public function insert(){
		$coname=$_POST['projectname'];
		$caname=$_POST['name'];
		$cainfo=$_POST['description'];
		$coid = $_POST['coid'];
		$ok=DB::insert('chapterinfo',['coid'=>$coid,'coname'=>$coname,'caname'=>$caname,'cainfo'=>$cainfo]);
		if($ok>0){
			$this->json([
					'code'=>200,
					'msg'=>'数据插入成功',
					'data'=>''				
			]);
		}else{
			$this->json([
					'code'=>201,
					'msg'=>'数据插入失败',
					'data'=>''				
			]);
		}
		
	}

	public function update(){
		$id=$_POST['id'];
		$caname=$_POST['name'];
		$cainfo=$_POST['description'];

		$ok=DB::update('chapterinfo',['caname'=>$caname,
						'cainfo'=>$cainfo],['id'=>$id]);
		if($ok>0){
			$this->json([
					'code'=>200,
					'msg'=>'数据更新成功',
					'data'=>''				
			]);
		}else{
			$this->json([
					'code'=>201,
					'msg'=>'数据更新失败',
					'data'=>''				
			]);
		}
	}

	public function delete(){
		$id=$_POST['id'];

		$ok=DB::delete('chapterinfo',['id'=>$id]);
	
		if($ok){
			$this->json([
				'code'=>200,
				'msg'=>'数据删除成功',
				'data'=>''
			]);
		}else{
			$this->json([
				'code'=>201,
				'msg'=>'数据删除失败',
				'data'=>''
			]);
		}
		
	}

	public function select(){
		$coid = $_POST["coid"];
		$class = $_POST["class"];
		$course = DB::row('courseinfo',['*'],['id' => $coid]);
		// $res = DB::select('chapterinfo',['*'],['coid' => $coid]);
		// foreach ($res as $key => $value) {
		// 	$row = DB::select('experimentinfo',['*'],['caid' => $value->id]);
		// 	foreach ($row as $key1 => $value1) {
		// 		$row[$key1]->isclass = $this->checkstr($value1->class,$class);
		// 	}
		// 	$value->experimentinfo = $row;
		// }
		$row = DB::select('experimentinfo',['*'],['coid' => $coid]);
		foreach ($row as $key1 => $value1) {
			$row[$key1]->isclass = $this->checkstr($value1->class,$class);
		}
		$this->json([
			'code'=>200,
			'msg'=>'数据获取成功',
			'data'=>$row,
			'course'=>$course
		]);
	}
	public function checkstr($str,$needle){
		//  $needle ='a';//判断是否包含a这个字符
		// if($str == NULL){
		// 	return false;
		// }
		if($str == null){
			return false;
		}
		$tmparray = explode($needle,$str);
		if(count($tmparray)>1){
			return true;
		} else{
			return false;
		}
	}
}


?>