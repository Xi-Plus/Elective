<?php

$sth = $G["db"]->prepare('SELECT * FROM `class` ORDER BY `classid`');
$sth->execute();
$row = $sth->fetchAll(PDO::FETCH_ASSOC);
$D["class"] = [];
foreach ($row as $temp) {
	$D["class"][$temp["classid"]] = $temp;
}
