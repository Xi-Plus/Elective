<?php

function Login($type="", $account="", $password="") {
	global $U, $C, $G;

	if (isset($account)) {
		if (in_array($type, ["student", "admin"])) {
			if ($type == "student") {
				$sth = $G["db"]->prepare('SELECT `stuid` AS `account`, `password`, `name` FROM `student` WHERE `stuid` = :stuid');
				$sth->bindValue(":stuid", $account);
			} else if ($type == "admin") {
				$sth = $G["db"]->prepare('SELECT `account`, `password`, `name` FROM `admin` WHERE `account` = :account');
				$sth->bindValue(":account", $account);
			}
			$sth->execute();
			$account = $sth->fetch(PDO::FETCH_ASSOC);
			if ($account !== false && password_verify($password, $account["password"])) {
				$cookie = md5(uniqid(rand(),true));
				$sth = $G["db"]->prepare('INSERT INTO `login_session` (`type`, `account`, `cookie`) VALUES (:type, :account, :cookie)');
				if ($type == "student") {
					$sth->bindValue(":type", 0);
				} else if ($type == "admin") {
					$sth->bindValue(":type", 1);
				}
				$sth->bindValue(":account", $account["account"]);
				$sth->bindValue(":cookie", $cookie);
				$sth->execute();
				setcookie($C["cookiename"], $cookie, time()+$C["cookieexpire"], $C["path"]);
				$U = $account;
				$U["islogin"] = true;
				$U["accttype"] = $type;
				$showform = false;
				return "success";
			} else {
				return "failed";
			}
		} else {
			return "wrong_type";
		}
	} else {
		return "failed";
	}
}

function Logout() {
	global $U, $C, $G;

	if ($U["islogin"]) {
		$sth = $G["db"]->prepare('DELETE FROM `login_session` WHERE `cookie` = :cookie');
		$sth->bindValue(":cookie", $_COOKIE[$C["cookiename"]]);
		$sth->execute();
		setcookie($C["cookiename"], "", time(), $C["path"]);
	}
	$U["islogin"] = false;
	$showform = false;
	
	return "success";
}
