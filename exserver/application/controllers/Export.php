<?php
defined('BASEPATH') OR exit('No direct script access allowed');
use \QCloud_WeApp_SDK\Mysql\Mysql as DB;

class Export extends CI_Controller{
    function __construct(){
         parent::__construct();
    }
  
    function index($table_name){
         $query = DB::select($table_name,['*']);
         for ($i=0; $i < count($query); $i++) { 
         	$qarr[$i]=$query[$i];
         }
  		// print_r($qarr);
  		// exit;

         if(!$query)
             return false;
  
         // Starting the PHPExcel library
         $this->load->library('Extend/PHPExcel');
         $this->load->library('Extend/PHPExcel/IOFactory');
  
         $objPHPExcel = new PHPExcel();
         $objPHPExcel->getProperties()->setTitle("export")->setDescription("none");
  
         $objPHPExcel->setActiveSheetIndex(0);
  
         // Field names in the first row
         //$fields = $query->list_fields();
         $col = 0;
         foreach ($query[0] as $field){
             $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, 1, $field);
             $col++;
         }
  
         // Fetching the table data
         $row = 2;
         foreach($query as $data){
             $col = 0;
             foreach ($query as $field){
                 $objPHPExcel->getActiveSheet()->setCellValueByColumnAndRow($col, $row, $data->$field);
                 $col++;
             }
 
             $row++;
         }
  
         $objPHPExcel->setActiveSheetIndex(0);
  
         $objWriter = IOFactory::createWriter($objPHPExcel, 'Excel5');
  
         // Sending headers to force the user to download the file
         header('Content-Type: application/vnd.ms-excel');
         header('Content-Disposition: attachment;filename="Products_'.date('dMy').'.xls"');
         header('Cache-Control: max-age=0');
  
         $objWriter->save('php://output');
    }

}



?>