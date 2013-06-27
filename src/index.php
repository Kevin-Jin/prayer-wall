<?php
function constructNoteElement($title, $message, $author, $timestamp) {
	$str = '				<div class="note">';
	if ($title)
		$str .= '<h1>' . $title . '</h1>';
	$str .= '<p>' . $message . '</p><h2>';
	if (!$author)
		$str .= 'Anonymous';
	else
		$str .= $author;
	$str .= '</h2></div>
';
	return $str;
}

function fetchPosts(&$lastLoadedPost) {
	require_once('includes/databaseManager.php');
	require_once('includes/config.php');

	$con = makeDatabaseConnection();
	$ps = $con->prepare("SELECT `postid`,`posttime`,`title`,`message`,`displayname` FROM `posts` `p` LEFT JOIN `users` `u` on `poster` = `userid` WHERE `postid` < ? ORDER BY `postid` DESC LIMIT ?");
	$upperbound = isset($lastLoadedPost) ? $lastLoadedPost : 0x80000000;
	$ps->bind_param('ii', $upperbound, Config::getInstance()->notesPerPage);
	$str = '';
	if ($ps->execute()) {
		$ps->bind_result($lastLoadedPost, $posttime, $title, $message, $poster);
		for ($i = 0; $ps->fetch(); $i++)
			$str .= constructNoteElement($title, $message, $poster, $posttime);
	}
	$ps->close();
	if ($i < Config::getInstance()->notesPerPage) {
		$lastLoadedPost = -1;
	} else {
		$ps = $con->prepare("SELECT EXISTS(SELECT 1 FROM `posts` WHERE `postid` < ?)");
		$ps->bind_param('i', $lastLoadedPost);
		if ($ps->execute()) {
			$ps->bind_result($more);
			$ps->fetch();
			if (!$more)
				$lastLoadedPost = -1;
		}
		$ps->close();
	}
	$con->close();
	return $str;
}

if (isset($_GET['scroll'])) {
	define("allowEntry", true);
	echo fetchPosts($_GET['start']);
	echo "<input id=\"start\" type=\"hidden\" name=\"start\" value=\"{$_GET['start']}\">";
	return;
}

define("allowEntry", true);
session_start();
if (!isset($_SESSION['loggedInUserId']) && isset($_COOKIE['auth'])) {
	require_once('includes/loginFunctions.php');
	loadCookie();
}
if (isset($_POST['newtitle']) && isset($_POST['newnote'])) {
	$_POST['newtitle'] = str_replace(array("\r\n", "\r", "\n"), "<br />", htmlspecialchars(trim($_POST['newtitle']), ENT_COMPAT | ENT_XHTML, 'UTF-8'));
	$_POST['newnote'] = str_replace(array("\r\n", "\r", "\n"), "<br />", htmlspecialchars(trim($_POST['newnote']), ENT_COMPAT | ENT_XHTML, 'UTF-8'));
	if ($_POST['newnote']) {
		$now = time();

		require_once('includes/databaseManager.php');
		$con = makeDatabaseConnection();
		$ps = $con->prepare("INSERT INTO `posts` (`posttime`,`title`,`message`,`poster`) VALUES (?,?,?,?)");
		$ps->bind_param('issi', $now, $_POST['newtitle'], $_POST['newnote'], $_SESSION['loggedInUserId']);
		$ps->execute();
		$ps->close();
		$con->close();
	} else {
		$error = 'Your message was not posted because it was empty.';
	}

	if (isset($_POST['echo'])) {
		echo constructNoteElement($_POST['newtitle'], $_POST['newnote'], isset($_SESSION['loggedInNick']) ? $_SESSION['loggedInNick'] : null, $now);
		return;
	}
}
$title = 'Posts - thePRAYERwall';
$head = <<<HEADEND

		<link rel="stylesheet" type="text/css" href="posts.css">
		<script type="text/javascript" src="jquery.isotope.min.js"></script>
		<script type="text/javascript" src="jquery.autosize-min.js"></script>
		<script type="text/javascript" src="posts.js"></script>
HEADEND;
if (isset($error))
	$body = '			<div id="error"><p>' . $error . '</p></div>
';
else
	$body = '';
$author = isset($_SESSION['loggedInUserId']) ? $_SESSION['loggedInNick'] : "Anonymous";
$body .= <<<BODYEND
			<div class="board">
				<form id="compose" method="post" action="index.php"><div class="note" id="composecontainer">
					<input id="newtitle" type="text" name="newtitle" placeholder="Title..." maxlength="24">
					<textarea id="newmessage" name="newnote" rows="7" cols="1" placeholder="Write new note..."></textarea>
					<input id="makepost" type="submit" value="Submit as {$author}">
				</div></form>

BODYEND;
$body .= fetchPosts($_GET['start']);
$body .= <<<BODYEND
			</div>
BODYEND;
if ($_GET['start'] !== -1) {
	$body .= <<<BODYEND

			<form id="nextpageform" method="get" action="index.php"><div><input id="start" type="hidden" name="start" value="{$_GET['start']}"><input id="nextpage" type="submit" value="Next page"></div></form>
BODYEND;
}

require 'includes/pageTemplate.php';
?>