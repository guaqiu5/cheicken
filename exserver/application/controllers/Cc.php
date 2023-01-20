<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use \QCloud_WeApp_SDK\Mysql\Mysql as DB;
error_reporting(E_ALL&~E_NOTICE);

class Cc extends CI_Controller{
	public function index(){
		$coid=$_POST['coid'];
		//$res = DB::select('courseinfo',['*'],['id'=>$id]);
		// $info=[];
		// foreach ($res as $ke => $va) {
		// 	$info[$ke] = $va;
		// 	$info[$ke]->name = $va->coname;
			$res1 = DB::select('chapterinfo',['*'],['coid' => $coid]);
			$chapter=[];
			foreach ($res1 as $key => $value) {
				//$value->name = $value->caname;
				$chapter[$key] = $value;
				$res2 = DB::select('experimentinfo',['*'],['caid' => $value->id]);
				$experiment=[];
				foreach ($res2 as $keyex => $valuex) {
					//$valuex->name =$valuex->ename;	
					$experiment[$keyex] = $valuex;
				}
				$chapter[$key]->children = $experiment;


			}
			//$info[$ke]->children = $chapter;
			$this->json([
				'data' => $chapter
			]);
		}
		
	}



?>