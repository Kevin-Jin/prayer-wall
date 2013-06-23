<?php
function namecheck() {
	if (!array_key_exists('checkname', $_GET))
		//TODO: hacking attempt
		return;

	echo '';
}

function commit() {
	if (!array_key_exists('email', $_POST) || !array_key_exists('password', $_POST) || !array_key_exists('nick', $_POST))
		//TODO: hacking attempt
		return;

	$title = 'Register - thePRAYERwall';
	$head = '';
	$body = <<<BODYEND
			<p>You will be transferred to the front page shortly. Click <a href="index.php">here</a> if you are not redirected within 5 seconds.</p>
BODYEND;

	require 'pageTemplate.php';
}

if (count($_GET) > 0) {
	namecheck();
	return;
}

if (count($_POST) > 0) {
	commit();
	return;
}

$title = 'Register - thePRAYERwall';
$head = <<<HEADEND

		<link rel="stylesheet" type="text/css" href="register.css">
		<script type="text/javascript" src="register.js"></script>
HEADEND;
$body = <<<BODYEND
			<form method="post" action="register.php">
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
				</fieldset>
				<fieldset>
					<legend>Display info:</legend>

					<label for="nick">Nickname:</label>
					<p class="hint" id="nickhint"></p>
					<input type="text" id="nick" name="nick" maxlength="20">
					<br style="clear: both">
				</fieldset>
				<input type="submit" id="regsubmit" name="register" value="Register">
			</form>
BODYEND;

require 'pageTemplate.php';
?>