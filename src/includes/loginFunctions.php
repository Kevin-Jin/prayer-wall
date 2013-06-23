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
	$ps = $con->prepare("SELECT `userid`,`tokenhash` FROM `cookies` WHERE `uniqueid` = ?");
	$ps->bind_param('i', $uid);
	if ($ps->execute()) {
		$rs = $ps->get_result();
		if ($array = $rs->fetch_array()) {
			require_once('hashFunctions.php');
			$userid = $array[0];
			$correct = checkBcryptHash($array[1], $token);
		}
		$rs->close();
	}
	$ps->close();

	$ps = $con->prepare("DELETE FROM `cookies` WHERE `uniqueid` = ?");
	$ps->bind_param('i', $uid);
	$ps->execute();
	$ps->close();

	if ($correct) {
		$_SESSION['loggedInUserId'] = $userid;
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