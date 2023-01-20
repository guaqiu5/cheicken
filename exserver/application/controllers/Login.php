<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use \QCloud_WeApp_SDK\Mysql\Mysql as DB;

class Login extends CI_Controller {
    public function index() {
        $res = DB::row('studentinfo',['*'],['num' => $_POST["num"],'pwd' => $_POST['pwd']]);
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
                'data' => $res,
            ]);
        }
    }
	
	//重置密码
    public function repassword(){
        $num = $_POST["num"];
        $pwd = $_POST["pwd"];
        $repwd1 = $_POST["repwd1"];
        $res = DB::row('studentinfo',['*'],['num' => $_POST["num"],'pwd' => $_POST['pwd']]);
        if($res){
            $res1 = DB::update('studentinfo',['pwd' => $repwd1],['num' => $num]);
            if($res1){
                $this->json([
                    'code' => 1,
                    'msg' =>  "重置成功",
                    'data' => [],
                ]);
            }else{
                $this->json([
                    'code' => 0,
                    'msg' =>  "重置失败",
                    'data' => $res,
                ]);
            }
        }else{
             $this->json([
                'code' => 201,
                'msg' =>  "原始密码错误！",
                'data' => $res,
            ]);
        }

    }
}
