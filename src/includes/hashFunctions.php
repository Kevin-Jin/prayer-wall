<?php
if (!defined("allowEntry"))
	require_once('hackingAttempt.php');

require_once('bcrypt.php');

function hashBcrypt($in) {
	require_once('config.php');
	return password_hash($in, PASSWORD_BCRYPT, array('cost' => Config::getInstance()->bcryptRounds));
}

function checkBcryptHash($actualHash, $check) {
	return password_verify($check, $actualHash);
}

function needsRehash($hash) {
	require_once('config.php');
	return password_needs_rehash($hash, PASSWORD_BCRYPT, array('cost' => Config::getInstance()->bcryptRounds));
}
?>
