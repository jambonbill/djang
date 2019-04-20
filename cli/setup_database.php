<?php
session_start();
require __DIR__."/../vendor/autoload.php";

$Models=new Djang\Models(__DIR__."/../profiles/127.0.0.1.json");

echo "Djang\Models->createTables()\n";

$Models->createTables();

// Make sure the models are correct, issue warnings if they are not.

// TODO //
exit("done\n");