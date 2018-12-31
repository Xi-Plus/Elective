<?php
require(__DIR__.'/config/config.php');
ini_set("display_errors", 0);


function logging($text) {
	global $C, $G;
	$sth = $G["db"]->prepare('INSERT INTO `log` (`text`) VALUES (:text)');
	$sth->bindValue(":text", $text);
	$sth->execute();
}

logging("post:".json_encode($_POST));

logging("get:".json_encode($_GET));

logging("cookies:".json_encode($_COOKIE));

$res = [
	"result"=> "failed"
];
if (isset($_REQUEST["action"])) {
	logging("action:".json_encode($_REQUEST["action"]));
	switch ($_REQUEST["action"]) {
		case 'checklogin':
			$res = [
				"result"=> $U["islogin"]
			];
			break;

		case 'login':
			require(__DIR__.'/func/Login.php');
			$res = [
				"result"=> Login($_REQUEST["type"], $_REQUEST["account"], $_REQUEST["password"])
			];
			break;
		
		case 'logout':
			require(__DIR__.'/func/Login.php');
			$res = [
				"result"=> Logout()
			];
			break;
	}
}

logging("res:".json_encode($res));

echo json_encode($res);
