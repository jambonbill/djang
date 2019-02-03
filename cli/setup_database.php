<?php
session_start();
require __DIR__."/../vendor/autoload.php";

$Models=new Djang\Models(__DIR__."/../profiles/127.0.0.1.json");

echo "Djang\Models->createTables()\n";

$Models->createTables();


// TODO //
exit("done\n");