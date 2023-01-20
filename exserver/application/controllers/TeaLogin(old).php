<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use \QCloud_WeApp_SDK\Mysql\Mysql as DB;

class TeaLogin extends CI_Controller{
	public function index(){
		$tnum=$_POST['tnum'];
		$pwd=$_POST['pwd'];

		$res=DB::row('teacherinfo',['*'],['tnum'=>$tnum,'pwd'=>$pwd]);
		 if($res){
            $this->json([
                'code' => 1,
                'msg' =>  "登录成功",
                'data' => $res,
            ]);
        }else{
             $this->json([
                'code' => 0,
                'msg' =>  "账号或密码错误！",
                'data' => [],
            ]);
        }
	}

}

?>