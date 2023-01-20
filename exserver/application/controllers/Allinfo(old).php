<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use \QCloud_WeApp_SDK\Mysql\Mysql as DB;
error_reporting(E_ALL&~E_NOTICE);

class Allinfo extends CI_Controller{
	public function index(){
		// $class = DB::select('studentinfo',['distinct(class)']);
		// $ename = DB::select('experimentinfo',['id','ename']);
		$class = $_POST['classname'];
		$exid = $_POST['exid'];
		$ratio = DB::row('experimentinfo',['*'],['id'=>$exid]);
		$course = DB::row('courseinfo',['preparation','operation','report'],['id' => $ratio->coid]);
		if($this->checkstr($ratio->class,$class) == false){
			$this->json([
				'code' => 201,
				'msg' => '该班级的实验尚未开启',
				'data' => []				
			]);
			return false;
		}
		$stu = DB::select('studentinfo',['id','num','name'],['class' => $class]);
		$res4 = DB::select('exconclusion',['score'],['exid' => $exid]);
		$allscore = (int)($ratio->single*$ratio->singleCount + $ratio->multi*$ratio->multiCount);
		$reportAllscore = 0;//总分
		foreach ($res4 as $key2 => $value2) {
			$reportAllscore += $value2->score;
		}
		foreach ($stu as $key => $value) {
			$res1 = DB::row('stuques',['exid','score','allscore']
												,['exid' => $exid,'stuid' => $stu[$key]->id]);

			$res2 = DB::row('record',['stime','etime','duration','operationscore']
			   									,['exid' => $exid,'stuid' => $stu[$key]->id]);
			$stu[$key]->exid = $res1->exid;
			$stu[$key]->score = (int)$res1->score / $allscore * 100;
			$stu[$key]->allscore =$allscore;
			$stu[$key]->operationscore = 0;
			if($res2->stime != NUll && $res2){
				$stu[$key]->stime = date("Y-m-d H:i:s",$res2->stime);
			}else{
				$stu[$key]->stime = $res2->stime;
			}
			if($res2->etime != NUll && $res2){
				$stu[$key]->etime = date("Y-m-d H:i:s",$res2->etime);
				$stu[$key]->duration = $this->secToTime($res2->duration);
				$stu[$key]->operationscore =(int)$res2->operationscore;
			}else{
				$stu[$key]->etime = $res2->etime;
				$stu[$key]->duration = $res2->duration;
			}
			$num = 0;//总分
			//获取主观题分数
			$res3 = DB:: select('stureport',['score'],['exid' => $exid,'stuid' => $stu[$key]->id]);
			foreach ($res3 as $key1 => $value1) {
				$num += $value1->score;
			}
			$stu[$key]->reportscore = $num / $reportAllscore * 100;

			$stu[$key]->reportAllscore = $reportAllscore;

			// $stu[$key]->rationscore = $stu[$key]->allscore;
			$stu[$key]->rationscore = $stu[$key]->score * $course->preparation/100 + $stu[$key]->operationscore * $course->operation/100 + $stu[$key]->reportscore * $course->report/100;


		}
		
		$this->json([
				'code' => 200,
				'msg' => '数据获取成功',
				'data' => $stu				
		]);
	}

	public function projectIndex(){
		$class = $_POST['classname'];
		$coid = $_POST['exid'];//课程id
		$experiment = DB::select('experimentinfo',['*'],['coid'=>$coid]);//获取实验信息

		if($experiment == NULL){
			$this->json([
				'code' => 201,
				'msg' => '该课程暂无实验',
				'data' => []				
			]);
			return false;
		}
		$stu = DB::select('studentinfo',['id','num','name'],['class' => $class]);//获取学生信息
		$course = DB::row('courseinfo',['preparation','operation','report'],['id' => $coid]);
		// $report = [];//各个实验填写的总分
		// $choice = [];//各个实验选择题总分
		foreach ($stu as $key => $stuinfo) {
			$stuChoice = 0;//学生总选择得分；
			$stuOperation = 0;//学生总操作得分；
			$stuReport = 0;//学生总报告得分；
			$num = 0;//实验总加次数
			foreach ($experiment as $key1 => $experimentinfo) {

				if($this->checkstr($experimentinfo->class,$class) == false){
					continue;
				}
				$num++;//次数加一
				$res1 = DB::row('stuques',['exid','score','allscore']
												,['exid' => $experimentinfo->id,'stuid' => $stu[$key]->id]);

				$res2 = DB::row('record',['operationscore']
			   									,['exid' => $experimentinfo->id,'stuid' => $stu[$key]->id]);
				$res4 = DB::select('exconclusion',['score'],['exid' => $experimentinfo->id]);
				$reportAllscore = 0;//各个实验填写部分总分
				foreach ($res4 as $key2 => $value2) {
					$reportAllscore += $value2->score;
				}
				$choice =  (int)($experimentinfo->single*$experimentinfo->singleCount + $experimentinfo->multi*$experimentinfo->multiCount);

				$repotnum = 0;//学生填写总分
				//获取主观题分数
				$res3 = DB:: select('stureport',['score'],['exid' => $experimentinfo->id,'stuid' => $stu[$key]->id]);
				foreach ($res3 as $key3 => $value1) {
					$repotnum += $value1->score;
				}
				$stuChoice += (int)$res1->score / $choice * 100;//百分制
				$stuOperation += (int)$res2->operationscore;//本身就是百分制，不需要转化
				$stuReport += $repotnum / $reportAllscore *100;//百分制

				// var_dump($reportAllscore);//实验填写部分总分
				// var_dump($choice);//选择部分总分
				// var_dump($repotnum);//学生报告分数
				// var_dump((int)$res1->score);//选择分数
				// var_dump((int)$res2->operationscore);//操作得分
			}
			if($num == 0){
				$this->json([
					'code' => 202,
					'msg' => '该课程实验暂未对该班级开放',
					'data' => []				
				]);
				return false;
			}
			$stu[$key]->stuChoice = round($stuChoice / $num,2);
			$stu[$key]->stuOperation = round($stuOperation / $num,2) ;
			$stu[$key]->stuReport = round($stuReport / $num,2) ;
			
			$stu[$key]->stuAllScore = round($stu[$key]->stuChoice * $course->preparation / 100  + $stu[$key]->stuOperation * $course->operation / 100 + $stu[$key]->stuReport*$course->report /100,2);
			// var_dump($stuChoice);//选择分数
			// var_dump($stuOperation);//操作得分
			// var_dump($stuReport);//报告
			// var_dump($num);
			// exit();

		}
		$this->json([
			'code' => 200,
			'msg' => '数据获取成功',
			'data' => $stu				
		]);

		// foreach ($stu as $key => $studentinfo) {
			
		// }
		// foreach ($couser as $courserKey => $courserValue) {
		// 	$ratio = $courserValue;
		// 	$exid = $courserValue->id;
		// 	if($this->checkstr($ratio->class,$class) == true){
		// 		$res4 = DB::select('exconclusion',['score'],['exid' => $exid]);
		// 		$allscore = (int)($ratio->single*$ratio->singleCount + $ratio->multi*$ratio->multiCount);
		// 		$reportAllscore = 0;//总分
		// 		foreach ($res4 as $key2 => $value2) {
		// 			$reportAllscore += $value2->score;
		// 		}
		// 		foreach ($stu as $key => $value) {
		// 			$res1 = DB::row('stuques',['exid','score','allscore']
		// 												,['exid' => $exid,'stuid' => $stu[$key]->id]);

		// 			$res2 = DB::row('record',['stime','etime','duration','operationscore']
		// 			   									,['exid' => $exid,'stuid' => $stu[$key]->id]);
		// 		$stu[$key]->exid = $res1->exid;
		// 		$stu[$key]->score = (int)$res1->score / $allscore * 100;
		// 		$stu[$key]->allscore =$allscore;
		// 		$stu[$key]->operationscore = 0;
		// 		if($res2->stime != NUll && $res2){
		// 			$stu[$key]->stime = date("Y-m-d H:i:s",$res2->stime);
		// 		}else{
		// 			$stu[$key]->stime = $res2->stime;
		// 		}
		// 		if($res2->etime != NUll && $res2){
		// 			$stu[$key]->etime = date("Y-m-d H:i:s",$res2->etime);
		// 			$stu[$key]->duration = $this->secToTime($res2->duration);
		// 			$stu[$key]->operationscore =(int)$res2->operationscore;
		// 		}else{
		// 			$stu[$key]->etime = $res2->etime;
		// 			$stu[$key]->duration = $res2->duration;
		// 		}
		// 		$num = 0;//总分
		// 		//获取主观题分数
		// 		$res3 = DB:: select('stureport',['score'],['exid' => $exid,'stuid' => $stu[$key]->id]);
		// 		foreach ($res3 as $key1 => $value1) {
		// 			$num += $value1->score;
		// 		}
		// 		$stu[$key]->reportscore = $num / $reportAllscore * 100;

		// 		$stu[$key]->reportAllscore = $reportAllscore;

		// 		// $stu[$key]->rationscore = $stu[$key]->allscore;
		// 		$stu[$key]->rationscore = $stu[$key]->score * $ratio->preparation/100 + $stu[$key]->operationscore * $ratio->operation/100 + $stu[$key]->reportscore * $ratio->report/100;

		// 		}
		// 	}
		// }
		
		
	}

	public function allExpermentIndex(){
		$class = $_POST['classname'];
		$coid = $_POST['exid'];//课程id
		$num = $_POST['num'];
		$experiment = DB::select('experimentinfo',['*'],['coid'=>$coid]);//获取实验信息

		if($experiment == NULL){
			$this->json([
				'code' => 201,
				'msg' => '该课程暂无实验',
				'data' => []				
			]);
			return false;
		}
		$course = DB::row('courseinfo',['preparation','operation','report'],['id' => $coid]);
		$stu = DB::row('studentinfo',['id','num','name'],['num' => $num]);//获取学生信息
		$allData = [];
		foreach ($experiment as $key1 => $experimentinfo) {

			if($this->checkstr($experimentinfo->class,$class) == false){
				continue;
			}
			$res1 = DB::row('stuques',['exid','score','allscore']
											,['exid' => $experimentinfo->id,'stuid' => $stu->id]);

			$res2 = DB::row('record',['operationscore']
		   									,['exid' => $experimentinfo->id,'stuid' => $stu->id]);
			$res4 = DB::select('exconclusion',['score'],['exid' => $experimentinfo->id]);
			$reportAllscore = 0;//各个实验填写部分总分
			foreach ($res4 as $key2 => $value2) {
				$reportAllscore += $value2->score;
			}
			$choice =  (int)($experimentinfo->single*$experimentinfo->singleCount + $experimentinfo->multi*$experimentinfo->multiCount);

			$repotnum = 0;//学生填写总分
			//获取主观题分数
			$res3 = DB:: select('stureport',['score'],['exid' => $experimentinfo->id,'stuid' => $stu->id]);
			foreach ($res3 as $key3 => $value1) {
				$repotnum += $value1->score;
			}
			$stuChoice = (int)$res1->score / $choice * 100;//百分制
			$stuOperation = (int)$res2->operationscore;//本身就是百分制，不需要转化
			$stuReport = $repotnum / $reportAllscore *100;//百分制
			$stuAllScore = round($stuChoice * $course->preparation / 100  + $stuOperation * $course->operation / 100 + $stuReport*$course->report /100,2);//总分
			$allData[] = [
				'exname' => $experimentinfo->ename,
				'stuChoice' => $stuChoice,
				'stuOperation' => $stuOperation,
				'stuReport' => $stuReport,
				'stuAllScore' => $stuAllScore
			];

		}
		$this->json([
			'code' => 200,
			'msg' => '数据获取成功',
			'data' => $allData				
		]);

	}

	public function secToTime($times){
		$result = '00:00:00';  
        if ($times>0) {  
                $hour = floor($times/3600);  
                $minute = floor(($times-3600 * $hour)/60);  
                $second = floor((($times-3600 * $hour) - 60 * $minute) % 60);  
                $result = $hour.':'.$minute.':'.$second;  
        }  
        return $result;  
	}
	public function checkstr($str,$needle){
		// 判断$str中是否包含$needle字符串
		//  $needle ='a';//判断是否包含a这个字符
		// if($str == NULL){
		// 	return false;
		// }
		if($str == null){
			return false;
		}else if($needle == null){
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