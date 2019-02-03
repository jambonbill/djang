<?php
session_start();
require __DIR__."/../vendor/autoload.php";

$B=new Djang\Base(__DIR__."/../profiles/127.0.0.1.json");
$User=new Djang\User($B);

//print_r($B->config());exit;

//$User->activate(1);
if($User->updatePassword(1,"edx")){
	echo "updated\n";
}



exit("done\n");