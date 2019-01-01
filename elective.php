<!DOCTYPE html>
<?php
require(__DIR__.'/config/config.php');
require(__DIR__.'/func/Elective.php');
?>
<html lang="zh-Hant-TW">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css" integrity="sha384-rwoIResjU2yc3z8GV/NPeZWAv56rSmLldC3R/AZzGRnGxQQKnKkoFVhFQhNUwEyJ" crossorigin="anonymous">
<title><?=$C["titlename"]?>/選課</title>

<style type="text/css">
body {
	padding-top: 4.5rem;
}
.filtericon {
	width: 18px;
	text-align: center;
}
</style>
</head>
<body>
<?php
if ($U["accttype"] == "student") {
	$sth = $G["db"]->prepare('SELECT * FROM ( SELECT * FROM `elective` WHERE `stuid` = :stuid ORDER BY `classid` ) `elective` LEFT JOIN `class` ON `elective`.`classid` = `class`.`classid`');
	$sth->bindValue(":stuid", $U["account"]);
	$sth->execute();
	$row = $sth->fetchAll(PDO::FETCH_ASSOC);
	$D["elective"] = [];
	$D["calendar"] = [];
	foreach ($row as $temp) {
		$D["elective"][$temp["classid"]] = $temp;
		$D["elective"][$temp["classid"]]["time"] = [];
	}

	$sth = $G["db"]->prepare('SELECT * FROM `class_time` WHERE `classid` IN ( SELECT `elective`.`classid` FROM (SELECT * FROM `elective` WHERE `stuid` = :stuid ORDER BY `classid`) `elective` LEFT JOIN `class` ON `elective`.`classid` = `class`.`classid` )');
	$sth->bindValue(":stuid", $U["account"]);
	$sth->execute();
	$row = $sth->fetchAll(PDO::FETCH_ASSOC);
	foreach ($row as $temp) {
		$D["elective"][$temp["classid"]]["time"] []= $temp;
		for ($period=$temp["period1"]; $period <= $temp["period2"]; $period++) { 
			$D["calendar"][$temp["day"]][$period] = $D["elective"][$temp["classid"]]["name"];
		}
	}
}

if (isset($_POST["select"])) {
	if (!$U["islogin"]) {
		?>
		<div class="alert alert-danger alert-dismissible" role="alert">
			<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			此功能需要驗證帳號，請<a href="<?=$C["path"]?>/adminlogin/">登入</a>
		</div>
		<?php
	} else if ($U["accttype"] != "student") {
		?>
		<div class="alert alert-danger alert-dismissible" role="alert">
			<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			你沒有對應權限執行此動作
		</div>
		<?php
	} else {
		$sth = $G["db"]->prepare('SELECT * FROM `class` WHERE `classid` = :classid');
		$sth->bindValue(":classid", $_POST["select"]);
		$sth->execute();
		$class = $sth->fetch(PDO::FETCH_ASSOC);
		if ($class === false) {
			?>
			<div class="alert alert-danger alert-dismissible" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				查無該堂課
			</div>
			<?php
		} else {
			$collision = false;
			$sth = $G["db"]->prepare('SELECT * FROM `class_time` WHERE `classid` = :classid');
			$sth->bindValue(":classid", $_POST["select"]);
			$sth->execute();
			$time = $sth->fetchAll(PDO::FETCH_ASSOC);
			foreach ($time as $day) {
				for ($period=$day["period1"]; $period <= $day["period2"]; $period++) { 
					if (isset($D["calendar"][$day["day"]][$period])) {
						$collision = true;
					}
				}
			}
			if ($collision) {
				?>
				<div class="alert alert-danger alert-dismissible" role="alert">
					<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					該課程衝堂
				</div>
				<?php
			} else {
				$sth = $G["db"]->prepare("INSERT INTO `elective` (`stuid`, `classid`) VALUES (:stuid, :classid)");
				$sth->bindValue(":stuid", $U["account"]);
				$sth->bindValue(":classid", $_POST["select"]);
				$sth->execute();
				$D["elective"][$class["classid"]] = $class;
				$D["elective"][$class["classid"]]["time"] = [];
				foreach ($time as $day) {
					$D["elective"][$class["classid"]]["time"] []= $day;
					for ($period=$day["period1"]; $period <= $day["period2"]; $period++) { 
						$D["calendar"][$day["day"]][$period] = $class["name"];
					}
				}
				?>
				<div class="alert alert-success alert-dismissible" role="alert">
					<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					已成功選修 <?=$class["classid"]?> <?=$class["name"]?>
				</div>
				<?php
			}
		}
	}
}

if (isset($_POST["remove"])) {
	if (!$U["islogin"]) {
		?>
		<div class="alert alert-danger alert-dismissible" role="alert">
			<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			此功能需要驗證帳號，請<a href="<?=$C["path"]?>/adminlogin/">登入</a>
		</div>
		<?php
	} else if ($U["accttype"] != "student") {
		?>
		<div class="alert alert-danger alert-dismissible" role="alert">
			<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			你沒有對應權限執行此動作
		</div>
		<?php
	} else {
		$sth = $G["db"]->prepare('SELECT * FROM ( SELECT * FROM `elective` WHERE `stuid` = :stuid AND `classid` = :classid ) `elective` LEFT JOIN `class` ON `elective`.`classid` = `class`.`classid`');
		$sth->bindValue(":stuid", $U["account"]);
		$sth->bindValue(":classid", $_POST["remove"]);
		$sth->execute();
		$elective = $sth->fetch(PDO::FETCH_ASSOC);
		if ($elective === false) {
			?>
			<div class="alert alert-danger alert-dismissible" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				查無該選修
			</div>
			<?php
		} else {
			$sth = $G["db"]->prepare("DELETE FROM `elective` WHERE `stuid` = :stuid AND `classid` = :classid");
			$sth->bindValue(":stuid", $U["account"]);
			$sth->bindValue(":classid", $_POST["remove"]);
			$sth->execute();
			foreach ($D["elective"][$_POST["remove"]]["time"] as $day) {
				for ($period=$day["period1"]; $period <= $day["period2"]; $period++) { 
					unset($D["calendar"][$day["day"]][$period]);
				}
			}
			unset($D["elective"][$_POST["remove"]]);
			?>
			<div class="alert alert-success alert-dismissible" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				已成功退選 <?=$elective["classid"]?> <?=$elective["name"]?>
			</div>
			<?php
		}
	}
}

require("header.php");
?>
<div class="<?=($U["accttype"] == "student"?"container-fluid":"container")?>">
	<h2>選課</h2>
	<form method="POST">
	<div class="row">
		<div class="col-sm-12 <?=($U["accttype"] == "student"?"col-md-6":"col-md-12")?>">
			<div class="row">
				<label class="col-sm-3 col-md-2 form-control-label"><i class="fa fa-calendar filtericon" aria-hidden="true"></i> 星期</label>
				<div class="col-sm-9 col-md-10 form-inline">
					<select class="form-control" name="day">
						<option value="">所有星期</option>
						<?php
						for ($day=1; $day <= 7; $day++) { 
							?>
							<option value="<?=$day?>" <?=(($_POST["day"]??"")==$day?"selected":"")?> >星期<?=$C["day"][$day]?></option>
							<?php
						}
						?>
					</select>
				</div>
			</div>
			<div class="row">
				<label class="col-sm-3 col-md-2 form-control-label"><i class="fa fa-calendar filtericon" aria-hidden="true"></i> 節次</label>
				<div class="col-sm-9 col-md-10 form-inline">
					<select class="form-control" name="period">
						<option value="">所有節次</option>
						<?php
						for ($period=1; $period <= 13; $period++) {
							?>
							<option value="<?=$period?>" <?=(($_POST["period"]??"")==$period?"selected":"")?>>第<?=$period?>節</option>
							<?php
						}
						?>
					</select>
				</div>
			</div>
			<div class="row">
				<div class="col-sm-9 col-md-10 offset-sm-3 offset-md-2">
					<button type="submit" name="search" class="btn btn-success">查詢</button>
				</div>
			</div>
			<?php
			if (isset($_POST["day"])) {
				$D["class"] = getSearchResult($_POST["day"], $_POST["period"]);
			?>
			<div class="table-responsive">
				<table class="table">
					<thead>
						<th>編號</th>
						<th>名稱</th>
						<th>時間</th>
						<th>學分數</th>
						<?php
						if ($U["accttype"] == "student") {
							?>
							<th>選課</th>
							<?php
						}
						?>
					</thead>
					<tbody id="plantable">
					<?php
					foreach ($D["class"] as $class) {
						?>
						<tr>
							<td><?=htmlentities($class["classid"])?></td>
							<td><?=htmlentities($class["name"])?></td>
							<td><?php
								$collision = false;
								foreach ($class["time"] as $time) {
									if ($time["period1"] == $time["period2"]) {
										printf("(%s) %s ", $C["day"][$time["day"]], $time["period1"]);
									} else {
										printf("(%s) %s-%s ", $C["day"][$time["day"]], $time["period1"], $time["period2"]);
									}
									for ($period=$time["period1"]; $period <= $time["period2"]; $period++) { 
										if (isset($D["calendar"][$time["day"]][$period])) {
											$collision = true;
										}
									}
								}
							?></td>
							<td><?=$class["credit"]?></td>
							<?php
							if ($U["accttype"] == "student") {
								?>
								<td>
								<?php
								if (isset($D["elective"][$class["classid"]])) {
									echo "已選";
								} else if ($collision) {
									echo "衝堂";
								} else {
									?><button type="submit" name="select" value="<?=$class["classid"]?>" class="btn btn-success btn-sm">選課</button><?php
								}
								?>
								</td>
								<?php
							}
							?>
						</tr>
						<?php
					}
					?>
					</tbody>
				</table>
			</div>
			<?php
			}
			if ($U["accttype"] == "student") {
				?>
				<h3>已選課程</h3>
				<div class="table-responsive">
					<table class="table">
						<thead>
							<th>編號</th>
							<th>名稱</th>
							<th>時間</th>
							<th>學分數</th>
							<th>退選</th>
						</thead>
						<tbody id="plantable">
						<?php
						foreach ($D["elective"] as $class) {
							?>
							<tr>
								<td><?=htmlentities($class["classid"])?></td>
								<td><?=htmlentities($class["name"])?></td>
								<td><?php
									foreach ($class["time"] as $time) {
										if ($time["period1"] == $time["period2"]) {
											printf("(%s) %s ", $C["day"][$time["day"]], $time["period1"]);
										} else {
											printf("(%s) %s-%s ", $C["day"][$time["day"]], $time["period1"], $time["period2"]);
										}
									}
								?></td>
								<td><?=$class["credit"]?></td>
								<td>
									<button type="submit" name="remove" value="<?=$class["classid"]?>" class="btn btn-danger btn-sm">退選</button>
								</td>
							</tr>
							<?php
						}
						?>
						</tbody>
					</table>
				</div>
				<?php
			}
			?>
		</div>
		<?php
		if ($U["accttype"] == "student") {
		?>
		<div class="col-sm-12 col-md-6">
			<div class="table-responsive">
				<table class="table">
					<thead>
						<th>星期<br>&nbsp;&nbsp;\<br>節次</th>
						<?php
						for ($day=1; $day <= 7; $day++) { 
							?>
							<th><?=$C["day"][$day]?></th>
							<?php
						}
						?>
					</thead>
					<tbody>
					<?php
					for ($period=1; $period <= 13; $period++) {
						?>
						<tr>
							<td><?=$period?></td>
							<?php
							for ($day=1; $day <= 7; $day++) { 
								?>
								<td>
									<?php
									if (isset($D["calendar"][$day][$period])) {
										echo $D["calendar"][$day][$period];
									}
									?>
								</td>
								<?php
							}
							?>
						</tr>
						<?php
					}
					?>
					</tbody>
				</table>
			</div>
		</div>
		<?php
		}
		?>
	</div>
	</form>
</div>

<?php
require("footer.php");
?>
<script src="https://code.jquery.com/jquery-3.1.1.slim.min.js" integrity="sha384-A7FZj7v+d/sdmMqp/nOQwliLvUsJfDHW+k9Omg/a/EheAdgtzNs3hpfag6Ed950n" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.min.js" integrity="sha384-DztdAPBWPRXSA/3eYEEUWrWCy7G5KFbe8fFjk5JAIxUYHKkDx6Qin1DkWx51bBrb" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/js/bootstrap.min.js" integrity="sha384-vBWWzlZJ8ea9aCX4pEW3rVHjgjt7zpkNpZk+02D9phzyeVkE+jo0ieGizqPLForn" crossorigin="anonymous"></script>
<script src="https://use.fontawesome.com/4c0a12abc0.js"></script>
<script type="text/javascript">
$(function () {
	$('[data-toggle="tooltip"]').tooltip()
})
</script>
</body>
</html>
