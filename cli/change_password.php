<?php
session_start();
require __DIR__."/../vendor/autoload.php";

$B=new Djang\Base(__DIR__."/../profiles/127.0.0.1.json");
$USER=new Djang\User($B);

//print_r($B->config());exit;

$email=readline("Enter username or email: ");

$uid=$USER->exist($email);//find user

if (!$uid) {
	exit("error : user `$email` not found\n");
}

$usr=$B->authUser($uid);
print_r($usr);

echo "User #$uid\n";
echo "\t".$usr['first_name'] . " ".$usr['last_name']."\n";

$pw=readline("Enter new password: ");
$pw2=readline("Confirm new password: ");

if ($pw!=$pw2) {
	exit("Passwords mismatch\n");
}

$UD=new Djang\UserDjango($B->db());
$encrypted=$UD->djangopassword($pw);//encrypt
echo "$encrypted\n";

if ($USER->updatePassword($uid, $encrypted)) {
	echo "Password updated\n";
} else {
	exit("error updating password\n");
}

exit("done\n");