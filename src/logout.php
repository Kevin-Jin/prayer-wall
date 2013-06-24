<?php
define("allowEntry", true);
session_start();
if (!isset($_SESSION['loggedInUserId']) && isset($_COOKIE['auth'])) {
	require_once('includes/loginFunctions.php');
	loadCookie();
}
if (!isset($_SESSION['loggedInUserId']))
	require_once('includes/hackingAttempt.php');
$title = 'Logout - thePRAYERwall';
$head = <<<HEADEND

		<meta http-equiv="Refresh" content="3; index.php" />
HEADEND;
				$body = <<<BODYEND
			<p>You will be transferred to the front page shortly. Click <a href="index.php">here</a> if you are not redirected within 3 seconds.</p>
BODYEND;

unset($_SESSION['loggedInUserId']);
unset($_SESSION['loggedInNick']);
if (isset($_COOKIE['auth'])) {
	require_once('includes/loginFunctions.php');
	destroyCookie();
}
require 'includes/pageTemplate.php';
?>
