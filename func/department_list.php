<?php

$sth = $G["db"]->prepare('SELECT * FROM `department` ORDER BY `depid`');
$sth->execute();
$row = $sth->fetchAll(PDO::FETCH_ASSOC);
$D["department"] = [];
foreach ($row as $temp) {
	$D["department"][$temp["depid"]] = $temp;
}
