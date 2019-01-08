<?php

function getSearchResult($day="", $period="") {
	global $C, $G, $U;

	if ($U["accttype"] == "student") {
		$elective = getElective();
		$calendar = getCalendar();
	}

	$query = 'SELECT * FROM `class_time` WHERE `classid` IN ( SELECT `classid` FROM `class_time` WHERE 1 ';
	if ($day != "" && is_numeric($day)) {
		$query .= "AND `day` = :day ";
	}
	if ($period != "" && is_numeric($period)) {
		$query .= "AND `period1` <= :period AND :period <= `period2` ";
	}
	$query .= ") ORDER BY `classid` ASC, `day` ASC, `period1` ASC ";
	$sth = $G["db"]->prepare($query);
	if ($day != "" && is_numeric($day)) {
		$sth->bindValue(":day", $day);
	}
	if ($period != "" && is_numeric($period)) {
		$sth->bindValue(":period", $period);
	}
	$sth->execute();
	$row = $sth->fetchAll(PDO::FETCH_ASSOC);

	$result = [];
	foreach ($row as $temp) {
		if (!isset($result[$temp["classid"]])) {
			$result[$temp["classid"]] = ["time"=>[]];
		}
		$result[$temp["classid"]]["time"] []= $temp;
	}

	$query = 'SELECT * FROM `class` WHERE `classid` IN ( SELECT `classid` FROM `class_time` WHERE 1 ';
	if ($day != "" && is_numeric($day)) {
		$query .= "AND `day` = :day ";
	}
	if ($period != "" && is_numeric($period)) {
		$query .= "AND `period1` <= :period AND :period <= `period2` ";
	}
	$query .= ") ORDER BY `classid` ";
	$sth = $G["db"]->prepare($query);
	if ($day != "" && is_numeric($day)) {
		$sth->bindValue(":day", $day);
	}
	if ($period != "" && is_numeric($period)) {
		$sth->bindValue(":period", $period);
	}
	$sth->execute();
	$row = $sth->fetchAll(PDO::FETCH_ASSOC);
	foreach ($row as $temp) {
		$result[$temp["classid"]] += $temp;
		$result[$temp["classid"]]["timestr"] = "";
		if ($U["accttype"] == "student") {
			$result[$temp["classid"]]["elective"] = "ok";
		}
		foreach ($result[$temp["classid"]]["time"] as $time) {
			if ($time["period1"] == $time["period2"]) {
				$result[$temp["classid"]]["timestr"] .= sprintf("(%s) %s ", $C["day"][$time["day"]], $time["period1"]);
			} else {
				$result[$temp["classid"]]["timestr"] .= sprintf("(%s) %s-%s ", $C["day"][$time["day"]], $time["period1"], $time["period2"]);
			}
			if ($U["accttype"] == "student") {
				for ($period=$time["period1"]; $period <= $time["period2"]; $period++) { 
					if (isset($calendar[$time["day"]][$period])) {
						$result[$temp["classid"]]["elective"] = "collision";
					}
				}
			}
		}
		if ($U["accttype"] == "student" && isset($elective[$temp["classid"]])) {
			$result[$temp["classid"]]["elective"] = "selected";
		}
	}

	return $result;
}

function getElective() {
	global $C, $G, $U;

	$sth = $G["db"]->prepare('SELECT * FROM ( SELECT * FROM `elective` WHERE `stuid` = :stuid ORDER BY `classid` ) `elective` LEFT JOIN `class` ON `elective`.`classid` = `class`.`classid`');
	$sth->bindValue(":stuid", $U["account"]);
	$sth->execute();
	$row = $sth->fetchAll(PDO::FETCH_ASSOC);
	$result = [];
	foreach ($row as $temp) {
		$result[$temp["classid"]] = $temp;
		$result[$temp["classid"]]["time"] = [];
	}

	$sth = $G["db"]->prepare('SELECT * FROM `class_time` WHERE `classid` IN ( SELECT `elective`.`classid` FROM (SELECT * FROM `elective` WHERE `stuid` = :stuid ORDER BY `classid`) `elective` LEFT JOIN `class` ON `elective`.`classid` = `class`.`classid` )');
	$sth->bindValue(":stuid", $U["account"]);
	$sth->execute();
	$row = $sth->fetchAll(PDO::FETCH_ASSOC);
	foreach ($row as $temp) {
		$result[$temp["classid"]]["time"] []= $temp;
	}

	foreach ($result as $classid => $temp) {
		$result[$temp["classid"]]["timestr"] = "";
		foreach ($result[$temp["classid"]]["time"] as $time) {
			if ($time["period1"] == $time["period2"]) {
				$result[$temp["classid"]]["timestr"] .= sprintf("(%s) %s ", $C["day"][$time["day"]], $time["period1"]);
			} else {
				$result[$temp["classid"]]["timestr"] .= sprintf("(%s) %s-%s ", $C["day"][$time["day"]], $time["period1"], $time["period2"]);
			}
		}
	}

	return $result;
}

function getCalendar() {
	global $C, $G, $U;

	$elective = getElective();

	$result = [];
	$sth = $G["db"]->prepare('SELECT * FROM `class_time` WHERE `classid` IN ( SELECT `elective`.`classid` FROM (SELECT * FROM `elective` WHERE `stuid` = :stuid ORDER BY `classid`) `elective` LEFT JOIN `class` ON `elective`.`classid` = `class`.`classid` )');
	$sth->bindValue(":stuid", $U["account"]);
	$sth->execute();
	$row = $sth->fetchAll(PDO::FETCH_ASSOC);
	foreach ($row as $temp) {
		for ($period=$temp["period1"]; $period <= $temp["period2"]; $period++) { 
			$result[$temp["day"]][$period] = $elective[$temp["classid"]]["name"];
		}
	}

	return $result;
}

function Elective($classid) {
	global $C, $G, $U;

	$elective = getElective();
	$calendar = getCalendar();

	$sth = $G["db"]->prepare('SELECT * FROM `class` WHERE `classid` = :classid');
	$sth->bindValue(":classid", $classid);
	$sth->execute();
	$class = $sth->fetch(PDO::FETCH_ASSOC);
	if ($class === false) {
		return ["result" => "not_found"];
	} else {
		$collision = false;
		$sth = $G["db"]->prepare('SELECT * FROM `class_time` WHERE `classid` = :classid');
		$sth->bindValue(":classid", $classid);
		$sth->execute();
		$time = $sth->fetchAll(PDO::FETCH_ASSOC);
		foreach ($time as $day) {
			for ($period=$day["period1"]; $period <= $day["period2"]; $period++) { 
				if (isset($calendar[$day["day"]][$period])) {
					return ["result" => "collision"];
				}
			}
		}

		$sth = $G["db"]->prepare("INSERT INTO `elective` (`stuid`, `classid`) VALUES (:stuid, :classid)");
		$sth->bindValue(":stuid", $U["account"]);
		$sth->bindValue(":classid", $classid);
		$sth->execute();

		return ["result" => "success", "class"=> $class];
	}
}

function Unelective($classid, $stuid=null) {
	global $C, $G, $U;

	if (is_null($stuid)) {
		$stuid = $U["account"];
	}

	$elective = getElective();

	$sth = $G["db"]->prepare('SELECT * FROM ( SELECT * FROM `elective` WHERE `stuid` = :stuid AND `classid` = :classid ) `elective` LEFT JOIN `class` ON `elective`.`classid` = `class`.`classid`');
	$sth->bindValue(":stuid", $stuid);
	$sth->bindValue(":classid", $classid);
	$sth->execute();
	$elective = $sth->fetch(PDO::FETCH_ASSOC);
	if ($elective === false) {
		return ["result" => "not_found"];
	} else {
		$sth = $G["db"]->prepare("DELETE FROM `elective` WHERE `stuid` = :stuid AND `classid` = :classid");
		$sth->bindValue(":stuid", $stuid);
		$sth->bindValue(":classid", $classid);
		$sth->execute();
		return ["result"=> "success", "elective"=> $elective];
	}
}
