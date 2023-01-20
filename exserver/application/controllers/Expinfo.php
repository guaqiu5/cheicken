<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use \QCloud_WeApp_SDK\Mysql\Mysql as DB;
error_reporting(E_ALL&~E_NOTICE);

class Expinfo extends CI_Controller{

	private $TOPIC_IMAGES_TYPE = 1; // 主观题题干图片类型
	private $ANSWER_IMAGES_TYPE = 2; // 主观题答案图片类型

	public function index(){
		$stuid = $_POST['stuid'];
		$exid = $_POST['exid'];
		$suffix ='order by titleid asc';
		$res = DB::select('exreportinfo',['*'],['exid' => $exid, 'printable' => 1]); // 只查询需要在报告中打印的内容

		$res1 = DB::select('stureport',['*'],['exid' => $exid,'stuid' => $stuid],'and',$suffix);
		foreach ($res1 as $key => $value) {
			$res2 = DB::row('exconclusion',['id','topic','score'],['id' => $value->titleid]);
			$res1[$key]->topic = $res2->topic;
			$res1[$key]->Allscore = $res2->score;
			$res1[$key]->keyvalue = $value->id;
			// 拼装 correctmark 批改痕迹表数据，一个 stureport.id 对应 一条批改痕迹 mark
			$resMark = DB::select('correctmark',['id', 'stureportid', 'mark'],['stureportid' => $value->id]);
			if ($resMark > 0) {
				$res1[$key]->correctmarkid = $resMark[0]->id;
				$res1[$key]->stureportid = $resMark[0]->stureportid;
				$res1[$key]->mark = $resMark[0]->mark;
			}
			// 查询主观题相关图片
			$subjectiveQuestionId = $res2->id;
			$res1[$key]->topicImages = $this->queryImages0($subjectiveQuestionId, $this->TOPIC_IMAGES_TYPE);
			$res1[$key]->answerImages = $this->queryImages0($subjectiveQuestionId, $this->ANSWER_IMAGES_TYPE);
		}
		
		// array_push($res,$res1);

		$this->json([
				'code' => 200,
				'msg' => '数据获取成功',
				'data' => $res,
				'report' => $res1			
		]);
	}

	private function queryImages0($subjectiveQuestionId, $type) {
		$subjectiveQuestionImages = DB::select('subjective_question_image',[
			'id', 'subjective_question_id as subjectiveQuestionId', 'name',
			'size', 'image_base64_content as imageBase64Content', 'flag',
			'create_time as createtime'
		], [	'subjective_question_id' => $subjectiveQuestionId,
				'type' => $type
		], 'and', 'order by id asc');
		foreach ($subjectiveQuestionImages as $key => $value) { 
			$subjectiveQuestionImages[$key]->shortName = explode('.', $value->name)[0];
		}
		return $subjectiveQuestionImages;
	}

	public function score(){
		$id = $_POST["id"];
		$score = $_POST["score"];
		$res = DB::update('stureport',['score' => $score],['id' => $id]);
		if($res > 0){
			$this->json([
				'code' => 200,
				'msg' => '更新成功',
				'data' => [],	
			]);
		}else{
			$this->json([
				'code' => 201,
				'msg' => '更新失败，请勿重复提交',
				'data' => [],	
			]);
		}
	}

	public function updateoperationscore(){
		$id = $_POST["id"];
		$exid = $_POST["exid"];
		$operationscore = $_POST["operationscore"];
		$res = DB::update('record',['operationscore' => $operationscore],['stuid' => $id,'exid' => $exid]);
		if($res>0){
			$this->json([
				'code' => 200,
				'msg' => '更新成功',
				'data' => [],	
			]);
		}else{
			$this->json([
				'code' => 201,
				'msg' => '更新失败，重复提交或该学生未签到',
				'data' => [],	
			]);
		}

	}
	
	// 查询批改痕迹
	public function queryCorrectMark() {
		$stureportid = $_POST["stureportid"];
		$res = DB::select('correctmark',['id', 'stureportid', 'mark'],['stureportid' => $stureportid]);
		$this->json([
			'code' => 200,
			'msg' => '数据获取成功',
			'data' => $res[0]
		]);
	}
	
	// 保存批改痕迹：一个 stureportid 对应一条 correctmark 记录
	public function saveCorrectMark() {
		$id = $_POST["id"];
		$stureportid = $_POST["stureportid"];
		$mark = $_POST["mark"];
		$ok = -1; // 初始值
		// 1. 确保 stureportid 的 correctmark 表记录真的不存在
		if ($id <= 0) {
			$res = DB::select('correctmark',['id'],['stureportid' => $stureportid]); // 返回数组
			if ($res > 0) {
				$id = $res[0]->id;
			}
		}
		// 2. 如果存在，则更新；否则新增；
		if ($id > 0) {
			$ok = DB::update('correctmark',['mark' => $mark],['id' => $id]); // 返回更新成功条数
			if ($ok > 0) {
				$this->json([
					'code' => 200,
					'msg' => '保存成功',
					'data' => [$id]
				]);
			}
		} else {
			$ok = DB::insert('correctmark',['stureportid' => $stureportid,'mark' => $mark]); // 返回插入成功条数
			// 新增成功后需返回 id 给前端
			if ($ok > 0) {
				$res = DB::select('correctmark',['id'],['stureportid' => $stureportid]);
				if($res > 0){
					$this->json([
						'code' => 200,
						'msg' => '保存成功',
						'data' => [$res[0]->id]	// return correctmark's id
					]);
				}
			}
		}
		if ($ok <= 0) {
			$this->json([
				'code' => 201,
				'msg' => '保存失败，或重复提交',
				'data' => [$id]
			]);
		}
	}

}

?>