<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use \QCloud_WeApp_SDK\Mysql\Mysql as DB;
error_reporting(E_ALL&~E_NOTICE);

class StuQues extends CI_Controller{
	public function index(){
		$stuid = $_POST['stuid'];//学生id
		$exid = $_POST['exid'];//实验id

		$experimentinfo = DB::select('experimentinfo',['*'],['id' => $exid]);//获取该节实验信息

		if($experimentinfo[0]->isclass == 1){//该实验开课
			$title = [];
			$qnums = [];
			
			$res = DB::select('exam',['*'],['exid' => $exid,'type' => 0]);//获取单选题题目
			$scount = count($res);//获取单选题个数
			$singlenum = $experimentinfo[0]->single;//获取实验表中的单选题个数
			$singleCount = $experimentinfo[0]->singleCount;//单选题分数
			
			$res1 = DB::select('exam',['*'],['exid' => $exid,'type' => 1]);//获取多选题个数
			$mcount = count($res1);
			$multinum = $experimentinfo[0]->multi;//获取实验表中的单选题个数
			$multiCount = $experimentinfo[0]->multiCount;//获取多选题分数

			
			//查询该学生是否已经分配到题目
			$data = DB::select('stuques',['*'],['stuid' => $stuid,'exid' => $exid]);
			//====================显示单选题===================================================
			if(!$data){//未分配题目
				if($singlenum >= 2){//多道单选题
					$stemp = range(1,$scount);//数组 1,2,3
					$stemps = array_rand($stemp,$singlenum);//数组:随机不重复数 

					foreach ($stemps as $key => $value) {
						$sarr[$key] = $res[$value];
						//获取选项
						$sarr[$key]->soptions = DB::select('examanswer',['*']
																,['examid' => $res[$value]->id]);
						$num[$key] = $sarr[$key]->id;
						array_push($title,$sarr[$key]);
					}
					// array_push($title,$sarr);
					//$title = $sarr;
					//exit;
					//存入选择题id
					$qnum = implode(',',$num);
					array_push($qnums,$qnum);
					//DB::insert('stuques',['stuid' => $stuid,'exid' => $exid,'qnum' => $qnum]);
				}else if($singlenum != 0){//一道单选题
						$stemp = rand(1,$scount);//数组:随机数 1 /2 /3
						//获取选项
						$soption = DB::select('examanswer',['*'],['examid' => $res[$stemp-1]->id]);
						//if(!$data){
						array_push($qnums,$res[$stemp-1]->id);
						//DB::insert('stuques',['stuid' => $stuid,'exid' => $exid
						//						,'qnum' => $res[$stemp-1]->id]);
						//}
						$res[$stemp-1]->soptions = $soption ;
						array_push($title,$res[$stemp-1]);
						//$title = $res[$stemp-1];
				}
				
				$num = []; //在单选之后，必须清空数组！！！
				// print_r($title);
				// exit;
				//=====================显示多选题==================================================
				if($multinum >= 2){//多道多选题
					$mtemp = range(1,$mcount);
					$mtemps = array_rand($mtemp,$multinum);//数组:随机不重复数 

					foreach ($mtemps as $key => $value) {
						$marr[$key] = $res1[$value];
						//获取选项
						$marr[$key]->soptions = DB::select('examanswer',['*'],['examid' => $res1[$value]->id]);
						$num[$key] = $marr[$key]->id;
						array_push($title,$marr[$key]);
					}

					$qnum = implode(',',$num);//数组转换为字符串
					// if(!$data){
						array_push($qnums,$qnum);
					 	//DB::insert('stuques',['stuid' => $stuid,'exid' => $exid,'qnum' => $qnum]);
					// }
				}else if($multinum != 0){//一道多选题
					$mtemp = rand(1,$mcount);
					$moption = DB::select('examanswer',['*'],['examid' => $res1[$mtemp-1]->id]);//获取选项
					//if(!$data){
						array_push($qnums,$res1[$mtemp-1]->id);
						//DB::insert('stuques',['stuid' => $stuid,'exid' => $exid
						//						,'qnum' => $res1[$mtemp-1]->id]);
					//}
					$res1[$mtemp-1]->soptions = $moption ;
					// var_dump($title);
					// exit;
					array_push($title,$res1[$mtemp-1]);

				}
					// print_r($qnums);
					// exit;
					$qqnums = implode(',',$qnums);
					DB::insert('stuques',['stuid' => $stuid,'exid' => $exid,'qnum' => $qqnums]);
					$this->json([
						'code' => 200,
						'msg' => '数据获取成功',
						'data' => [
							'questions' => $title,
							'singleCount'=>$singleCount,
							'multiCount' =>$multiCount,
							'qstu' => [],
							'experimentinfo' => $experimentinfo[0]
						]
					]);
			 }
			else{//已分配题目
					$aqnum = explode(',',$data[0]->qnum);
					// print_r($aqnum); //[24,27,30]
					// exit;
					for ($i=0;$i<count($aqnum);$i++) {
						$qres[$i] = DB::row('exam',['*'],['id' => $aqnum[$i]]);
						$ores = DB::select('examanswer',['*'],['examid' => $qres[$i]->id]);
						$qres[$i]->soptions = $ores;
					}

					$this->json([
						'code' => 200,
						'msg' => '数据获取成功',
						'data' => [
							'questions' => $qres,
							'singleCount'=>$singleCount,
							'multiCount' =>$multiCount,
							'qstu' => $data,
							'experimentinfo' => $experimentinfo[0]
						]
					]);
			}
		}else{
					$this->json([
						'code' => 201,
						'msg' => ',未开课，数据获取失败',
						'data' => ''
					]);
		}
	}

	public function submit(){
		$stuid = $_POST['stuid'];
		$exid = $_POST['exid'];
		$qtrue = $_POST['qtrue'];
		$answer = $_POST['answer'];
		$score = $_POST['score'];
		$allscore = $_POST['allscore'];
		$isfinishtest = $_POST['isfinishtest'];
		$flag = $_POST['flag'];
		$res = DB::update('stuques',['qtrue' => $qtrue,'answer' => $answer,'score' => $score,'allscore'=>$allscore,'isfinishtest' => $isfinishtest,'flag' => $flag],['stuid'=>$stuid,'exid' => $exid]);
		if($res>0){
			$this->json([
				'code' => 200,
				'msg' => '交卷成功',
				'data' => ''
			]);
		}else{
			$this->json([
				'code' => 201,
				'msg' => '交卷失败',
				'data' => ''
			]);
		}
	}
}
?>