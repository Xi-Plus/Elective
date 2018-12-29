<?php

$sth = $G["db"]->prepare('SELECT * FROM `student` ORDER BY `stuid`');
$sth->execute();
$row = $sth->fetchAll(PDO::FETCH_ASSOC);
$D["student"] = [];
foreach ($row as $temp) {
	$D["student"][$temp["stuid"]] = $temp;
}
