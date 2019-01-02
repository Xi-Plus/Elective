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
	switch ($_REQUEST["action"]) {
		case 'checklogin':
			$res = $U;
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
		
		case 'search':
			require(__DIR__.'/func/Elective.php');
			$res = [
				"result"=> (object)getSearchResult($_REQUEST["day"], $_REQUEST["period"])
			];
			break;
		
		case 'getelective':
			if (!$U["islogin"]) {
				$res = [
					"result"=> "not_login"
				];
				break;
			}
			if ($U["accttype"] != "student") {
				$res = [
					"result"=> "no_permission"
				];
				break;
			}

			require(__DIR__.'/func/Elective.php');
			$res = [
				"result"=> "ok",
				"data"=> (object)getElective()
			];
			break;

		case 'getcalendar':
			if (!$U["islogin"]) {
				$res = [
					"result"=> "not_login"
				];
				break;
			}
			if ($U["accttype"] != "student") {
				$res = [
					"result"=> "no_permission"
				];
				break;
			}

			require(__DIR__.'/func/Elective.php');
			$res = [
				"result"=> "ok",
				"data"=> (object)getCalendar()
			];
			break;

		case 'elective':
			if (!$U["islogin"]) {
				$res = [
					"result"=> "not_login"
				];
				break;
			}
			if ($U["accttype"] != "student") {
				$res = [
					"result"=> "no_permission"
				];
				break;
			}

			require(__DIR__.'/func/Elective.php');
			$res = Elective($_REQUEST["classid"]);
			break;

		case 'unelective':
			if (!$U["islogin"]) {
				$res = [
					"result"=> "not_login"
				];
				break;
			}
			if ($U["accttype"] != "student") {
				$res = [
					"result"=> "no_permission"
				];
				break;
			}

			require(__DIR__.'/func/Elective.php');
			$res = Unelective($_REQUEST["classid"]);
			break;
	}
}

logging("res:".json_encode($res));

echo json_encode($res);
