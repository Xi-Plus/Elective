<?php

function getSearchResult($day="", $period="") {
	global $C, $G;

	$query = 'SELECT * FROM `class_time` WHERE 1 ';
	if ($day != "" && is_numeric($day)) {
		$query .= "AND `day` = :day ";
	}
	if ($period != "" && is_numeric($period)) {
		$query .= "AND `period1` <= :period AND :period <= `period2` ";
	}
	$query .= "ORDER BY `classid` ";
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
