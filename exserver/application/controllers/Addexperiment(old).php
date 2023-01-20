<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use \QCloud_WeApp_SDK\Mysql\Mysql as DB;

class Addexperiment extends CI_Controller{
	public function index(){
		$ok=DB::select('experimentinfo',['*']);
		if($ok>0){
			$this->json([
				'code'=>1,
				'msg'=>'数据获取成功',
				'data'=>$ok				
			]);
		}
	}
	public function insert(){
		// $caid = $_POST["caid"];
		$coid = $_POST["coid"];
		$ename=$_POST['name'];
		$einfo=$_POST['description'];
		$etime=$_POST['time'];

		$ok=DB::insert('experimentinfo',['coid'=>$coid,'ename'=>$ename,'einfo'=>$einfo,'etime'=>$etime]);
		if($ok>0){
			$this->json([
				'code'=>200,
				'msg'=>'数据插入成功',
				'data'=>''			
			]);
		}else{
			$this->json([
				'code'=>201,
				'msg'=>'数据插入失败',
				'data'=>''			
			]);
		}
	}

	public function update(){
		$id=$_POST['id'];//实验编号
		$ename=$_POST['name'];
		$einfo=$_POST['description'];
		$etime=$_POST['time'];

		$ok=DB::update('experimentinfo',['ename'=>$ename,'einfo'=>$einfo,'etime'=>$etime],['id'=>$id]);
		if($ok>0){
			$this->json([
				'code'=>200,
				'msg'=>'数据更新成功',
				'data'=>''				
			]);
		}else{
			$this->json([
				'code'=>201,
				'msg'=>'数据更新失败',
				'data'=>''				
			]);
		}
	}

	public function delete(){
		$id=$_POST['id'];

		$ok=DB::delete('experimentinfo',['id'=>$id]);
		
		if($ok>0){
			$this->json([
				'code'=>200,
				'msg'=>'数据删除成功',
				'data'=>''				
			]);
		}else{
			$this->json([
				'code'=>201,
				'msg'=>'数据删除成功',
				'data'=>''				
			]);
		}
	}

	public function search()
	{
		$currentPage=$_POST["page"];//当前页数
        $pageSize=$_POST["pageSize"];//每页数
        $exid = $_POST["exid"];//实验id
		$start=($currentPage-1)*$pageSize;
		$filter = [];
		// var_dump($exid);exit();
		if($exid!=""){
			$filter = ['id' => $exid];
		}
        $suffix ='order by createtime desc limit '.$start.','.$pageSize;
        $res = DB::select('experimentinfo', ['*'],$filter,'and',$suffix);
        $res2 = DB::select('experimentinfo', ['id'],$filter);
        $arr = [];
        foreach ($res as $key => $value) {
        	$coname =DB::row('courseinfo',['coname'],['id'=>$value->coid]);
        	$value->coname = $coname->coname;
        	$arr[$key] = $value;

        }
        if(count($res)>0){
	        $this->json([
	            'code' => 200,
	            'msg'=>'数据获取成功',
	            'data' => $arr,
	            'total' =>count($res2)
	        ]);
        }else{
          $this->json([
              'code' => 201,
              'msg'=>'数据获取失败',
              'data' => [],
              'total' =>count($res2)
        ]);
      }
	}

	public function start(){
		$id = $_POST["id"];
		$multi = $_POST["multi"];
		$multiCount = $_POST["multiCount"];
		$single = $_POST["single"];
		$singleCount = $_POST["singleCount"];
		$classname = $_POST["classname"];
		$resmulti = DB::select('exam',['count(*)'],['exid' => $id,'type' => 1]);//多选题
		$ressingle = DB::select('exam',['count(*)'],['exid' => $id,'type' => 0]);//单选题
		foreach ($ressingle[0] as $key => $value) {
			$singleSum = $value;
		}
		foreach ($resmulti[0] as $key => $value) {
			$multiSum = $value;
		}
		if($multi > $multiSum || $single > $singleSum){
			$this->json([
				'code'=>203,
				'msg'=>'没有这么多选择题，请先添加！',
				'data'=>''				
			]);
		}else{
			// $isclass = DB::select('stuques',['*'],['exid' => $id]);
			$class = DB::row('experimentinfo',['class'],['id'=>$id]);
			$classarr = explode(',',$class->class);
			$isExitClass = 0;
			for ($i=0; $i < count($classarr); $i++) { 
				if($classarr[$i] == $classname){
					$isExitClass = 1;
				}
			}
			if($isExitClass == 0){//未开课
				$classarr[$i] = $classname;

				$ok = DB::update('experimentinfo',['single' => $single,'singleCount' => $singleCount,
						'multi' => $multi , 'multiCount' => $multiCount,'class' =>implode(",",$classarr),'isclass' => 1],['id' => $id]);
				if($ok>0){
					$this->json([
						'code'=>200,
						'msg'=>'开课成功！',
						'data'=>''				
					]);
				}else{
					$this->json([
						'code'=>201,
						'msg'=>'开课失败！',
						'data'=>''				
					]);
				}
			}else{
				$this->json([
						'code'=>201,
						'msg'=>'该班级已经开课',
						'data'=>''				
					]);
			}
		}
	}

	public function rationSelect(){
		$id = $_POST["id"];
		$res = DB::row('experimentinfo',['preparation','operation','report'],['id' => $id]);
		if(count($res)>0){
			$this->json([
	            'code' => 200,
	            'msg'=>'数据获取成功',
	            'data' => $res,
	        ]);
		}else{
			$this->json([
	            'code' => 201,
	            'msg'=>'数据获取失败',
	            'data' => [],
	        ]);
		}
	}
	public function updateratio(){
		$id = $_POST["id"];
		$preparation = $_POST["preparation"];
		$operation = $_POST["operation"];
		$report = $_POST["report"];
		$res = DB::update('experimentinfo',['preparation' => $preparation,'operation' => $operation,'report' => $report],['id'=>$id]);
		if($res>0){
			$this->json([
	            'code' => 200,
	            'msg'=>'数据更新成功',
	            'data' => [],
	        ]);
		}else{
			$this->json([
	            'code' => 201,
	            'msg'=>'数据更新失败',
	            'data' => [],
	        ]);
		}
	}
}


?>