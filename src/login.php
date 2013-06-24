<?php
function query() {
	if (!isset($_POST['email']) || !isset($_POST['password']))
		require_once('includes/hackingAttempt.php');

	define("allowEntry", true);
	session_start();
	if (!isset($_SESSION['loggedInUserId']) && isset($_COOKIE['auth'])) {
		require_once('includes/loginFunctions.php');
		loadCookie();
	}
	if (isset($_SESSION['loggedInUserId']))
		require_once('includes/hackingAttempt.php');
	$title = 'Login - thePRAYERwall';
	$head = '';

	require_once('includes/databaseManager.php');
	$con = makeDatabaseConnection();
	$ps = $con->prepare("SELECT `userid`,`displayname`,`password` FROM `users` WHERE `email` = ?");
	$ps->bind_param('s', $_POST['email']);
	if ($ps->execute()) {
		$ps->bind_result($userid, $displayname, $hash);
		if ($ps->fetch()) {
			require_once('includes/hashFunctions.php');
			$correct = checkBcryptHash($hash, $_POST['password']);
			if ($correct) {
				$_SESSION['loggedInUserId'] = $userid;
				$_SESSION['loggedInNick'] = $displayname;
				$head = <<<HEADEND

		<meta http-equiv="Refresh" content="3; index.php" />
HEADEND;
				$body = <<<BODYEND
			<p>You will be transferred to the front page shortly. Click <a href="index.php">here</a> if you are not redirected within 3 seconds.</p>
BODYEND;
			} else {
				$head = <<<HEADEND

		<meta http-equiv="Refresh" content="3; login.php" />
HEADEND;
				$body = <<<BODYEND
			<p>Incorrect password</p>
			<p>You will be returned to the login form shortly. Click <a href="login.php">here</a> if you are not redirected within 3 seconds.</p>
BODYEND;
			}
		} else {
			$head = <<<HEADEND

		<meta http-equiv="Refresh" content="3; login.php" />
HEADEND;
			$body = <<<BODYEND
			<p>Incorrect email address</p>
			<p>You will be returned to the login form shortly. Click <a href="login.php">here</a> if you are not redirected within 3 seconds.</p>
BODYEND;
		}
	}
	$ps->close();
	if ($correct && isset($_POST["persistent"])) {
		require_once('includes/loginFunctions.php');
		createNewCookie($con);
	}
	$con->close();

	require 'includes/pageTemplate.php';
}

if (count($_POST) > 0) {
	query();
	return;
}

define("allowEntry", true);
session_start();
if (!isset($_SESSION['loggedInUserId']) && isset($_COOKIE['auth'])) {
	require_once('includes/loginFunctions.php');
	loadCookie();
}
if (isset($_SESSION['loggedInUserId']))
	require_once('includes/hackingAttempt.php');
$title = 'Login - thePRAYERwall';
$head = <<<HEADEND

		<link rel="stylesheet" type="text/css" href="formpage.css">
HEADEND;
$body = <<<BODYEND
			<form method="post" action="login.php">
				<fieldset>
					<legend>Login info:</legend>

					<label for="email">Email Address:</label>
					<p class="hint" id="emailhint"></p>
					<input type="text" id="email" name="email" maxlength="254">
					<br style="clear: both">

					<label for="password">Password:</label>
					<p class="hint" id="passwordhint"></p>
					<input type="password" id="password" name="password" maxlength="32">
					<br style="clear: both">

					<label for="persistent">Persistent:</label>
					<p class="hint" id="persistenthint"></p>
					<input type="checkbox" id="persistent" name="persistent">
					<br style="clear: both">
				</fieldset>
				<input type="submit" id="submit" name="login" value="Login">
			</form>
BODYEND;

require 'includes/pageTemplate.php';
?>
