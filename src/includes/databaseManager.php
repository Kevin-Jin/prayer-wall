<?php
if (!defined("allowEntry"))
	require_once('hackingAttempt.php');

function makeDatabaseConnection() {
	require_once('config.php');
	return new mysqli('p:' . Config::getInstance()->dbHost, Config::getInstance()->dbUser, Config::getInstance()->dbPass, Config::getInstance()->dbName);
}
?>