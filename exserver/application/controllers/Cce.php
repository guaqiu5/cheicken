<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use \QCloud_WeApp_SDK\Mysql\Mysql as DB;
error_reporting(E_ALL&~E_NOTICE);

class Cce extends CI_Controller{
	public function index(){
		// $res = DB::select('courseinfo',['*']);
		// $info=[];
		// foreach ($res as $ke => $va) {
		// 	$info[$ke] = $va;
		// 	$info[$ke]->name = $va->coname;
		// 	$res1 = DB::select('chapterinfo',['*'],['coid' => $va->id]);
		// 	$chapter=[];
		// 	foreach ($res1 as $key => $value) {
		// 		$value->name = $value->caname;
		// 		$chapter[$key] = $value;
		// 		$res2 = DB::select('experimentinfo',['*'],['caid' => $value->id]);
		// 		$experiment=[];
		// 		foreach ($res2 as $keyex => $valuex) {
		// 			$valuex->name =$valuex->ename;	
		// 			$experiment[$keyex] = $valuex;
		// 		}
		// 		$chapter[$key]->children = $experiment;
		// 	}
		// 	$info[$ke]->children = $chapter;
		// }

		$res = DB::select('courseinfo',['*']);
		$info=[];
		foreach ($res as $ke => $va) {
			$info[$ke] = $va;
			$info[$ke]->name = $va->coname;
			$res1 = DB::select('experimentinfo',['*'],['coid' => $va->id]);
			$experiment=[];
			foreach ($res1 as $keyex => $valuex) {
				$valuex->name =$valuex->ename;	
				$experiment[$keyex] = $valuex;
			}
				
			$info[$ke]->children = $experiment;
		}
		$this->json([
			'data' => $info
		]);

	}
}


?>