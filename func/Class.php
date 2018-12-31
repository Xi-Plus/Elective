<?php

function checkTimeValid($str) {
	foreach (explode(",", $str) as $day) {
		$day = trim($day);
		$day = explode("-", $day);
		if (count($day) != 2 && count($day) != 3) {
			return "wrong_format";
		}
		if ($day[0] < 1 || $day[1] > 7) {
			return "bad_day";
		}
		if ($day[1] < 1 || $day[1] > 13) {
			return "bad_period";
		}
		if (count($day) == 3 && ($day[2] < 1 || $day[2] > 13)) {
			return "bad_period";
		}
	}
	return "ok";
}
