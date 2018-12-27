<?php

$sth = $G["db"]->prepare('SELECT * FROM `admin` ORDER BY `account`');
$sth->execute();
$row = $sth->fetchAll(PDO::FETCH_ASSOC);
$D["admin"] = [];
foreach ($row as $temp) {
	$D["admin"][$temp["account"]] = $temp;
}
