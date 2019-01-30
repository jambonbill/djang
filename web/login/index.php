<?php
// Djang test
header('Content-Type: text/html; charset=utf-8');
session_start();

require __DIR__."/../../vendor/autoload.php";

$B=new Djang\Base("../../profiles/127.0.0.1.json");


exit('please log in');
