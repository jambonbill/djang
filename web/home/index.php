<?php
// Djang test
header('Content-Type: text/html; charset=utf-8');
session_start();

require __DIR__."/../../vendor/autoload.php";

$B=new Djang\Base("../../profiles/127.0.0.1.json");


//echo "User #".$B->userId()."<br />";

$B->logout();

$user=$B->user();

echo "<pre>";print_r($user);echo "</pre>";

//exit("ok");

if(!$B->isStaff()){
  header('location:../login');
  exit;
}
