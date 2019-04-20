<?php
session_start();
require __DIR__."/../vendor/autoload.php";

$B=new Djang\Base(__DIR__."/../profiles/127.0.0.1.json");
$User=new Djang\User($B);

//print_r($B->config());exit;

$email=		readline("Enter email: ");

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
  exit("Invalid email format\n");
}

$username =	readline("Enter Username: ");
$first_name=readline("Enter first name: ");
$last_name=	readline("Enter last name: ");
$password=	readline("Enter Password: ");

echo "--------------\n";
echo "Email: $email\n";
echo "Username: $username\n";
echo "First name: $first_name\n";
echo "Last name: $last_name\n";
echo "--------------\n";

$uid=$User->create($email, $first_name, $last_name);

echo "user #$uid created successfully\n";

if($User->updatePassword($uid, $password)){
	echo "Password updated\n";
}

if($User->activate($uid)){
	echo "user activated\n";
}else{
	exit("error: not activated");
}

exit("done\n");