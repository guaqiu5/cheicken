<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use \QCloud_WeApp_SDK\Mysql\Mysql as DB;
error_reporting(E_ALL&~E_NOTICE);

class StuExp extends CI_Controller{
	public function index(){
		$stuid = $_POST['stuid'];
		$coid = $_POST['coid'];
		//$arr[] = '';
		$res = DB::select('experimentinfo',['*'],['coid' => $coid]);//实验id 26 27 29 30 31 32
		foreach ($res as $key => $value) {
			$res1 = DB::row('record',['*'],['exid' => $value->id,'stuid' => $stuid]);
			if($res1->duration){//已签到
				$allscore = (int)($value->single*$value->singleCount + $value->multi*$value->multiCount);
				$res2 = DB::row('experimentinfo',['ename'],['id' => $res1->exid]);
				$res3 = DB::row('stuques',['score'],['stuid' => $stuid,'exid' => $res1->exid]);
				$res5 = DB::row('record',['operationscore'],['stuid' => $stuid,'exid' => $res1->exid]);
				$res6 = DB::select('exconclusion',['score'],['exid' => $res1->exid]);
				$reportAllscore = 0;//总分
				foreach ($res6 as $key2 => $value2) {
					$reportAllscore += $value2->score;
				}
				$arr[$key]['exid'] = $res1->exid;
				$arr[$key]['ename'] = $res2->ename;
				$arr[$key]['stime'] = date("Y-m-d H:i:s",$res1->stime);
				$arr[$key]['etime'] = date("Y-m-d H:i:s",$res1->etime);
				$arr[$key]['duration'] = $this->secToTime($res1->duration);
				$arr[$key]['score'] = (int)$res3->score;
				$arr[$key]['operationscore'] = (int)$res5->operationscore;
				// 计算实验报告总分 = 主观题总分 + 客观题总分
				$subjectiveTotalScore = $this->getSubjectiveQuestionsScore($arr[$key]['exid'], $stuid);
				$objectiveTotalScore = $this->getObjectiveQuestionsScore($arr[$key]['exid'], $stuid);
				$arr[$key]['reportscore'] = $subjectiveTotalScore + $objectiveTotalScore;
				// 计算实验总分 = 预习测试题总分 * 30% + 操作得分 * 40% + 实验报告得分 * 30%
				$arr[$key]['rationscore'] =
					$arr[$key]['score'] * $value->preparation / 100
					+ $arr[$key]['operationscore'] * $value->operation / 100
					+ $arr[$key]['reportscore'] * $value->report / 100;
			}
		
		}
		$this->json([
				'code' => 200,
				'msg' => '数据获取成功',
				'data' => $arr			
		]);
	}

	/**
	 * 主观题得分
	 */
	private function getSubjectiveQuestionsScore($exid, $stuid) {
		// 获取主观题答题分数
		$resultTotalScore = 0;//总分
		$results = DB:: select('stureport',['score'],['exid' => $exid,'stuid' => $stuid]);
		foreach ($results as $key => $value) {
			$resultTotalScore += $value->score;
		}
		return $resultTotalScore;
	}

	/**
	 * 客观题得分
	 */
	private function getObjectiveQuestionsScore($exid, $stuid) {
		$resultTotalScore = 0;//总分
		//获取客观题分数
		$objectiveQuestions = DB:: select('objective_question',['id'],['exid' => $exid]);
		foreach ($objectiveQuestions as $key => $value) {
			$objectiveResult = DB:: select('objective_question_result',
				['score'],
				['stuid' => $stuid, 
				 'objective_question_id' => $objectiveQuestions[$key]->id]);
			if ($objectiveResult) {
				$resultTotalScore += $objectiveResult[0]->score;
			} else {
				$resultTotalScore += 0;
			}
		}
		return $resultTotalScore;
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
}

?>