var emailOk;
var passwordOk;
var nickOk;

function updateSubmitButton() {
	if (!emailOk || !passwordOk || !nickOk)
		$('#regsubmit').attr('disabled', 'disabled');
	else
		$('#regsubmit').removeAttr('disabled');
}

function emailValid(address) {
	if (address.length > 254)
		return false;
	var localpart = true;
	var quotedstring = false;
	var escape = false;
	for (var i = 0; i < address.length; i++) {
		var ch = address.charAt(i);
		if (localpart) {
			//simple cases
			if (
					//uppercase letters, lowercase letters, digits
					ch >= 'A' && ch <= 'Z' || ch >= 'a' && ch <= 'z' || ch >= '0' && ch <= '9'
					//!#$%&'*+-/=?^_`{|}~
					|| ch === '!' || ch >= '#' && ch <= '\'' || ch === '*' || ch === '+' || ch === '-' || ch === '/' || ch === '=' || ch === '?' || ch >= '^' && ch <= '`' || ch >= '{' && ch <= '~'
					//periods are allowed if not at start of local part and are not consecutive
					|| ch === '.' && i !== 0 && address.charAt(i - 1) !== '.'
			) {
				escape = false; //in case ch is preceded by a backslash
				continue;
			}

			if (ch === '"') {
				if (!escape) {
					if (!quotedstring && i !== 0 && address.charAt(i - 1) !== '.') //don't start quoted string if not at beginning or after a period
						return false;
					if (quotedstring && i !== address.length - 1 && address.charAt(i + 1) !== '@' && address.charAt(i + 1) !== '.') //don't end quoted string if not at end of local part or before a period
						return false;
					quotedstring = !quotedstring;
				}
				escape = false; //in case ch is preceded by a backslash
			} else if (ch === '@') {
				if (!quotedstring && !escape) {
					if (address.charAt(i - 1) === '.') //periods are not allowed at end of local part
						return false;
					if (i > 64 || address.length - i - 1 > 255) //local part or domain part exceeds max length
						return false;
					localpart = false;
				}
				escape = false; //in case ch is preceded by a backslash
			} else if (ch === '\\') {
				//double consecutive backslashes means unescaped single backslash, so set escape = false if we're already escaped
				//otherwise just set escape = true and escape the next character
				escape = !escape;
			} else if (escape) {
				escape = false; //backslash only escapes one character
			} else if (!quotedstring) { //always allow any quoted/escaped characters
				return false;
			}
		} else {
			//uppercase letters, lowercase letters, digits, hyphen, period
			if (ch >= 'A' && ch <= 'Z' || ch >= 'a' && ch <= 'z' || ch >= '0' && ch <= '9' || ch === '-' || ch === '.')
				continue;
			//TODO: handle IP address literals, hyphen restrictions at start/end
			return false;
		}
	}
	//TODO: check min lengths of local and domain parts and whether domain part has at least one period
	if (localpart) //no domain part
		return false;
	return true;
}

function updateEmailHint(e) {
	emailOk = false;
	updateSubmitButton();
	if ($(this).val().length === 0) {
		$("#emailhint").css("color", "black").html("This will be the ID you use to login");
		return;
	} else if (!emailValid($(this).val())) {
		$("#emailhint").css("color", "red").html("✘ Invalid email address");
		return;
	}

	$.get("register.php?checkemail=" + $(this).val(), function(resp) {
		if (resp !== "") { //we'll return an empty string for no conflicts
			emailOk = false;
			updateSubmitButton();
			$("#emailhint").css("color", "red").html("✘ " + resp + " is already in use");
		} else {
			emailOk = true;
			updateSubmitButton();
			$("#emailhint").css("color", "green").html("✔");
		}
	});
}

function passwordProblem(pwd) {
	if (pwd.length < 10)
		return "Must be at least 10 characters long";
	if (pwd.length > 32)
		return "Must be no more than 32 characters long";
	for (var i = pwd.length - 1; i >= 0; --i)
		if (pwd.charAt(i) === ' ')
			return "You may not have a space in your password";
		else if (pwd.charAt(i) < ' ' || pwd.charAt(i) > '~')
			return "Only A-Z, a-z, 0-9, !, \", #, $, %, &, ', (, ), ,, -, ., :, ;, <, =, >, ?, @, [, \\, ], ^, _, `, {, |, }, ~";
	return null;
}

function updatePasswordHint(e) {
	if ($(this).val().length === 0) {
		passwordOk = false;
		updateSubmitButton();
		$("#passwordhint").css("color", "black").html("Choose something unguessable but rememberable");
		return;
	}
	var prob = passwordProblem($(this).val());
	if (prob !== null) {
		passwordOk = false;
		updateSubmitButton();
		$("#passwordhint").css("color", "red").html("✘ " + prob);
	} else {
		passwordOk = true;
		updateSubmitButton();
		$("#passwordhint").css("color", "green").html("✔");
	}
}

function nickProblem(nick) {
	if (nick.length < 2)
		return "Must be at least 2 characters long";
	if (nick.length > 20)
		return "Must be no more than 20 characters long";
	for (var i = nick.length - 1; i >= 0; --i)
		if (nick.charAt(i) < ' ' || nick.charAt(i) > '~')
			return "Only A-Z, a-z, 0-9, !, \", #, $, %, &, ', (, ), ,, -, ., :, ;, <, =, >, ?, @, [, \\, ], ^, _, `, {, |, }, ~";
	return null;
}

function updateNickHint(e) {
	nickOk = false;
	updateSubmitButton();
	if ($(this).val().length === 0) {
		$("#nickhint").css("color", "black").html("This is how you will be identified in your posts");
		return;
	}
	var prob = nickProblem($(this).val());
	if (prob !== null) {
		$("#nickhint").css("color", "red").html("✘ " + prob);
		return;
	}

	$.get("register.php?checknick=" + $(this).val(), function(resp) {
		if (resp !== "") { //we'll return an empty string for no conflicts
			nickOk = false;
			updateSubmitButton();
			$("#nickhint").css("color", "red").html("✘ " + resp + " is already in use");
		} else {
			nickOk = true;
			updateSubmitButton();
			$("#nickhint").css("color", "green").html("✔");
		}
	});
}

$(document).ready(function() {
	emailOk = passwordOk = nickOk = false;
	updateSubmitButton();

	$("#email").removeAttr("maxlength");
	$("#password").removeAttr("maxlength");
	$("#nick").removeAttr("maxlength");

	//TODO: disable paste in Opera
	$('#email, #password, #nick').on('cut paste', function(e) {
		//cut may delete character count to below requirement
		//paste may insert character count to above limit or introduce illegal characters
		//so just don't let either happen
		(e.preventDefault) ? e.preventDefault() : e.returnValue = false;
	});
	$('#email').on('click', updateEmailHint).on('keyup', updateEmailHint);
	$('#password').on('click', updatePasswordHint).on('keyup', updatePasswordHint);
	$('#nick').on('click', updateNickHint).on('keyup', updateNickHint);
});