<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use \QCloud_WeApp_SDK\Mysql\Mysql as DB;

class Addcourse extends CI_Controller{
	public function select(){
		$ok=DB::select('courseinfo',['*']);
		if($ok>0){
			$this->json([
				'code'=>1,
				'msg'=>'数据获取成功',
				'data'=>$ok
			]);
		}
	}

	public function index(){
		$coname=$_POST['name'];
		$cinfo=$_POST['description'];
		$tname=$_POST['teachername'];
		$tcollege=$_POST['teachercollege'];
		$preparation = $_POST["preparation"];
		$operation = $_POST["operation"];
		$report = $_POST["report"];
		if($_FILES["image"]["error"] > 0){
			$this->json([
				'code'=>501,
				'msg'=>'图片上传失败',
				'data'=>$_FILES["image"]["error"]
			]);
		}else{
			$type = trim(strrchr($_FILES["image"]["name"], '.'),'.');
	        $image = "upload/".date("Ymd").rand(10000, 99999).".".$type;
	        move_uploaded_file($_FILES["image"]["tmp_name"],$image);
			$ok=DB::insert('courseinfo',['coname'=>$coname
									,'cinfo'=>$cinfo
									,'tname'=>$tname
									,'tcollege'=>$tcollege
									,'image'=>$image
									,'preparation'=>$preparation
									,'operation' => $operation
									,'report' => $report]);
			if($ok>0){
				$this->json([
					'code'=>200,
					'msg'=>'数据添加成功',
					'data'=>$ok
				]);
			}else{
				$this->json([
					'code'=>500,
					'msg'=>'数据添加失败',
					'data'=>$ok
				]);
			}
		}
		
	}
	

	public function update(){
		$id=$_POST['id'];
		$coname=$_POST['name'];
		$cinfo=$_POST['description'];
		$tname=$_POST['teachername'];
		$tcollege=$_POST['teachercollege'];
		$preparation = $_POST["preparation"];
		$operation = $_POST["operation"];
		$report = $_POST["report"];
		if(count($_FILES) > 0){
			if($_FILES["image"]["error"] > 0){
				$this->json([
					'code'=>501,
					'msg'=>'图片上传失败',
					'data'=>$_FILES["image"]["error"]
				]);
			}
	       	$type = trim(strrchr($_FILES["image"]["name"], '.'),'.');
	        $image = "upload/".date("Ymd").rand(10000, 99999).".".$type;
	        move_uploaded_file($_FILES["image"]["tmp_name"],$image);
			$ok=DB::update('courseinfo',['coname'=>$coname
									,'cinfo'=>$cinfo
									,'tname'=>$tname
									,'tcollege'=>$tcollege
									,'image'=>$image
									,'preparation'=>$preparation
									,'operation' => $operation
									,'report' => $report],['id'=>$id]);
			
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
		}else{
			$ok=DB::update('courseinfo',['coname'=>$coname
									,'cinfo'=>$cinfo
									,'tname'=>$tname
									,'tcollege'=>$tcollege
									,'preparation'=>$preparation
									,'operation' => $operation
									,'report' => $report],['id'=>$id]);
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
		
	}

	public function delete(){
		$id=$_POST['id'];

		$ok=DB::delete('courseinfo',['id'=>$id]);
		
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
}

?>