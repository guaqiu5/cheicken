<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use \QCloud_WeApp_SDK\Mysql\Mysql as DB;
error_reporting(E_ALL&~E_NOTICE);
//！！！！！！！！！！！！！！！！！！！！！！！无用！！！！！！！！！！！！！！！！！！！！！！！！！！！
class QueSelect extends CI_Controller{
	//获取单选题
	public function single(){
		//$id = $_POST['id'];//学生id
		$exid = $_POST['exid'];//实验id
		$single = $_POST['single'];//单选题个数

		$res = DB::select('exam',['*'],['exid' => $exid,'type' => 0]);
		$count = count($res);//获取单选题个数 
		//print_r($count); 3
		// print_r($res); //0 1 2
		
		if($single >= 2){//多道单选题
			$stemp = range(1,$count);//数组 1,2,3
			$stemps = array_rand($stemp,$single);//数组:随机不重复数 
			// print_r($temps); //0,1  0,2  1,2  
			foreach ($stemps as $key => $value) {
				$sarr[$key] = $res[$value];
				//获取选项
				$sarr[$key]->soptions = DB::select('examanswer',['*'],['examid' => $res[$value]->id]);
				$num[$key] = $sarr[$key]->id;
			}
			

			// $qnum = implode(',',$num);
			// DB::insert('stuques',['exid' => $exid,'qnum' => $qnum],['id' => $id]);

			$this->json([
				'code' => '1',
				'msg' => '数据获取成功',
				'date' => [
							'topic' => $sarr
							]
			]);
		}else{//一道单选题
			$stemp = rand(1,$count);//数组:随机数 1 /2 /3
			$soption = DB::select('examanswer',['*'],['examid' => $res[$stemp-1]->id]);//获取选项
			//DB::insert('stuques',['exid' => $exid,'qnum' => $res[$stemp-1]->id],['id' => $id]);
			$this->json([
				'code' => '1',
				'msg' => '数据获取成功',
				'date' => [
							'topic' => $res[$stemp-1],
							'option' => $soption
							]
			]);
		}

		
	}


	//获取多选题
	public function multi(){
		$id = $_POST['id'];//学生id
		$exid = $_POST['exid'];//实验id
		$multi = $_POST['multi'];//多选题个数

		$res1 = DB::select('exam',['*'],['exid' => $exid,'type' => 1]);
		$count1 = count($res1);//获取多选题个数

		if($multi >= 2){//多道多选题
			$mtemp = range(1,$count1);
			$mtemps = array_rand($mtemp,$multi);//数组:随机不重复数 

			foreach ($mtemps as $key => $value) {
				$marr[$key] = $res1[$value];
				//获取选项
				$marr[$key]->moptions = DB::select('examanswer',['*'],['examid' => $res1[$value]->id]);
				$num[$key] = $marr[$key]->id;
			}

			// $qnum = implode(',',$num);//数组转换为字符串
			// DB::insert('stuques',['exid' => $exid,'qnum' => $qnum],['id' => $id]);

			$this->json([
				'code' => '1',
				'msg' => '数据获取成功',
				'date' => [
							'topic' => $marr
							]
			]);
		}else{//一道多选题
			$mtemp = rand(1,$count1);
			$moption = DB::select('examanswer',['*'],['examid' => $res1[$mtemp-1]->id]);//获取选项
			//DB::insert('stuques',['exid' => $exid,'qnum' => $res1[$mtemp-1]->id],['id' => $id]);
			$this->json([
				'code' => '1',
				'msg' => '数据获取成功',
				'date' => [
							'topic' => $res1[$mtemp-1],
							'option' => $moption
							]
			]);
		}
		
	}

}

?>