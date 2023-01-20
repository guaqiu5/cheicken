<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use \QCloud_WeApp_SDK\Mysql\Mysql as DB;

class Addexreport extends CI_Controller{
	public function index(){
		$exid=$_POST['exid'];
		$retitle=$_POST['retitle'];
	
		$ok=DB::insert('exreportinfo',['exid'=>$exid,'retitle'=>$retitle]);
		$ok2 = DB::update('experimentinfo',['flag' => 1],['id' => $exid]);
		if($ok>0){
			$this->json([
				'code'=>200,
				'msg'=>'添加成功',
				'data'=>$ok				
			]);
		}else{
			$this->json([
				'code'=>201,
				'msg'=>'添加失败',
				'data'=>$ok				
			]);
		}
	}

	public function update(){
		$id=$_POST['id'];//报告编号
		$retitle=$_POST['retitle'];
		$recont=$_POST['recont'];

		$ok=DB::update('exreportinfo',['retitle'=>$retitle,'recont'=>$recont],['id'=>$id]);
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

		$ok=DB::delete('exreportinfo',['id'=>$id]);
		
		if($ok>0){
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

	public function search()
	{
		# code...
		$exid=$_POST['id'];
		$res=DB::select('exreportinfo',['*'],['exid'=>$exid]);
		if(count($res)>=0){
			$this->json([
				'code'=>200,
				'msg'=>'数据获取成功',
				'data'=>$res			
			]);
		}else{
			$this->json([
				'code'=>201,
				'msg'=>'数据获取失败',
				'data'=>''				
			]);
		}
	}

	public function updatePrintable() {
		$id = $_POST['id']; //报告编号
		$printable = $_POST['printable']; // 是否在报告中打印该内容

		$ok=DB::update('exreportinfo',['printable'=>$printable],['id'=>$id]);
		if($ok>0){
			$this->json([
				'code'=>200,
				'msg'=>'数据更新成功',
				'data'=>$printable
			]);
		}else{
			$this->json([
				'code'=>201,
				'msg'=>'数据更新失败',
				'data'=>$printable
			]);
		}
	}

}
?>