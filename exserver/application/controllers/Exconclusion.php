<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use \QCloud_WeApp_SDK\Mysql\Mysql as DB;
error_reporting(E_ALL&~E_NOTICE);

class Exconclusion extends CI_Controller{

	private $TOPIC_IMAGES_TYPE = 1; // 主观题题干图片类型
	private $ANSWER_IMAGES_TYPE = 2; // 主观题答案图片类型

	/**
	 * 主观题保存
	 */
	public function index(){
		try {
			$question = $_POST["question"];
			$exid = $_POST["exid"];
			if (!$this->save0($question, $exid)) {
				$this->errorHandler('主观题 保存失败');
				return;
			}
			$this->successHandler('主观题 保存成功', null);
			return;
		} catch (Exception $ex) {
			$this->errorHandler('接口异常：'.$ex->getMessage());
			return;
		}
	}

	private function save0($question, $exid) {
		if (!$question) {
			return false;
		}
		for ($i = 0; $i < count($question) ; $i++) { 
			$description = $question[$i]['topic'];
			$answer = $question[$i]['answer'];
			$answerkey = $question[$i]['analysis'];
			$score = $question[$i]['score'];
			// 主观题
			$ok = DB::insert('exconclusion',['exid' => $exid, 'topic' => $description,
				'answer' => $answer, 'analysis' => $answerkey, 'score' => $score
			]);
			if($ok	<= 0){
				return false;
			}
			// 取当前插入的主观题ID
			$data = DB::row('exconclusion',['max(id)']);
			foreach ($data as $key => $value) {
				$subjectiveQuestionId = $value;
			}
			// 更新主观题ID至图片表中，实现关联【题干】
			if (!$this->updateImages0($question[$i]['topicImageIds'], $subjectiveQuestionId)) {
				return false;
			}
			// 更新主观题ID至图片表中，实现关联【答案】
			if (!$this->updateImages0($question[$i]['answerImageIds'], $subjectiveQuestionId)) {
				return false;
			}
		}
		return true;
	}

	public function search(){
		$exid=$_POST['exid'];
		$arr = DB::select('exconclusion',['*'],['exid' => $exid]);
		foreach ($arr as $key => $value) {
			// 查询主观题相关图片
			$subjectiveQuestionId = $arr[$key]->id;
			$arr[$key]->topicImages = $this->queryImages0($subjectiveQuestionId, $this->TOPIC_IMAGES_TYPE);
			$arr[$key]->answerImages = $this->queryImages0($subjectiveQuestionId, $this->ANSWER_IMAGES_TYPE);
		}
		$this->json([
			'code' => 200,
			'msg'=>"数据获取成功",
			'data' => $arr
		]);
	}

	public function queryResult() {
		try {
			$exid = $_POST['exid'];
			$stuid = $_POST['stuid'];
			$subjectiveQuestionsResult = $this->queryResult0($exid, $stuid);
			$this->successHandler('主观题答题结果 查询成功', $subjectiveQuestionsResult);
			return;
		} catch (Exception $ex) {
			$this->errorHandler('接口异常：'.$ex->getMessage());
			return;
		}
	}

	private function queryResult0($exid, $stuid){
		$arr = DB::select('exconclusion',['*'],['exid' => $exid]);
		$exconclusions = array();
		foreach ($arr as $key => $value) {
			// 查询主观题相关图片
			$subjectiveQuestionId = $arr[$key]->id;
			$arr[$key]->topicImages = $this->queryImages0($subjectiveQuestionId, $this->TOPIC_IMAGES_TYPE);
			$arr[$key]->answerImages = $this->queryImages0($subjectiveQuestionId, $this->ANSWER_IMAGES_TYPE);
			// 查询学生答题记录
			$stureport = DB::select('stureport',['*'],[
					'exid' => $exid,
					'stuid' => $stuid,
					'titleid' => $subjectiveQuestionId
				]);
			if ($stureport) {
				array_push($exconclusions, $arr[$key]);
			} else {
				if ($arr[$key]->enabled == 1) {
					array_push($exconclusions, $arr[$key]);
				}
			}
		}
		return $exconclusions;
	}

	public function enable() {
		try {
			$subjectiveQuestionId = $_POST["id"];
			$enabled = $_POST["enabled"];
			if (!$this->enable0($subjectiveQuestionId, $enabled)) {
				$this->errorHandler('主观题 更新失败');
				return;
			}
			$this->successHandler('主观题 更新成功', null);
			return;
		} catch (Exception $ex) {
			$this->errorHandler('接口异常：'.$ex->getMessage());
			return;
		}
	}

	private function enable0($subjectiveQuestionId, $enabled) {
		$updateOk = DB::update('exconclusion', [
			'enabled' => $enabled
		], [ 'id' => $subjectiveQuestionId ]);
		if ($updateOk < 0){
			return false;
		} else {
			return true;
		}
	}

	public function update(){
		$id=$_POST['id'];
		$topic=$_POST['topic'];
		$answer=$_POST['answer'];
		$analysis=$_POST['analysis'];
		$score = $_POST['score'];
		$ok=DB::update('exconclusion',['topic'=>$topic
									,'answer'=>$answer,'analysis'=>$analysis,'score' => $score],['id'=>$id]);
		if($ok>0){
			$this->json([
				'code' => 200,
				'msg'=>"数据更新成功",
				'data' => ''
			]);
		}
		else{
			$this->json([
				'code' => 201,
				'msg'=>"数据更新失败",
				'data' => ''
			]);
		}
	}


	public function delete(){
		$id=$_POST['id'];
		$ok1=DB::delete('exconclusion',['id'=>$id]);
		$ok2 = DB::delete('subjective_question_image', ['subjective_question_id' => $id]);
		if($ok1 > 0 && $ok2 > 0){
			$this->json([
				'code' => 200,
				'msg'=>"数据删除成功",
				'data' => ''
			]);
		}
		else{
			$this->json([
				'code' => 201,
				'msg'=>"数据删除失败",
				'data' => ''
			]);
		}
	}

	public function addConclusion(){
		$id = $_POST["id"];
		$ok = DB::update('experimentinfo',['isconclusion' => 1],['id'=>$id]);
		if($ok>0){
			$this->json([
				'code' => 200,
				'msg'=>"总结添加成功",
				'data' => ''
			]);
		}
		else{
			$this->json([
				'code' => 201,
				'msg'=>"总结添加失败",
				'data' => ''
			]);
		}
	}

	/**
	 * 查询主观题图片
	 */
	public function queryImages() {
		try {
			$subjectiveQuestionId = $_POST['subjectiveQuestionId'];
			$type = $_POST['type'];
			$subjectiveQuestionImages = $this->queryImages0($subjectiveQuestionId, $type);
			$this->successHandler('主观题图片 查询成功', $subjectiveQuestionImages);
			return;
		} catch (Exception $ex) {
			$this->errorHandler('接口异常：'.$ex->getMessage());
			return;
		}
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

	/**
	 * 保存主观题图片
	 */
	public function saveImages() {
		try {
			$subjectiveQuestionId = $_POST['subjectiveQuestionId'];
			$type = $_POST['type'];
			$images = $_POST['images'];
			$flag = $_POST['flag'];
			$returnItem = $this->saveImages0($subjectiveQuestionId, $type, $images, $flag);
			if ($returnItem['success'] == false) {
				$this->errorHandler('主观题图片 保存失败');
				return;
			}
			$this->successHandler('主观题图片 保存成功', $returnItem);
			return;
		} catch (Exception $ex) {
			$this->errorHandler('接口异常：'.$ex->getMessage());
			return;
		}
	}

	private function saveImages0($subjectiveQuestionId, $type, $images, $flag) {
		$returnItem = [
			'success' => true, // 成功标志
			'imageIds' => [], // 本次插入的图片ID数组
			'flag' => -1 // 本次插入的时间戳唯一标志
		];
		// 1. 首次新增主观题时，主观题无ID还未保存，以 -1 代之；并以 flag 时间戳作为临时唯一标志
		// 2. 更新主观题时，主观题无ID已存在，删除之前的绑定图片，再插入新的图片
		if ($subjectiveQuestionId) {
			$deleteOk = DB::delete('subjective_question_image', [
				'subjective_question_id' => $subjectiveQuestionId,
				'type' => $type
			]);
			if ($deleteOk < 0) {
				$returnItem['success'] = false;
				return $returnItem;
			}
		}
		// 删除未创建主观题时的所有与该flag关联图片
		if ($flag) {
			$deleteOk = DB::delete('subjective_question_image', ['flag' => $flag]);
			if ($deleteOk < 0) {
				$returnItem['success'] = false;
				return $returnItem;
			}
		}
		$currentTimestamp = time();
		if ($images) {
			foreach ($images as $key => $value) {
				$insertOk = DB::insert('subjective_question_image', [
					'subjective_question_id' => ($subjectiveQuestionId ? $subjectiveQuestionId : -1),
					'type' => $type,
					'name' => $value['name'],
					'size' => $value['size'],
					'image_base64_content' => $value['imageBase64Content'],
					'flag' => $currentTimestamp
				]);
				if ($insertOk < 0) {
					$returnItem['success'] = false;
					return $returnItem;
				}
			}
			$subjectiveQuestionTopicImageIds = DB::select('subjective_question_image',[
				'id'
			],['flag' => $currentTimestamp], 'and', 'order by id asc');
			$returnItem['imageIds'] = $subjectiveQuestionTopicImageIds;
			$returnItem['flag'] = $currentTimestamp;
			return $returnItem;
		}
		return $returnItem;
	}

	/**
	 * 更新主观题ID至图片表中，实现关联
	 */
	private function updateImages0($imagesIds, $questionId) {
		if ($imagesIds) {
			for ($i = 0; $i < count($imagesIds); $i++) {
				$updateOk = DB::update('subjective_question_image', [
					'subjective_question_id' => $questionId
				], [ 'id' => $imagesIds[$i]['id'] ]);
				if ($updateOk < 0){
					return false;
				}
			}
		}
		return true;
	}

	private function successHandler($message, $data) {
		$this->json([
			'code' => 200,
			'msg' =>  $message,
			'data' => ($data != null ? $data : []),
		]);
	}

	private function errorHandler($message) {
		$this->json([
			'code' => 201,
			'msg' =>  $message,
			'data' => [],
		]);
	}
}


?>