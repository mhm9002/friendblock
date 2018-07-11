<?php
require_once (dirname(__FILE__)).'/includes/boot.php';
/*
$subject = 'test';
$body = 'this is a SMTP test';

$mail =cr_sendmail($_GET['to'],$_GET['toname'],$subject, $body);

if ($mail){
	echo 'test email sent';
}


var_dump($mail);
*/

//thumbnailer(64);

//echo phpinfo();
/*
$functions = array();
$path = DIR_INC;
define_dir($path, $functions);
reference_dir($path, $functions);
echo
	"<table>" .
		"<tr>" .
			"<th>Name</th>" .
			"<th>Defined</th>" .
			"<th>Referenced</th>" .
		"</tr>";
foreach ($functions as $name => $value) {
	echo
		"<tr>" . 
			"<td>" . htmlentities($name) . "</td>" .
			"<td>" . (isset($value[0]) ? count($value[0]) : "-") . "</td>" .
			"<td>" . (isset($value[1]) ? count($value[1]) : "-") . "</td>" .
		"</tr>";
}
echo "</table>";

function define_dir($path, &$functions) {
	if ($dir = opendir($path)) {
		while (($file = readdir($dir)) !== false) {
			if (substr($file, 0, 1) == ".") continue;
			if (is_dir($path . "/" . $file)) {
				define_dir($path . "/" . $file, $functions);
			} else {
				if (substr($file, - 4, 4) != ".php") continue;
				define_file($path . "/" . $file, $functions);
			}
		}
	}       
}
function define_file($path, &$functions) {
	$tokens = token_get_all(file_get_contents($path));
	for ($i = 0; $i < count($tokens); $i++) {
		$token = $tokens[$i];
		if (is_array($token)) {
			if ($token[0] != T_FUNCTION) continue;
			$i++;
			$token = $tokens[$i];
			if ($token[0] != T_WHITESPACE) die("T_WHITESPACE");
			$i++;
			$token = $tokens[$i];
			if ($token[0] != T_STRING) die("T_STRING");
			$functions[$token[1]][0][] = array($path, $token[2]);
		}
	}
}
function reference_dir($path, &$functions) {
	if ($dir = opendir($path)) {
		while (($file = readdir($dir)) !== false) {
			if (substr($file, 0, 1) == ".") continue;
			if (is_dir($path . "/" . $file)) {
				reference_dir($path . "/" . $file, $functions);
			} else {
				if (substr($file, - 4, 4) != ".php") continue;
				reference_file($path . "/" . $file, $functions);
			}
		}
	}       
}
function reference_file($path, &$functions) {
	$tokens = token_get_all(file_get_contents($path));
	for ($i = 0; $i < count($tokens); $i++) {
		$token = $tokens[$i];
		if (is_array($token)) {
			if ($token[0] != T_STRING) continue;
			if ($tokens[$i + 1] != "(") continue;
			$functions[$token[1]][1][] = array($path, $token[2]);
		}
	}
}
*/

//require_once (dirname(__FILE__)).'/inactive_users.php';

//var_dump($_SERVER);

cr_update_all_users_popularity();

?>