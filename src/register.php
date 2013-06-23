<?php
function commit() {
	$title = 'About - thePRAYERwall';
	$head = '';
	$body = <<<BODYEND
			<p>You will be transferred to the front page shortly. Click <a href="index.php">here</a> if you are not redirected within 5 seconds.</p>
BODYEND;

	require 'pageTemplate.php';
}

if (true) {
	commit();
	return;
}

$title = 'About - thePRAYERwall';
$head = '';
$body = <<<BODYEND
			<p>Insert text here.</p>
BODYEND;

require 'pageTemplate.php';
?>