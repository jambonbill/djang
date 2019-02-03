<?php
session_start();
require __DIR__."/../vendor/autoload.php";

$B=new Djang\Base(__DIR__."/../profiles/127.0.0.1.json");
$User=new Djang\User($B);

$test=$User->testlog();

exit("done\n");