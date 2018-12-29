<?php

$C["path"] = '/elective';
$C["sitename"] = '選課系統';
$C["titlename"] = '選課系統';

$C["DBhost"] = 'localhost';
$C["DBuser"] = 'user';
$C["DBpass"] = 'pass';
$C["DBname"] = 'dbname';

$C["cookiename"] = 'elective';
$C["cookieexpire"] = 86400*7;

$C["accttype"] = ['student'=>'學生', 'admin'=>'管理員'];
$C["day"] = [1=>'一', 2=>'二', 3=>'三', 4=>'四', 5=>'五', 6=>'六', 7=>'日'];

$C["superadmin"] = [];

$G["db"] = new PDO ('mysql:host='.$C["DBhost"].';dbname='.$C["DBname"].';charset=utf8', $C["DBuser"], $C["DBpass"]);

require(__DIR__."/../func/check_login.php");
