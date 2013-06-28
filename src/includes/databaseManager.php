<?php
if (!defined("allowEntry"))
	require_once('hackingAttempt.php');

function makeDatabaseConnection() {
	require_once('config.php');
	$con = new mysqli('p:' . Config::getInstance()->dbHost, Config::getInstance()->dbUser, Config::getInstance()->dbPass, Config::getInstance()->dbName);
	$con->set_charset("UTF8");
	return $con;
}
?>