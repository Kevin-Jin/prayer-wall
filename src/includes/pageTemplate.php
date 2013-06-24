<?php
if (!defined("allowEntry"))
	require_once('hackingAttempt.php');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN"
   "http://www.w3.org/TR/html4/strict.dtd">
<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
		<link rel="stylesheet" type="text/css" href="main.css">
		<script type="text/javascript" src="//ajax.googleapis.com/ajax/libs/jquery/1.10.1/jquery.min.js"></script>
		<title><?php echo $title; ?></title><?php echo $head; ?>

	</head>
	<body>
		<div id="top">
			<h1 id="logo">thePRAYERwall</h1>
<?php
if (!isset($_SESSION['loggedInUserId'])) {
	echo <<<LOGINFORMEND
			<form id="user" method="post" action="login.php">
				<table id="login_form">
					<tr>
						<td><label for="usr">Email Address</label></td>
						<td><label for="pwd">Password</label></td>
						<td><a href="register.php"><input type="button" name="signup" value="Register" class="submit" id="register"></a></td>
					</tr>
					<tr>
						<td><input type="text" id="usr" name="email" maxlength="254"></td>
						<td><input type="password" id="pwd" name="password" maxlength="32"></td>
						<td><input type="submit" name="signin" value="Login" class="submit"></td>
					</tr>
					<tr>
						<td><input type="checkbox" id="remember" name="persistent" value="1"><label for="remember">Remember me</label></td>
						<td><a href="#">I forgot my password</a></td>
					</tr>
				</table>
			</form>

LOGINFORMEND;
} else {
	$name = $_SESSION['loggedInNick'];
	echo <<<PROFILEEND
			<div id="user">
				<p>Welcome back, $name</p>
				<p><a href="logout.php">Log out</a>
			</div>

PROFILEEND;
}
?>
			<br class="clearfloat">
			<ul id="nav">
				<li><a href="index.php">Home</a></li>
				<li><a href="//bit.ly/theprayerwall">the ORIGINAL PRAYER wall</a></li>
				<li><a href="#">Prayer Boards</a></li>
				<li><a href="#">Prayer Resources</a></li>
				<li><a href="#">Blog</a></li>
				<li><a href="about.php">About</a></li>
			</ul>
		</div>
		<div id="middle">
<?php echo $body; ?>

		</div>
		<div id="bottom">
			<p>Copyright &copy; 2013 Kevin Jin. All Rights Reserved</p>
			<div id="valid">
				<a href="http://validator.w3.org/check?uri=referer"><img src="http://www.w3.org/Icons/valid-html401" alt="Valid HTML 4.01 Strict" height="31" width="88"></a>
				<a href="http://jigsaw.w3.org/css-validator/check/referer"><img src="http://jigsaw.w3.org/css-validator/images/vcss" alt="Valid CSS3" height="31" width="88"></a>
			</div>
		</div>
	</body>
</html>
