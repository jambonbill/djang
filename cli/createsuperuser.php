<?php
session_start();
require __DIR__."/../vendor/autoload.php";

//$B=new Djang\Base(__DIR__."/../profiles/127.0.0.1.json");

/*
$Models=new Djang\Models(__DIR__."/../profiles/127.0.0.1.json");
echo "Djang\Models->createTables()\n";
$Models->createSuperUser();
*/

$email = readline("Enter email: ");
$username = readline("Enter Username: ");
$first_name = readline("Enter first name: ");
$last_name = readline("Enter last name: ");
$password = readline("Enter Password: ");
$password2 = readline("Confirm password: ");

if ($password!=$password2) {
	exit("Error: passwords dont match\n");
}

echo "--------------\n";
echo "Email: $email\n";
echo "Username: $username\n";
echo "First name: $first_name\n";
echo "Last name: $last_name\n";
echo "--------------\n";

$confirm = readline("Confirm create superuser? (y/n): ");

//get 3 commands from user

/*
for ($i=0; $i < 3; $i++) {
        $line = readline("Command: ");
        readline_add_history($line);
}
*/

//dump history
//print_r(readline_list_history());

//dump variables
//print_r(readline_info());


exit("done\n");