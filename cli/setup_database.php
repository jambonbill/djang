<?php
session_start();
require __DIR__."/../vendor/autoload.php";

$B=new Djang\Base(__DIR__."/../profiles/127.0.0.1.json");

$Models=new Djang\Models(__DIR__."/../profiles/127.0.0.1.json");

echo "Djang\Models->createTables()\n";

$Models->createTables();

$Users=new Djang\User($B);

$users=[];
$users[]='staff@example.com';
$users[]='student@example.com';

foreach($users as $email){

	$uid=$Users->create($email, 'staff', 'example');

	//$Users->create('student@example.com', 'student', 'example');

	$Users->activate($uid);

}



// TODO //
exit("done\n");