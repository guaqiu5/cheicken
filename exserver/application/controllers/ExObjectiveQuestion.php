<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use \QCloud_WeApp_SDK\Mysql\Mysql as DB;
error_reporting(E_ALL&~E_NOTICE);

class ExobjectiveQuestion extends CI_Controller{

	// 客观题类型属性
	private $SINGLE_SELECTION_TYPE = 1; // 单选题
	private $MULTIPLE_SELECTION_TYPE = 2; // 多选题
	private $FILLING_IN_BLANK_TYPE = 3; // 填空题

	public function save(){
		try {
			$exid = $_POST['exid'];
			$singleSelection = $_POST['SingleSelection'];
			$multipleSelection = $_POST['MultipleSelection'];
			$fillingInBlank = $_POST['FillingInBlank'];
			if (!$this->save0($exid, $singleSelection)) {
				$this->errorHandler('单选题 添加失败');
				return;
			}
			if (!$this->save0($exid, $multipleSelection)) {
				$this->errorHandler('多选题 添加失败');
				return;
			}
			if (!$this->save0($exid, $fillingInBlank)) {
				$this->errorHandler('填空题 添加失败');
				return;
			}
			$this->successHandler('客观题 添加成功', null);
			return;
		} catch (Exception $ex) {
			$this->errorHandler('接口异常：'.$ex->getMessage());
			return;
		} 
	}

	private function save0($exid, $objectiveQuestions){
		if (!$objectiveQuestions) {
			return true;
		}
		for ($i = 0; $i < count($objectiveQuestions); $i++) {
			// 保存客观题
			$type = $objectiveQuestions[$i]['type'];
			$topic = $objectiveQuestions[$i]['topic'];
			$correctAnswerValue = $objectiveQuestions[$i]['correctAnswerValue'];
			if ($type == $this->MULTIPLE_SELECTION_TYPE) { // 多选题转字符串数组为字符串
				$correctAnswerValue = $this->formatArrayToOrderlyString($correctAnswerValue);
			} else if ($type == $this->FILLING_IN_BLANK_TYPE) { // 填空题 正确答案可写多个匹配项
				$correctAnswerValue = $this->formatStringSeparator($correctAnswerValue);
			}
            $analysis = $objectiveQuestions[$i]['analysis'];
            $score = $objectiveQuestions[$i]['score'];
			$questionOptions = $objectiveQuestions[$i]['questionOptions'];
            $insertOk = DB::insert('objective_question', [
				'exid' => $exid,
				'type' => $type,
				'topic' => $topic, 
                'correct_answer_value' => $correctAnswerValue,
				'analysis' => $analysis,
				'score' => $score
			]);
            if ($insertOk <= 0){
				return false;
			}
			// 取当前插入的客观题ID
			$data = DB::row('objective_question',['max(id)']);
			foreach ($data as $key => $value) {
				$objectiveQuestionId = $value;
			}
			if ($type != $this->FILLING_IN_BLANK_TYPE) { // 不是填空题，是单选/多选题
				// 保存客观题中（单选/多选）的选项
				for ($j = 0; $j < count($questionOptions); $j++) { 
					$insertSubOk = DB::insert('objective_question_option', [
						'objective_question_id' => $objectiveQuestionId,
						'option_name' => $questionOptions[$j]['optionName'],
						'option_value'=> $j
					]);
					if ($insertSubOk < 0){
						return false;
					}
				}
            }
			// 更新客观题ID至图片表中，实现关联
			if (!$this->updateTopicImages0($objectiveQuestions[$i]['topicImageIds'], $objectiveQuestionId)) {
				return false;
			}
		}
		return true;
	}

	public function query() {
		try {
			$exid = $_POST['exid'];
			$objectiveQuestions = $this->query0($exid);
			$this->successHandler('客观题 查询成功', $objectiveQuestions);
			return;
		} catch (Exception $ex) {
			$this->errorHandler('接口异常：'.$ex->getMessage());
			return;
		}
	}

	private function query0($exid) {
		$objectiveQuestions = DB::select('objective_question',[
			'id', 'exid', 'type', 'topic', 'correct_answer_value as correctAnswerValue',
			'analysis', 'score', 'create_time as createtime', 'enabled'
		],['exid' => $exid], 'and', 'order by type asc, id asc');
		foreach ($objectiveQuestions as $key => $value) {
			// 将单选题的正确答案转成数值
			if ($objectiveQuestions[$key]->type == $this->SINGLE_SELECTION_TYPE) {
				$objectiveQuestions[$key]->correctAnswerValue = 
					(int) $objectiveQuestions[$key]->correctAnswerValue;
			}
			// 将多选题的正确答案转成数组
			if ($objectiveQuestions[$key]->type == $this->MULTIPLE_SELECTION_TYPE) {
				$objectiveQuestions[$key]->correctAnswerValue = 
					$this->formatSeparatorStringToArray($objectiveQuestions[$key]->correctAnswerValue);
			}
            $questionOptions = DB::select('objective_question_option',[
				'id', 'objective_question_id as objectiveQuestionId',
				'option_name as optionName', 'option_value as optionValue'
			],['objective_question_id' => $value->id]);
            $objectiveQuestions[$key]->questionOptions = $questionOptions;
        }
		return $objectiveQuestions;
	}

	public function delete() {
		try {
			$id = $_POST["id"];
			if (!$this->delete0($id)) {
				$this->errorHandler('客观题 删除失败');
				return;
			}
			$this->successHandler('客观题 删除成功', null);
			return;
		} catch (Exception $ex) {
			$this->errorHandler('接口异常：'.$ex->getMessage());
			return;
		}
    }

	private function delete0($questionId) {
		$ok1 = DB::delete('objective_question', ['id' => $questionId]);
        $ok2 = DB::delete('objective_question_option', ['objective_question_id' => $questionId]);
		$ok3 = DB::delete('objective_question_topic_image', ['objective_question_id' => $questionId]);
		if ($ok1 > 0 && $ok2 >= 0 && $ok3 >= 0) {
			return true;
		}
		return false;
	}

	public function enable() {
		try {
			$objectiveQuestionId = $_POST["id"];
			$enabled = $_POST["enabled"];
			if (!$this->enable0($objectiveQuestionId, $enabled)) {
				$this->errorHandler('客观题 更新失败');
				return;
			}
			$this->successHandler('客观题 更新成功', null);
			return;
		} catch (Exception $ex) {
			$this->errorHandler('接口异常：'.$ex->getMessage());
			return;
		}
	}

	private function enable0($objectiveQuestionId, $enabled) {
		$updateOk = DB::update('objective_question', [
			'enabled' => $enabled
		], [ 'id' => $objectiveQuestionId ]);
		if ($updateOk < 0){
			return false;
		} else {
			return true;
		}
	}

	public function update() {
		try {
			$objectiveQuestions = $_POST["ObjectiveQuestion"];
			if (!$this->update0($objectiveQuestions)) {
				$this->errorHandler('客观题 更新失败');
				return;
			}
			$this->successHandler('客观题 更新成功', null);
			return;
		} catch (Exception $ex) {
			$this->errorHandler('接口异常：'.$ex->getMessage());
			return;
		}
    }

	private function update0($objectiveQuestions){
		if (!$objectiveQuestions) {
			return false;
		}
		for ($i = 0; $i < count($objectiveQuestions) ; $i++) {
			// 保存客观题
			$questionId = $objectiveQuestions[$i]['id'];
			$exid = $objectiveQuestions[$i]['exid'];
			$type = $objectiveQuestions[$i]['type'];
			$topic = $objectiveQuestions[$i]['topic'];
			$correctAnswerValue = $objectiveQuestions[$i]['correctAnswerValue'];
			if ($type == $this->MULTIPLE_SELECTION_TYPE) { // 多选题转字符串数组为字符串
				$correctAnswerValue = $this->formatArrayToOrderlyString($correctAnswerValue);
			} else if ($type == $this->FILLING_IN_BLANK_TYPE) { // 填空题 正确答案可写多个匹配项
				$correctAnswerValue = $this->formatStringSeparator($correctAnswerValue);
			}
            $analysis = $objectiveQuestions[$i]['analysis'];
            $score = $objectiveQuestions[$i]['score'];
			$questionOptions = $objectiveQuestions[$i]['questionOptions'];
            $updateOk = DB::update('objective_question', [
				'exid' => $exid,
				'type' => $type,
				'topic' => $topic,
                'correct_answer_value' => $correctAnswerValue,
				'analysis' => $analysis,
				'score' => $score
			], [ 'id' => $questionId ]);
            if ($updateOk < 0){
				return false;
			} else if ($type != $this->FILLING_IN_BLANK_TYPE) { // 不是填空题，是单选/多选题
				// 更新客观题中（单选/多选）的选项
				for ($j = 0; $j < count($questionOptions); $j++) {
					$optionId = $questionOptions[$j]['id'];
					$updateSubOk = DB::update('objective_question_option', [
						'objective_question_id' => $questionId,
						'option_name' => $questionOptions[$j]['optionName'],
						'option_value'=> $j
					], [ 'id' => $optionId ]);
					if ($updateSubOk < 0){
						return false;
					}
				}
            }
		}
		return true;
	}
	
	public function saveResult() {
		try {
			$isHandIn = $_POST['isHandIn'];
			$answerResults = $_POST['AnswerResult'];
			if (!$this->saveResult0($answerResults, $isHandIn)) {
				$this->errorHandler('客观题 保存失败');
				return;
			}
			$this->successHandler('客观题 保存成功', null);
			return;
		} catch (Exception $ex) {
			$this->errorHandler('接口异常：'.$ex->getMessage());
			return;
		}
	}

	private function saveResult0($answerResults, $isHandIn) {
		if (!$answerResults) {
			return false;
		}
		for ($i = 0; $i < count($answerResults); $i++) {
			// 新增或更新学生答题结果
			if (!$this->insertOrUpdateResult($answerResults[$i], $isHandIn)) {
				return false;
			}
		}
		return true;
	}

	/**
	 * 新增或更新学生答题结果
	 */
	private function insertOrUpdateResult($answerResult, $isHandIn) {
		$stuid = $answerResult['stuid'];
		$objectiveQuestionId = $answerResult['objectiveQuestionId'];
		$answerValue = $answerResult['answerValue'];
		// 查询客观题类型及正确答案
		$objectiveQuestion = DB::select('objective_question',[
			'id', 'exid', 'type', 'topic', 'correct_answer_value as correctAnswerValue',
			'analysis', 'score'
		],['id' => $objectiveQuestionId]);
		$correctAnswerValue = $objectiveQuestion[0]->correctAnswerValue;
		$type = $objectiveQuestion[0]->type;
		if ($type == $this->MULTIPLE_SELECTION_TYPE) { // 多选题转字符串数组为字符串
			$answerValue = $this->formatArrayToOrderlyString($answerValue);
		}
		// 如果交卷，则判断答题的正确性
		if ($isHandIn == 1) {
			$returnItem = $this->calcAnswerScore($type, $answerValue, $correctAnswerValue, $objectiveQuestion[0]->score);
			$isRight = $returnItem['isRight'];
			$score = $returnItem['score'];
		} else {
			$isRight = 0;
			$score = 0;
		}
		// 查询客观题答题记录
		$objectiveQuestionResult = DB::select('objective_question_result',[
			'id', 'stuid', 'objective_question_id as objectiveQuestionId', 
			'answer_value as answerValue', 'is_hand_in as isHandIn',
			'is_right as isRight', 'score'
		],['stuid' => $stuid, 'objective_question_id' => $objectiveQuestionId]);
		if (!$objectiveQuestionResult) {
			$insertOk = DB::insert('objective_question_result', [
				'stuid' => $stuid,
				'objective_question_id' => $objectiveQuestionId,
				'answer_value' => $answerValue,
				'is_hand_in' => $isHandIn,
				'is_right' => $isRight,
				'score' => $score
			]);
			if ($insertOk < 0) {
				return false;
			}
		} else {
			$updateOk = DB::update('objective_question_result', [
				'answer_value' => $answerValue,
				'is_hand_in' => $isHandIn,
				'is_right' => $isRight,
				'score' => $score
			], [ 'id' => $objectiveQuestionResult[0]->id ]);
			if ($updateOk < 0){
				return false;
			}
		}
		return true;
	}

	/**
	 * 计算答题得分
	 * 	单选/多选 全匹配才算正确
	 *  填空 支持部分正确
	 * 
	 * 	0-不正确
	 *  1-正确
	 *  2-部分正确
	 * 
	 */
	private function calcAnswerScore($questionType, $answerValue, $correctAnswerValue, $correctScore) {
		if ($questionType == $this->SINGLE_SELECTION_TYPE && $answerValue == $correctAnswerValue) {
			$isRight = 1;
			$score = $correctScore;
		} else if ($questionType == $this->MULTIPLE_SELECTION_TYPE && $answerValue == $correctAnswerValue) {
			$isRight = 1;
			$score = $correctScore;
		} else if ($questionType == $this->FILLING_IN_BLANK_TYPE) {
			$returnItem = $this->calcFillingInBlankAnswerScore($answerValue, $correctAnswerValue, $correctScore);
			$isRight = $returnItem['isRight'];
			$score = $returnItem['score'];
		} else {
			$isRight = 0;
			$score = 0;
		}
		$returnItem = [
			'isRight' => $isRight,
			'score' => $score
		];
		return $returnItem;
	}

	/**
	 * 填空题答案按匹配个数记分
	 * 	0-不正确
	 *  1-正确
	 *  2-部分正确
	 */
	private function calcFillingInBlankAnswerScore($answerValue, $correctAnswerValue, $correctScore) {
		$answerValueArray = explode(',', $this->formatStringSeparator($answerValue));
		$correctAnswerArray = explode(',', $this->formatStringSeparator($correctAnswerValue));
		$answerCorrectNum = 0;
		$correctAnswerNum = count($correctAnswerArray);
		for ($i = 0; $i < count($correctAnswerArray) && $i < count($answerValueArray); $i++) {
			if ($correctAnswerArray[$i] == $answerValueArray[$i]) {
				$answerCorrectNum++;
			}
		}
		if ($correctAnswerNum == 0) {
			$isRight = 0;
			$score = 0;
		} else {
			$isRight = $answerCorrectNum <= 0 ? 0 : ($answerCorrectNum < $correctAnswerNum ? 2 : 1);
			$score = $correctScore * $answerCorrectNum / $correctAnswerNum;
		}
		$returnItem = [
			'isRight' => $isRight,
			'score' => $score
		];
		return $returnItem;
	}

	/**
	 * 更新客观题分数
	 */
	public function updateResultScore() {
		try {
			$objectiveQuestionId = $_POST['objectiveQuestionId'];
			$questionResultId = $_POST['questionResultId'];
			$score = $_POST['score'];
			$updateResult = $this->updateResultScore0($objectiveQuestionId, $questionResultId, $score);
			if (!$updateResult['success']) {
				$this->errorHandler('客观题得分 修改失败');
				return;
			}
			$this->successHandler('客观题得分 修改成功', $updateResult);
			return;
		} catch (Exception $ex) {
			$this->errorHandler('接口异常：'.$ex->getMessage());
			return;
		}
	}

	private function updateResultScore0($objectiveQuestionId, $questionResultId, $score) {
		$updateResult = [
			'success' => false,
			'isRight' => 0,
			'score' => 0,
		];
		$objectiveQuestion = DB::select('objective_question',[
			'id', 'exid', 'type', 'topic', 'correct_answer_value as correctAnswerValue',
			'analysis', 'score'
		],['id' => $objectiveQuestionId]);
		if ($objectiveQuestion) {
			$questionScore = $objectiveQuestion[0]->score;
			if ($score == 0) {
				$isRight = 0;
			} else if ($score == $questionScore) {
				$isRight = 1;
			} else {
				$isRight = 2;
			}
			$updateOk = DB::update('objective_question_result', [
				'score' => $score,
				'is_right' => $isRight,
			], [ 'id' => $questionResultId ]);
			if ($updateOk < 0){
				$updateResult['success'] = false;
				return $updateResult;
			}
			$updateResult['success'] = true;
			$updateResult['isRight'] = $isRight;
			$updateResult['score'] = $score;
			return $updateResult;
		} else {
			$updateResult['success'] = false;
			return $updateResult;
		}
	}

	/**
	 * 查询实验报告客观题学生作答结果
	 */
	public function queryResult() {
		try {
			$exid = $_POST['exid'];
			$stuid = $_POST['stuid'];
			$objectiveQuestions = $this->queryResult0($exid, $stuid);
			$this->successHandler('客观题答题结果 查询成功', $objectiveQuestions);
			return;
		} catch (Exception $ex) {
			$this->errorHandler('接口异常：'.$ex->getMessage());
			return;
		}
	}

	private function queryResult0($exid, $stuid) {
		$objectiveQuestionsResult = array();
		$isAllHandIn = 0;
		// 客观题主干
		$objectiveQuestions = DB::select('objective_question',[
			'id', 'exid', 'type', 'topic', 'score', 'correct_answer_value as correctAnswerValue', 'enabled',
		],['exid' => $exid], 'and', 'order by type asc, id asc');
		foreach ($objectiveQuestions as $key => $value) {
			if ($objectiveQuestions[$key]->type != $this->FILLING_IN_BLANK_TYPE) {
				// 客观题选项（仅单选/多选，填空无）
				$questionOptions = DB::select('objective_question_option',[
					'id', 'objective_question_id as objectiveQuestionId',
					'option_name as optionName', 'option_value as optionValue'
				],['objective_question_id' => $value->id]);
				$this->appendOptionsIsRight($questionOptions, $objectiveQuestions[$key]->correctAnswerValue);
				$objectiveQuestions[$key]->questionOptions = $questionOptions;
			}
			// 学生答题结果
			$objectiveQuestionResult = DB::select('objective_question_result',[
				'id', 'stuid', 'objective_question_id as objectiveQuestionId', 
				'answer_value as answerValue', 'is_hand_in as isHandIn',
				'is_right as isRight', 'score'
			],['objective_question_id' => $value->id, 'stuid' => $stuid]);
			if ($objectiveQuestionResult) {
				$isAllHandIn = 1;
				$objectiveQuestions[$key]->isHandIn = $objectiveQuestionResult[0]->isHandIn;
				if ($objectiveQuestions[$key]->type == $this->SINGLE_SELECTION_TYPE) {
					$objectiveQuestionResult[0]->answerValue = (int) $objectiveQuestionResult[0]->answerValue;
				}
				// 将多选题的答题结果转成数组
				if ($objectiveQuestions[$key]->type == $this->MULTIPLE_SELECTION_TYPE) {
					$objectiveQuestionResult[0]->answerValue = 
						$this->formatSeparatorStringToArray($objectiveQuestionResult[0]->answerValue);
				}
				$objectiveQuestions[$key]->questionResult = $objectiveQuestionResult[0];
			} else {
				$objectiveQuestions[$key]->isHandIn = 0;
			}
			// 查询题干图片
			$objectiveQuestions[$key]->topicImages = $this->queryTopicImages0($objectiveQuestions[$key]->id);
        }
		if ($isAllHandIn == 1) {
			// 若已答题，过滤出只答过的题目，并作为最终返回（否则后来新增/启用而未作答则前端报错）
			foreach ($objectiveQuestions as $key => $value) {
				if ($objectiveQuestions[$key]->questionResult) {
					array_push($objectiveQuestionsResult, $objectiveQuestions[$key]);
				}
			}
		} else {
			// 若未答题，过滤出启用的题目，并作为最终返回
			foreach ($objectiveQuestions as $key => $value) {
				if ($objectiveQuestions[$key]->enabled == 1) {
					array_push($objectiveQuestionsResult, $objectiveQuestions[$key]);
				}
			}
		}
		return $objectiveQuestionsResult;
	}

	/**
	 * 将数组元素升序排列，并组合成字符串
	 */
	private function formatArrayToOrderlyString($array) {
		if (!is_array($array)) {
			return $array;
		}
		sort($array); // 升序排列选项值，如：0,1,2,3
		return implode(',', $array); // 组合数组元素为字符串
	}

	/**
	 * 将带分隔符的字符串转成数组
	 */
	private function formatSeparatorStringToArray($separatorString) {
		if (!is_string($separatorString)) {
			return $separatorString;
		}
		return array_map('intval', explode(',', $separatorString)); // 转成int数组
	}

	/**
	 * 将字符串中的分隔符逗号标准化为英文逗号
	 */
	private function formatStringSeparator($string) {
		$string = str_replace('，', ',', $string); // 替换中文逗号为英文逗号
		$string = preg_replace('/\s/', '', $string); // 去除空白字符，包括回车换行
		return $string;
	}

	/**
	 * 判断目标字符串是否在带分隔符的字符串子串中
	 */
	private function inSeparatorString($target, $separatorString) {
		if (!is_string($target) || $target == '') {
			return false;
		}
		return in_array($target, explode(',', $separatorString));
	}

	/**
	 * 向选项中添加该选项是否为正确答案的标记
	 */
	private function appendOptionsIsRight($questionOptions, $correctAnswerValue) {
		foreach ($questionOptions as $key => $value) {
			$isRight = $this->inSeparatorString($questionOptions[$key]->optionValue, $correctAnswerValue);
			$questionOptions[$key]->isRight = $isRight;
		}
	}

	/**
	 * 查询客观题题干图片
	 */
	public function queryTopicImages() {
		try {
			$objectiveQuestionId = $_POST['objectiveQuestionId'];
			$objectiveQuestionTopicImages = $this->queryTopicImages0($objectiveQuestionId);
			$this->successHandler('客观题题干图片 查询成功', $objectiveQuestionTopicImages);
			return;
		} catch (Exception $ex) {
			$this->errorHandler('接口异常：'.$ex->getMessage());
			return;
		}
	}

	private function queryTopicImages0($objectiveQuestionId) {
		$objectiveQuestionTopicImages = DB::select('objective_question_topic_image',[
			'id', 'objective_question_id as objectiveQuestionId', 'name',
			'size', 'image_base64_content as imageBase64Content', 'flag',
			'create_time as createtime'
		],['objective_question_id' => $objectiveQuestionId], 'and', 'order by id asc');
		foreach ($objectiveQuestionTopicImages as $key => $value) { 
			$objectiveQuestionTopicImages[$key]->shortName = explode('.', $value->name)[0];
		}
		return $objectiveQuestionTopicImages;
	}

	/**
	 * 保存客观题题干图片
	 */
	public function saveTopicImages() {
		try {
			$objectiveQuestionId = $_POST['objectiveQuestionId'];
			$images = $_POST['images'];
			$flag = $_POST['flag'];
			$returnItem = $this->saveTopicImages0($objectiveQuestionId, $images, $flag);
			if ($returnItem['success'] == false) {
				$this->errorHandler('客观题题干图片 保存失败');
				return;
			}
			$this->successHandler('客观题题干图片 保存成功', $returnItem);
			return;
		} catch (Exception $ex) {
			$this->errorHandler('接口异常：'.$ex->getMessage());
			return;
		}
	}

	private function saveTopicImages0($objectiveQuestionId, $images, $flag) {
		$returnItem = [
			'success' => true, // 成功标志
			'imageIds' => [], // 本次插入的图片ID数组
			'flag' => -1 // 本次插入的时间戳唯一标志
		];
		// 1. 首次新增客观题时，客观题无ID还未保存，以 -1 代之；并以 flag 时间戳作为临时唯一标志
		// 2. 更新客观题时，客观题无ID已存在，删除之前的绑定图片，再插入新的图片
		if ($objectiveQuestionId) {
			$deleteOk = DB::delete('objective_question_topic_image', ['objective_question_id' => $objectiveQuestionId]);
			if ($deleteOk < 0) {
				$returnItem['success'] = false;
				return $returnItem;
			}
		}
		// 删除未创建客观题时的所有与该flag关联图片
		if ($flag) {
			$deleteOk = DB::delete('objective_question_topic_image', ['flag' => $flag]);
			if ($deleteOk < 0) {
				$returnItem['success'] = false;
				return $returnItem;
			}
		}
		$currentTimestamp = time();
		if ($images) {
			foreach ($images as $key => $value) {
				$insertOk = DB::insert('objective_question_topic_image', [
					'objective_question_id' => ($objectiveQuestionId ? $objectiveQuestionId : -1),
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
			$objectiveQuestionTopicImageIds = DB::select('objective_question_topic_image',[
				'id'
			],['flag' => $currentTimestamp], 'and', 'order by id asc');
			$returnItem['imageIds'] = $objectiveQuestionTopicImageIds;
			$returnItem['flag'] = $currentTimestamp;
			return $returnItem;
		}
		return $returnItem;
	}

	/**
	 * 更新客观题ID至图片表中，实现关联
	 */
	private function updateTopicImages0($imagesIds, $questionId) {
		if ($imagesIds) {
			for ($i = 0; $i < count($imagesIds); $i++) {
				$updateOk = DB::update('objective_question_topic_image', [
					'objective_question_id' => $questionId
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