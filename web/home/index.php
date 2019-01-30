<?php
// Djang test
header('Content-Type: text/html; charset=utf-8');
session_start();

require __DIR__."/../../vendor/autoload.php";

$B=new Djang\Base;



if(!$B->isStaff()){
  header('location:../login');
  exit;
}
