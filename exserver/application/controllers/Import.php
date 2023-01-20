<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use \QCloud_WeApp_SDK\Mysql\Mysql as DB;

class Import extends CI_Controller{
	public function index(){

		// var_dump($_FILES);exit();
		//上传文件路径到数据库
		$file = 'upload/'.$_FILES['file']['name'];

		move_uploaded_file($_FILES["file"]["tmp_name"],$file);
		DB::insert('importinfo',['file' => $file]);
		
		$pathinfo = pathinfo($file); 
		$exts = $pathinfo['extension'];  
		// var_dump($pathinfo);
		// exit(0);
		
		//导入PHPExcel类库，PHPExcel没有用命名空间                        
		$this->load->library('Extend/PHPExcel');  //注意路径            
		//创建PHPExcel对象           
		$PHPExcel = new PHPExcel();//如果excel文件后缀名为.xls，导入这个类            
		if($exts == 'xls'){                
			$this->load->library('Extend/PHPExcel/Reader/PHPExcel_Reader_Excel5');                
			$PHPReader = new PHPExcel_Reader_Excel5();            
		}else if($exts == 'xlsx'){                
			$this->load->library('Extend/PHPExcel/Reader/PHPExcel_Reader_Excel2007');                
			$PHPReader = new PHPExcel_Reader_Excel2007();               
		}

		//获取表中的第一个工作表，如果要获取第二个，把0改为1，依次类推
		$PHPExcel = $PHPReader->load($file);       
		$currentSheet = $PHPExcel->getSheet(0); 

		//获取总列数   
		$allColumn = $currentSheet->getHighestColumn();
		//获取总行数            
		$allRow = $currentSheet->getHighestRow();  

		//循环获取表中的数据，$currentRow表示当前行，从哪行开始读取数据，索引值从0开始            
		for($currentRow = 1;$currentRow <= $allRow;$currentRow++){//从哪列开始，A表示第一列                
		    for($currentColumn = 'A';$currentColumn <= $allColumn;$currentColumn++){                    
			    //数据坐标                    
			    $address = $currentColumn.$currentRow;
			    //读取到的数据，保存到数组$arr中                    
			    $data[$currentRow][$currentColumn] = $currentSheet->getCell($address)->getValue();
			}           
		}            
		//$this->json([$data]); 
		$this->save_import($data);

	}
	// //存储数据到数据库表       
	public function save_import($data){   
		$flag = true;                      
		 foreach ($data as $k => $v){               
			  if($k > 1 && $v != ""){
			  //因为第一行是标题，从第二行开始算起                                        
				  /**=========以下需要根据自己excel表格模板来定制=========**/                    
				  // $grade = $v['A'];                    
				  // $info[$k-1]['grade'] = $grade;                    
				  // $college = $v['B'];                    
				  // $info[$k-1]['college'] = $college;
				  // $class = $v['C'];                    
				  // $info[$k-1]['class'] = $class;
				  // $num = $v['D'];                    
				  // $info[$k-1]['num'] = $num;
				  // $name = $v['E'];                    
				  // $info[$k-1]['name'] = $name; 
				  // $pwd = $v['F'];                    
				  // $info[$k-1]['pwd'] = $pwd; 
				  $info[$k-1]['name'] = $v['B'];//姓名
				  $info[$k-1]['num'] = $v['C'];//学号
				  $info[$k-1]['class'] = $v['D'];//班级
				  $info[$k-1]['pwd'] = $v['C']; //密码默认是学号
				  $row = DB::select('studentinfo',['id'],['num' => $v['C']]);
				  if(count($row)>0){
				  	continue;
				  }else{
				  	$result = DB::insert('studentinfo',$info[$k-1]);
				  	if($result == 0){
				  		$flag = false;  
				  	}
				  }                                  
			  }                           
		  }            
		  /*$result=$this->db->insert_batch('content', $info1); 当然也可以使用批量插入*/            
		  if($flag){                
			    $this->json([
			    		'code' => 200,
			    		'msg' => '数据导入成功',
			    		'data' => []
			    	]); 
		  }else{                
		  		$this->json([
			    		'code' => 201,
			    		'msg' => '数据导入失败',
			    		'data' => ''
			    	]);           
		  }        
	}

	public function search(){
		$page = $_POST['page'];//当前页数
		$pageSize=$_POST["pageSize"];//每页数
		$class = $_POST["class"];//班级
		$num = $_POST['num'];//学号
		$start=($page-1)*$pageSize;
		$suffix ='limit '.$start.','.$pageSize;
		$filter = [];
		if($num != ''){
			$filter["num"] = $num;
		}
		if($class != ''){
			$filter["class"] = $class;
		}
		$res = DB::select('studentinfo',['id'],$filter);
		$res1 = DB::select('studentinfo', ['*'], $filter ,'and',$suffix);
		$total = count($res);
		$this->json([
    		'code' => 200,
    		'msg' => '数据获取成功',
    		'data' => $res1,
    		'total' => $total
    	]);   
	}
	public function adduser(){
		$name = $_POST["name"];
		$num = $_POST["num"];
		$class = $_POST["class"];
		$row = DB::select('studentinfo',['id'],['num' => $num]);
		if(count($row) > 0){
			$this->json([
	    		'code' => 202,
	    		'msg' => '添加失败，该学生可能已经添加，请检查！',
	    		'data' => ''
	    	]);      
		}else{
			$res = DB::insert('studentinfo',['name' => $name,'num' => $num , 'class' => $class , 'pwd' => $num]);
			if($res>0){
				$this->json([
		    		'code' => 200,
		    		'msg' => '添加成功',
		    		'data' => ''
		    	]); 	
			}else{
				$this->json([
		    		'code' => 201,
		    		'msg' => '添加失败！',
		    		'data' => ''
		    	]); 	
			}
		}

	}

	public function update(){
		$name = $_POST["name"];
		$num = $_POST["num"];
		$class = $_POST["class"];
		$res = DB::update("studentinfo",['name' => $name,'class' => $class],['num' => $num]);

		if($res>0){
				$this->json([
		    		'code' => 200,
		    		'msg' => '修改成功',
		    		'data' => ''
		    	]); 	
			}else{
				$this->json([
		    		'code' => 201,
		    		'msg' => '修改失败！',
		    		'data' => ''
		    	]); 	
			}
	}
	public function remove(){
		$id = $_POST["id"];
		$res = DB::delete("studentinfo",['id' => $id]);
		if($res>0){
				$this->json([
		    		'code' => 200,
		    		'msg' => '删除成功',
		    		'data' => ''
		    	]); 	
			}else{
				$this->json([
		    		'code' => 201,
		    		'msg' => '删除失败！',
		    		'data' => ''
		    	]); 	
			}
	}

	/*批量删除*/
	public function batchremove(){
		$ids = $_POST["ids"];
		$arrid = explode(",",$ids);
		$falg = true;
		for ($i=0; $i < count($arrid); $i++) { 
			$res = DB::delete("studentinfo",['id' => $arrid[$i]]);
			if($res == 0){
				$flag = false;
			}
		}
		if($falg){
				$this->json([
		    		'code' => 200,
		    		'msg' => '删除成功',
		    		'data' => ''
		    	]); 	
			}else{
				$this->json([
		    		'code' => 201,
		    		'msg' => '删除失败！',
		    		'data' => ''
		    	]); 	
			}

	}

	public function getclass(){
		$suffix = "group by class";
		$res = DB::select('studentinfo',['class'],[],'and',$suffix);
		if($res){
			$this->json([
	    		'code' => 200,
	    		'msg' => '获取成功',
	    		'data' => $res
	    	]); 	
		}else{
			$this->json([
	    		'code' => 201,
	    		'msg' => '获取失败',
	    		'data' => $res
	    	]); 
		}
	}


}

?>