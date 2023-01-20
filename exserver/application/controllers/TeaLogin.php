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

    public function searchInfo(){
        $res = DB::select('teacherinfo',['*']);
        if($res){
            $this->json([
                'code' => 1,
                'msg' =>  "获取管理员信息成功",
                'data' => $res,
            ]);
        }else{
             $this->json([
                'code' => 0,
                'msg' =>  "获取管理员信息失败",
                'data' => [],
            ]);
        }
    }

    public function addAdminInfo(){
        $tnum = $_POST['tnum'];
        $pwd = $_POST['pwd'];

        $res = DB::insert('teacherinfo',['tnum' => $tnum , 'pwd' => $pwd]);
        if($res){
            $this->json([
                'code' => 200,
                'msg' =>  "添加成功",
                'data' => $res,
            ]);
        }else{
             $this->json([
                'code' => 201,
                'msg' =>  "添加失败",
                'data' => [],
            ]);
        }
    }

    public function removeAdmin(){
        $id = $_POST['id'];
        $res = DB::delete('teacherinfo',['id' => $id]);
        if($res){
            $this->json([
                'code' => 200,
                'msg' =>  "删除成功",
                'data' => $res,
            ]);
        }else{
             $this->json([
                'code' => 201,
                'msg' =>  "删除失败",
                'data' => [],
            ]);
        }
    }

    public function editAdmin(){
        $id = $_POST['id'];
        $pwd= $_POST['pwd'];
        $res = DB::update('teacherinfo',['pwd' => $pwd],['id' => $id]);
        if($res){
            $this->json([
                'code' => 200,
                'msg' =>  "修改成功",
                'data' => $res,
            ]);
        }else{
             $this->json([
                'code' => 201,
                'msg' =>  "修改失败",
                'data' => [],
            ]);
        }
    }

}

?>