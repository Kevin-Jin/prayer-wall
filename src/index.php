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
			$str .= '				<li>' . $message . '</li>
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
	echo "<a id=\"#nextpagelink\" href=\"index.php?start={$_GET["start"]}&scroll\"></a>";
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
		<script type="text/javascript" src="masonry.pkgd.min.js"></script>
		<script type="text/javascript" src="posts.js"></script>
HEADEND;
$body = <<<BODYEND
			<ul class="board">

BODYEND;
$body .= fetchPosts($_GET['start']);
$body .= <<<BODYEND
			</ul>
BODYEND;
if ($_GET['start'] !== -1) {
	$body .= <<<BODYEND

			<form method="get" action="index.php"><input type="hidden" name="start" value="{$_GET['start']}"><input id="nextpage" type="submit" value="Next page"></form>
BODYEND;
}

require 'includes/pageTemplate.php';
?>