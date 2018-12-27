<?php

if (!isset($_COOKIE[$C["cookiename"]])) {
	$U["islogin"] = false;
} else {
	$sth = $G["db"]->prepare('SELECT * FROM `login_session` WHERE `cookie` = :cookie');
	$sth->bindValue(":cookie", $_COOKIE[$C["cookiename"]]);
	$sth->execute();
	$cookie = $sth->fetch(PDO::FETCH_ASSOC);
	if ($cookie === false) {
		$U["islogin"] = false;
	} else {
		if ($cookie["type"] == 0) {
			$sth = $G["db"]->prepare('SELECT `stuid` AS `account`, `name` FROM `student` WHERE `stuid` = :stuid');
			$sth->bindValue(":stuid", $cookie["account"]);
			$sth->execute();
			$U = $sth->fetch(PDO::FETCH_ASSOC);
			$U["islogin"] = true;
			$U["accttype"] = "student";
		} else if ($cookie["type"] == 1) {
			$sth = $G["db"]->prepare('SELECT `account`, `name` FROM `admin` WHERE `account` = :account');
			$sth->bindValue(":account", $cookie["account"]);
			$sth->execute();
			$U = $sth->fetch(PDO::FETCH_ASSOC);
			$U["islogin"] = true;
			$U["accttype"] = "admin";
		} else {
			$U["islogin"] = false;
		}
	}
}
