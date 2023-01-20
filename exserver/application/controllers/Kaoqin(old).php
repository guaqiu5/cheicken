<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use \QCloud_WeApp_SDK\Mysql\Mysql as DB;

class Kaoqin extends CI_Controller{
		//未签到状态，增加一条stime,etime,duration为空的数据
		public function index(){
			$stuid=$_POST['stuid'];
			$exid = $_POST['exid'];
			$res1=DB::select('record',['*'],['stuid'=>$stuid,'exid' => $exid]);
			if(count($res1) > 0){
				if($res1[0]->stime != NULL){
					$res1[0]->stime1  =  date("Y-m-d H:i:s",$res1[0]->stime);
				}
				if($res1[0]->etime != NULL){
					$res1[0]->etime1  =  date("Y-m-d H:i:s",$res1[0]->etime);
				}
				$this->json([
	                'code' => 200,
	                'msg' =>  "数据获取成功",
	                'data' => $res1
	            ]);
			}else{
				$res=DB::insert('record',['stuid'=>$stuid,'exid' => $exid]);
				if($res){
		            $this->json([
		                'code' => 200,
		                'msg' =>  "数据获取成功",
		                'data' => $res1
		            ]);
		        }else{
		             $this->json([
		                'code' => 201,
		                'msg' =>  "数据获取失败！",
		                'data' => ''
		            ]);
		        }
			}
		}

		//按下“签到”按钮，记录开始时间stime
		public function Getstime(){
			$stuid=$_POST['stuid'];
			$exid = $_POST['exid'];
			$stime = time();
			$res1 = DB::row('record',['stime'],['stuid'=>$stuid,'exid' => $exid]);
			if(!$res1->stime){
				$res=DB::update('record',['stime'=>$stime],['stuid'=>$stuid,'exid' => $exid]);
				if($res > 0){
					$this->json([
						'code' => 200,
		                'msg' =>  "签到成功",
		                'data' => []
					]);
				}else{
					$this->json([
						'code' => 201,
		                'msg' =>  "签到失败",
		                'data' => []
					]);
				}
			}else{
				$this->json([
					'code' => 202,
	                'msg' =>  "已经签到过了",
	                'data' => []
				]);
			}
			
		}

			//按下“签退”按钮，记录结束时间etime，时长duration
			public function Getetime(){
				$stuid=$_POST['stuid'];
				$exid = $_POST['exid'];
				$etime = time();
				$res1 = DB::row('record',['stime','etime'],['stuid'=>$stuid,'exid' => $exid]);
				if($res1->stime!=NULL && $res1->etime == NULL){
					$exterment = DB::row('experimentinfo',['etime'],['id' => $exid]);
					$duration=$etime-$res1->stime;
					if($duration / 60 >= (int)$exterment->etime){
						$res2=DB::update('record',['etime'=>$exterment->etime * 60 + $res1->stime,'duration'=>$exterment->etime * 60,'operationscore' => 70]
												,['stuid'=>$stuid,'exid' => $exid]);
					}else{
						$operationscore = 0;//初始化
						$option = $duration / 60 / (int)$exterment->etime;
						if($option <= 0.2){
							$operationscore = 70;
						}else if($option > 0.2 && $option <= 0.4){
							$operationscore = 85;
						}else if($option > 0.4 && $option <= 0.8){
							$operationscore = 90;
						}else if($option >= 0.8 && $option <1){
							$operationscore = 85;
						}else if($option >=1){
							$operationscore = 70;
						}
						$res2=DB::update('record',['etime'=>$etime,'duration'=>$duration,'operationscore' => $operationscore]
												,['stuid'=>$stuid,'exid' => $exid]);
					}
					if($res2>0){
						$this->json([
							'code' => 200,
			                'msg' =>  "签退成功",
			                'data' => []
						]);
					}else{
						$this->json([
							'code' => 201,
			                'msg' =>  "签退失败",
			                'data' => []
						]);
					}
				}else{
					$this->json([
						'code' => 202,
		                'msg' =>  "尚未签到,或者已经签退过了！",
		                'data' => ''
					]);
				}
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