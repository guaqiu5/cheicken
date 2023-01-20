<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use \QCloud_WeApp_SDK\Mysql\Mysql as DB;
error_reporting(E_ALL&~E_NOTICE);

class ClassSelect extends CI_Controller{
	public function index(){
		$res = DB::select('studentinfo',['distinct(class)']);
		if($res){
			$this->json([
				'code' => 200,
				'msg' => '数据获取成功',
				'data' => $res
			]);
		}else{
			$this->json([
				'code' => 201,
				'msg' => '数据获取失败',
				'data' => ''
			]);
		}
		
	}

}

?>