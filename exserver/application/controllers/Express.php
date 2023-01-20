<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use \QCloud_WeApp_SDK\Mysql\Mysql as DB;

/*
  状态码：
  '-1'= > '失败',
  '0' = > '待审核',
  '1' = > '待取件',
  '2' = > '已取件',
  '3' = > '配送中',
  '4' = > '配送成功',
*/
class Express extends CI_Controller {
    public function index() {
      $res = DB::insert('receive', [
        'userid' => $_POST["openid"],
        'username' => $_POST["username"],
        'userphone' => $_POST["userphone"],
        'usermessage' => $_POST["usermessage"],
        'sendtime' => $_POST["usertime"],
        'sendaddress' => $_POST["useraddress"],
        'state'=>'0',
      ]);  
      $this->json([
          'code' => $res,
          'data' => [
              'msg' =>  $res
          ]
      ]);
    }
}