<?php
if (isset($argc) && $argc >= 2 && $argv[1] == "-f") {
	$incremental = 0;
} else if (isset($argc) && $argc >= 3 && $argv[2] == "-f") {
	$incremental = 0;
} else {
	$incremental = 1;
}
if (isset($argc) && $argc >= 2 && $argv[1] == "-v") {
	$verbose = 1;
} else if (isset($argc) && $argc >= 3 && $argv[2] == "-v") {
	$verbose = 1;
} else {
	$verbose = 0;
}

$_SERVER['PATH_INFO'] = "/viewbuildercron/index/$incremental/network/$verbose";
$_SERVER['REQUEST_URI'] = $_SERVER['PATH_INFO'];

$_SERVER["DOCUMENT_ROOT"] = dirname(__FILE__) . "/../";

require_once($_SERVER["DOCUMENT_ROOT"] . 'reports/index.php');
