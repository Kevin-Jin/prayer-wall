<?php
if (!defined("allowEntry"))
	require_once('hackingAttempt.php');

function loadCookie() {
	$params = explode(':', $_COOKIE['auth']);
	$uid = intval($params[0]);
	$token = $params[1];
	$correct = false;

	require_once('databaseManager.php');
	$con = makeDatabaseConnection();
	$ps = $con->prepare("SELECT `u`.`userid`,`displayname`,`tokenhash` FROM `cookies` `c` LEFT JOIN `users` `u` ON `u`.`userid` = `c`.`userid` WHERE `uniqueid` = ?");
	$ps->bind_param('i', $uid);
	if ($ps->execute()) {
		$ps->bind_result($userid, $displayname, $hash);
		if ($ps->fetch()) {
			require_once('hashFunctions.php');
			$correct = checkBcryptHash($hash, $token);
		}
	}
	$ps->close();

	$ps = $con->prepare("DELETE FROM `cookies` WHERE `uniqueid` = ?");
	$ps->bind_param('i', $uid);
	$ps->execute();
	$ps->close();

	if ($correct) {
		$_SESSION['loggedInUserId'] = $userid;
		$_SESSION['loggedInNick'] = htmlspecialchars($displayname, ENT_COMPAT | ENT_HTML401, 'UTF-8');
		createNewCookie($con);
	}

	$con->close();
}

function createNewCookie($con) {
	$newToken = bin2hex(openssl_random_pseudo_bytes(16));
	$tokenHash = hashBcrypt($newToken);
	$ps = $con->prepare("INSERT INTO `cookies` (`userid`,`tokenhash`) VALUES (?,?)");
	$ps->bind_param('is', $_SESSION['loggedInUserId'], $tokenHash);
	$ps->execute();
	$uid = $con->insert_id;
	$ps->close();

	setcookie('auth', implode(':', array($uid, $newToken)), time() + 60 * 60 * 24 * 15, '', '', isset($_SERVER["HTTPS"]), true);
}

function destroyCookie() {
	$params = explode(':', $_COOKIE['auth']);
	$uid = intval($params[0]);

	require_once('databaseManager.php');
	$con = makeDatabaseConnection();
	$ps = $con->prepare("DELETE FROM `cookies` WHERE `uniqueid` = ?");
	$ps->bind_param('i', $uid);
	$ps->execute();
	$ps->close();
	$con->close();

	setcookie('auth', '', time() - 60 * 60, '', '', isset($_SERVER["HTTPS"]), true);
}
?>