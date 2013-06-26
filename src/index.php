<?php
function fetchPosts(&$lastLoadedPost) {
	require_once('includes/databaseManager.php');
	require_once('includes/config.php');

	$con = makeDatabaseConnection();
	$ps = $con->prepare("SELECT `postid`,`posttime`,`message`,`poster` FROM `posts` WHERE `postid` < ? ORDER BY `postid` DESC LIMIT ?");
	$upperbound = isset($lastLoadedPost) ? $lastLoadedPost : 0x80000000;
	$ps->bind_param('ii', $upperbound, Config::getInstance()->notesPerPage);
	$str = '';
	if ($ps->execute()) {
		$ps->bind_result($lastLoadedPost, $posttime, $message, $poster);
		for ($i = 0; $ps->fetch(); $i++)
			$str .= '				<div class="note">' . $message . '</div>
';
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
$title = 'Posts - thePRAYERwall';
$head = <<<HEADEND

		<link rel="stylesheet" type="text/css" href="posts.css">
		<script type="text/javascript" src="jquery.isotope.min.js"></script>
		<script type="text/javascript" src="posts.js"></script>
HEADEND;
$body = <<<BODYEND
			<div class="board">

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