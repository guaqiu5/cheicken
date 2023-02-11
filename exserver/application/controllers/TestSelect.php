<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use \QCloud_WeApp_SDK\Mysql\Mysql as DB;

class TestSelect extends CI_Controller{
	public function index(){
		$exid = $_POST["exid"];
        $flag1 = 0;
        $flag2 = 0;
        if(isset($_POST["SingleSelection"])){
            $SingleSelection = $_POST["SingleSelection"];
        }else{
            $SingleSelection = [];
            $flag1 = 1;
        }
        if(isset($_POST["MultipleSelection"])){
            $MultipleSelection = $_POST["MultipleSelection"];
        }else{
            $MultipleSelection = [];
            $flag2 = 1;
        }
        for ($i=0; $i < count($SingleSelection) ; $i++) { 
            $topic = $SingleSelection[$i]["topic"];
            $correctAnswer = $SingleSelection[$i]["correctAnswer"];
            $analysis = $SingleSelection[$i]["analysis"];
            $type = 0;
            $answer = $SingleSelection[$i]["answer"];
            $ok =DB::insert('exam', ['exid' => $exid,'topic' => $topic, 
                'correctAnswer' => $correctAnswer, 'analysis'=> $analysis, 'type' => $type]);
            if($ok>0){
                $data = DB::row('exam',['max(id)']);
                foreach ($data as $key => $value) {
                    $examid = $value;
                }
                for ($j=0; $j < count($answer); $j++) { 
                    $addok = DB::insert('examanswer',['examid' => $examid,'flag'=>$j,'value' =>$answer[$j]['value']]);
                    if($addok < 0){
                        $this->json([
                            'code' => 201,
                            'msg' =>  "添加失败",
                            'data' => [],
                        ]);
                    }  
                }
            }else{
                $this->json([
                    'code' => 201,
                    'msg' =>  "添加失败",
                    'data' => [],
                ]);
            }
        }
        for ($i=0; $i < count($MultipleSelection) ; $i++) { 
            $topic = $MultipleSelection[$i]["topic"];
            $correctAnswer = implode(",",$MultipleSelection[$i]["correctAnswer"]);
            $analysis = $MultipleSelection[$i]["analysis"];
            $type = 1;
            $answer = $MultipleSelection[$i]["answer"];
            $ok =DB::insert('exam', ['exid' => $exid,'topic' => $topic, 
                'correctAnswer' => $correctAnswer, 'analysis'=> $analysis, 'type' => $type]);
            if($ok>0){
                $data = DB::row('exam',['max(id)']);
                foreach ($data as $key => $value) {
                    $examid = $value;
                }
                for ($j=0; $j < count($answer); $j++) { 
                    $addok = DB::insert('examanswer',['examid' => $examid,'flag'=>$j,'value' =>$answer[$j]['value']]);
                    if($addok < 0){
                        $this->json([
                            'code' => 201,
                            'msg' =>  "添加失败",
                            'data' => [],
                        ]);
                    }  
                }
            }else{
                 $this->json([
                    'code' => 201,
                    'msg' =>  "添加失败",
                    'data' => [],
                ]);
            }
        }
        if($flag1 == 1 && $flag2 == 1){
            $this->json([
                'code' => 201,
                'msg' =>  "请至少添加一道选择题！",
                'data' => [],
            ]);
        }else{
            $this->json([
                'code' => 200,
                'msg' =>  "添加成功",
                'data' => [],
            ]);
        }
	}

    public function search(){
        $exid = $_POST["id"];
        $res = DB::select('exam',['*'],['exid' => $exid]);
        foreach ($res as $key => $value) {
            $answer = DB::select('examanswer',["*"],['examid' => $value->id]);
            $res[$key]->answer = $answer;
        }
        if($res){
            $this->json([
                'code' => 200,
                'msg' =>  "获取成功",
                'data' => $res,
            ]);
        }else{
            $this->json([
                'code' => 201,
                'msg' =>  "获取失败",
                'data' => [],
            ]);
        }

    }
    public function update(){
        $id = $_POST["id"];
        $topic = $_POST["topic"];
        $answer = $_POST["answer"];
        $correctAnswer = $_POST["correctAnswer"];
        $type = $_POST["type"];
        $analysis = $_POST["analysis"];
        $flag = false;
        if($type == 1){
            $correctAnswer = implode(",",$correctAnswer);
        }
        $ok1 = DB::update('exam',['topic' => $topic,'correctAnswer' => $correctAnswer,
            'analysis' => $analysis],['id'=>$id]);
        if($ok1>0){
            $flag = true;
        }
        foreach ($answer as $key => $value) {
            $ok2 = DB::update('examanswer',['value' => $value["value"]],['id' => $value['id']]);
            if($ok2>0){
                $flag = true;
            }
        }
        if($flag){
            $this->json([
                'code' => 200,
                'msg' =>  "更新成功",
                'data' => [],
            ]);
        }else{
             $this->json([
                'code' => 201,
                'msg' =>  "更新失败",
                'data' => [],
            ]);
        }

    }

    public function delete(){
        $id = $_POST["id"];
        $ok1 = DB::delete('exam',['id' => $id]);
        $ok2 = DB::delete('examanswer',['examid' => $id]);
        if($ok1 > 0 && $ok2 > 0){
            $this->json([
                'code' => 200,
                'msg' =>  "删除成功",
                'data' => [],
            ]);
        }else{
             $this->json([
                'code' => 201,
                'msg' =>  "删除失败",
                'data' => [],
            ]);
        }
    }

    public function swap(){
        $id1 = $_POST["id1"];
        $id2 = $_POST["id2"];
        $temp1 = DB::select('exam',['*'],['id' => $id1]);
        $temp2 = DB::select('exam',['*'],['id' => $id2]);
        $temp3 = DB::select('examanswer',['*'],['examid' => $id1]);
        $temp4 = DB::select('examanswer',['*'],['examid' => $id2]);
        $ok1 = DB::update('exam',['id' => $id2,'topic' => $temp2[0]->topic,'correctAnswer' => $temp2[0]->correctAnswer,
        'analysis' => $temp2[0]->analysis,'type' => $temp2[0]->type],['id'=>$id1]);
        $ok2 = DB::update('exam',['id' => $id1,'topic' => $temp1[0]->topic,'correctAnswer' => $temp1[0]->correctAnswer,
        'analysis' => $temp1[0]->analysis,'type' => $temp1[0]->type],['id'=>$id2]);
        $ok3 = 0;
        $ok4 = 0;
        foreach ($temp3 as $key => $value) {
            $ok3 = DB::update('examanswer',['examid' => $id2],['id' => $value->id]);
        }
        foreach ($temp4 as $key => $value) {
            $ok4 = DB::update('examanswer',['examid' => $id1],['id' => $value->id]);
        }
        if($ok1 > 0 && $ok2 > 0 && $ok3 && $ok4){
            $this->json([
                'code' => 200,
                'msg' =>  "交换成功",
                'data' => [],
            ]);
        }else{
             $this->json([
                'code' => 201,
                'msg' =>  "交换失败",
                'data' => [],
            ]);
        }
    }

    

}

?>