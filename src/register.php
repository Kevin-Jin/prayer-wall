<?php
function emailExists($email) {
	require_once('includes/databaseManager.php');
	$con = makeDatabaseConnection();
	$ps = $con->prepare("SELECT 1 FROM `users` WHERE `email` = ?");
	$ps->bind_param('s', $email);
	$exists = !$ps->execute() || $ps->fetch();
	$ps->close();
	$con->close();
	return $exists;
}

function nickExists($nick) {
	if (strcasecmp($nick, 'Anonymous') === 0)
		return true;

	require_once('includes/databaseManager.php');
	$con = makeDatabaseConnection();
	$ps = $con->prepare("SELECT 1 FROM `users` WHERE `displayname` = ?");
	$ps->bind_param('s', $nick);
	$exists = !$ps->execute() || $ps->fetch();
	$ps->close();
	$con->close();
	return $exists;
}

function idcheck() {
	$checknick = false;
	if (count($_GET) !== 1 || !($checkemail = isset($_GET['checkemail'])) && !($checknick = isset($_GET['checknick'])))
		require_once('hackingAttempt.php');

	define("allowEntry", true);
	if ($checkemail) {
		if (get_magic_quotes_gpc())  
			$email = stripslashes($_GET['checkemail']);
		else
			$email = $_GET['checkemail'];

		if (emailExists($email))
			echo htmlspecialchars($email, ENT_COMPAT | ENT_HTML401, 'UTF-8');
	}
	if ($checknick) {
		if (get_magic_quotes_gpc())  
			$nick = stripslashes($_GET['checknick']);
		else
			$nick = $_GET['checknick'];

		if (nickExists($nick))
			echo htmlspecialchars($nick, ENT_COMPAT | ENT_HTML401, 'UTF-8');
	}
	echo '';
}

 function emailValid($address) {
	if (strlen($address) > 254)
		return false;
	$localpart = true;
	$quotedstring = false;
	$escape = false;
	for ($i = 0; $i < strlen($address); $i++) {
		$ch = $address[$i];
		if ($localpart) {
			//simple cases
			if (
					//uppercase letters, lowercase letters, digits
					$ch >= 'A' && $ch <= 'Z' || $ch >= 'a' && $ch <= 'z' || $ch >= '0' && $ch <= '9'
					//!#$%&'*+-/=?^_`{|}~
					|| $ch === '!' || $ch >= '#' && $ch <= '\'' || $ch === '*' || $ch === '+' || $ch === '-' || $ch === '/' || $ch === '=' || $ch === '?' || $ch >= '^' && $ch <= '`' || $ch >= '{' && $ch <= '~'
					//periods are allowed if not at start of local part and are not consecutive
					|| $ch === '.' && $i !== 0 && $address[$i - 1] !== '.'
			) {
				$escape = false; //in case ch is preceded by a backslash
				continue;
			}

			if ($ch === '"') {
				if (!$escape) {
					if (!$quotedstring && $i !== 0 && $address[$i - 1] !== '.') //don't start quoted string if not at beginning or after a period
						return false;
					if ($quotedstring && $i !== strlen($address) - 1 && $address[$i + 1] !== '@' && $address[$i + 1] !== '.') //don't end quoted string if not at end of local part or before a period
						return false;
					$quotedstring = !$quotedstring;
				}
				$escape = false; //in case ch is preceded by a backslash
			} else if ($ch === '@') {
				if (!$quotedstring && !$escape) {
					if ($address[$i - 1] === '.') //periods are not allowed at end of local part
						return false;
					if ($i > 64 || strlen($address) - $i - 1 > 255) //local part or domain part exceeds max length
						return false;
					$localpart = false;
				}
				$escape = false; //in case ch is preceded by a backslash
			} else if ($ch === '\\') {
				//double consecutive backslashes means unescaped single backslash, so set escape = false if we're already escaped
				//otherwise just set escape = true and escape the next character
				$escape = !$escape;
			} else if ($escape) {
				$escape = false; //backslash only escapes one character
			} else if (!$quotedstring) { //always allow any quoted/escaped characters
				return false;
			}
		} else {
			//uppercase letters, lowercase letters, digits, hyphen, period
			if ($ch >= 'A' && $ch <= 'Z' || $ch >= 'a' && $ch <= 'z' || $ch >= '0' && $ch <= '9' || $ch === '-' || $ch === '.')
				continue;
			//TODO: handle IP address literals, hyphen restrictions at start/end
			return false;
		}
	}
	//TODO: check min lengths of local and domain parts and whether domain part has at least one period
	if ($localpart) //no domain part
		return false;
	return true;
}

function passwordProblem($pwd) {
	if (strlen($pwd) < 10)
		return "Must be at least 10 characters long";
	if (strlen($pwd) > 32)
		return "Must be no more than 32 characters long";
	for ($i = strlen($pwd) - 1; $i >= 0; --$i)
		if ($pwd[$i] === ' ')
			return "You may not have a space in your password";
		else if ($pwd[$i] < ' ' || $pwd[$i] > '~')
			return "Only A-Z, a-z, 0-9, !, \", #, $, %, &, ', (, ), ,, -, ., :, ;, <, =, >, ?, @, [, \\, ], ^, _, `, {, |, }, ~";
	return null;
}

function nickProblem($nick) {
	if (strlen($nick) < 2)
		return "Must be at least 2 characters long";
	if (strlen($nick) > 20)
		return "Must be no more than 20 characters long";
	for ($i = strlen($nick) - 1; $i >= 0; --$i)
		if ($nick[$i] < ' ' || $nick[$i] > '~')
			return "Only A-Z, a-z, 0-9, !, \", #, $, %, &, ', (, ), ,, -, ., :, ;, <, =, >, ?, @, [, \\, ], ^, _, `, {, |, }, ~";
	return null;
}

function makeAccount($email, $password, $nick) {
	require_once('includes/hashFunctions.php');
	$hash = hashBcrypt($password);

	require_once('includes/databaseManager.php');
	$con = makeDatabaseConnection();
	$ps = $con->prepare("INSERT INTO `users` (`email`,`password`,`displayname`) VALUES (?,?,?)");
	$ps->bind_param('sss', $email, $hash, $nick);
	$ps->execute();
	$userid = $con->insert_id;
	$ps->close();
	$con->close();

	return $userid;
}

function commit() {
	if (!isset($_POST['email']) || !isset($_POST['password']) || !isset($_POST['nick']))
		require_once('includes/hackingAttempt.php');

	define("allowEntry", true);
	session_start();
	if (!isset($_SESSION['loggedInUserId']) && isset($_COOKIE['auth'])) {
		require_once('includes/loginFunctions.php');
		loadCookie();
	}
	if (isset($_SESSION['loggedInUserId']))
		require_once('includes/hackingAttempt.php');
	$title = 'Register - thePRAYERwall';
	$head = '';
	$email = $_POST['email'];
	$password = $_POST['password'];
	$nick = $_POST['nick'];
	
	if (get_magic_quotes_gpc()) {
		$email = stripslashes($email);
		$password = stripslashes($password);
		$nick = stripslashes($nick);
	}

	$allOk = true;
	//validate on the server side in case JavaScript is disabled or client is spoofing
	if ($allOk && !emailValid($email)) {
		$allOk = false;
		$head = <<<HEADEND

		<meta http-equiv="Refresh" content="3; register.php" />
HEADEND;
		$body = <<<BODYEND
			<p>Correct your email address. Invalid email address</p>
			<p>You will be returned to the registration form shortly. Click <a href="register.php">here</a> if you are not redirected within 3 seconds.</p>
BODYEND;
	}
	if ($allOk && emailExists($email)) {
		$allOk = false;
		$head = <<<HEADEND

		<meta http-equiv="Refresh" content="3; register.php" />
HEADEND;
		$body = <<<BODYEND
			<p>Correct your email address. $email is already in use</p>
			<p>You will be returned to the registration form shortly. Click <a href="register.php">here</a> if you are not redirected within 3 seconds.</p>
BODYEND;
	}
	$prob = passwordProblem($password);
	if ($allOk && $prob) {
		$allOk = false;
		$head = <<<HEADEND

		<meta http-equiv="Refresh" content="3; register.php" />
HEADEND;
		$body = <<<BODYEND
			<p>Correct your password. $prob</p>
			<p>You will be returned to the registration form shortly. Click <a href="register.php">here</a> if you are not redirected within 3 seconds.</p>
BODYEND;
	}
	$prob = nickProblem($nick);
	if ($allOk && $prob) {
		$allOk = false;
		$head = <<<HEADEND

		<meta http-equiv="Refresh" content="3; register.php" />
HEADEND;
		$body = <<<BODYEND
			<p>Correct your nickname. $prob.</p>
			<p>You will be returned to the registration form shortly. Click <a href="register.php">here</a> if you are not redirected within 3 seconds.</p>
BODYEND;
	}
	if ($allOk && nickExists($nick)) {
		$allOk = false;
		$head = <<<HEADEND

		<meta http-equiv="Refresh" content="3; register.php" />
HEADEND;
		$body = <<<BODYEND
			<p>Correct your nickname. $nick is already in use</p>
			<p>You will be returned to the registration form shortly. Click <a href="register.php">here</a> if you are not redirected within 3 seconds.</p>
BODYEND;
	}
	if ($allOk) {
		$_SESSION['loggedInUserId'] = makeAccount($email, $password, $nick);
		$_SESSION['loggedInNick'] = htmlspecialchars($nick, ENT_COMPAT | ENT_HTML401, 'UTF-8');;
		$head = <<<HEADEND

		<meta http-equiv="Refresh" content="3; index.php" />
HEADEND;
		$body = <<<BODYEND
			<p>You will be transferred to the front page shortly. Click <a href="index.php">here</a> if you are not redirected within 3 seconds.</p>
BODYEND;
	}

	require 'includes/pageTemplate.php';
}

if (count($_GET) > 0) {
	idcheck();
	return;
}

if (count($_POST) > 0) {
	commit();
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
$title = 'Register - thePRAYERwall';
$head = <<<HEADEND

		<link rel="stylesheet" type="text/css" href="formpage.css">
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
				<div><input type="submit" id="submit" name="register" value="Register"></div>
			</form>
BODYEND;

require 'includes/pageTemplate.php';
?>