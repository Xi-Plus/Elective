<!DOCTYPE html>
<?php
require(__DIR__."/config/config.php");
require(__DIR__."/func/Elective.php");
?>
<html lang="zh-Hant-TW">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css" integrity="sha384-rwoIResjU2yc3z8GV/NPeZWAv56rSmLldC3R/AZzGRnGxQQKnKkoFVhFQhNUwEyJ" crossorigin="anonymous">
<title><?=$C["titlename"]?>/管理選課</title>

<style type="text/css">
body {
	padding-top: 4.5rem;
}
</style>
</head>
<body>
<?php
$showform = true;
$D["class"] = getSearchResult();
if (!$U["islogin"]) {
	?>
	<div class="alert alert-danger alert-dismissible" role="alert">
		<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		此功能需要驗證帳號，請<a href="<?=$C["path"]?>/adminlogin/">登入</a>
	</div>
	<?php
	$showform = false;
} else if ($U["accttype"] != "admin") {
	?>
	<div class="alert alert-danger alert-dismissible" role="alert">
		<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		你沒有對應權限執行此動作
	</div>
	<?php
	$showform = false;
} else if (isset($_POST["delete"])) {
	$temp = explode("-", $_POST["delete"]);
	if (count($temp) == 2) {
		$result = Unelective($temp[1], $temp[0]);
		switch ($result["result"]) {
			case 'not_found':
				?>
				<div class="alert alert-danger alert-dismissible" role="alert">
					<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					查無該選修
				</div>
				<?php
				break;

			case 'success':
				?>
				<div class="alert alert-success alert-dismissible" role="alert">
					<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					已成功退選 <?=$result["elective"]["classid"]?> <?=$result["elective"]["name"]?>
				</div>
				<?php
				$D["elective"] = getElective();
				$D["calendar"] = getCalendar();
				break;
		}
	} else {
		?>
		<div class="alert alert-danger alert-dismissible" role="alert">
			<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			資料出錯
		</div>
		<?php
	}
}
if (!isset($_GET["classid"]) && !isset($_GET["stuid"])) {
	?>
	<div class="alert alert-success alert-dismissible" role="alert">
		<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		請提供課程編號或是學生編號
	</div>
	<?php
	$showform = false;
}

require("header.php");
if ($showform) {
?>
<div class="container">
	<h2>管理選課</h2>
	<form action="" method="post">
	<?php
	if (isset($_GET["classid"])) {
		$query = 'SELECT * FROM  `class` WHERE `classid` = :classid ';
		$sth = $G["db"]->prepare($query);
		$sth->bindValue(":classid", $_GET["classid"]);
		$sth->execute();
		$class = $sth->fetch(PDO::FETCH_ASSOC);
		?>
		課程 <?=$class["classid"]?> <?=$class["name"]?> 學分數 <?=$class["credit"]?>
		<div class="table-responsive">
			<table class="table">
				<tr>
					<th>學號</th>
					<th>姓名</th>
					<th>退選</th>
				</tr>
				<?php
				$query = 'SELECT `student`.`stuid`, `student`.`name` AS `stuname` FROM (SELECT * FROM `elective` WHERE `classid` = :classid) elective LEFT JOIN `student` ON `elective`.`stuid` = `student`.`stuid`';
				$sth = $G["db"]->prepare($query);
				$sth->bindValue(":classid", $_GET["classid"]);
				$sth->execute();
				$D["elective"] = $sth->fetchAll(PDO::FETCH_ASSOC);
				foreach ($D["elective"] as $elective) {
					?>
					<tr>
						<td><?=htmlentities($elective["stuid"])?></td>
						<td><?=htmlentities($elective["stuname"])?></td>
						<td>
							<button type="submit" name="delete" value="<?=$elective["stuid"]?>-<?=$class["classid"]?>" class="btn btn-danger btn-sm"><i class="fa fa-trash" aria-hidden="true"></i> 退選</button>
						</td>
					</tr>
					<?php
				}
				?>
			</table>
		</div>
		<?php
	} else if (isset($_GET["stuid"])) {
		$query = 'SELECT * FROM  `student` WHERE `stuid` = :stuid ';
		$sth = $G["db"]->prepare($query);
		$sth->bindValue(":stuid", $_GET["stuid"]);
		$sth->execute();
		$student = $sth->fetch(PDO::FETCH_ASSOC);
		?>
		學生 <?=$student["stuid"]?> <?=$student["name"]?> 的選課表
		<div class="table-responsive">
			<table class="table">
				<tr>
					<th>課程編號</th>
					<th>課程名稱</th>
					<th>退選</th>
				</tr>
				<?php
				$query = 'SELECT `class`.`classid`, `class`.`name` AS `classname` FROM (SELECT * FROM `elective` WHERE `stuid` = :stuid) elective LEFT JOIN `class` ON `elective`.`classid` = `class`.`classid` ';
				$sth = $G["db"]->prepare($query);
				$sth->bindValue(":stuid", $_GET["stuid"]);
				$sth->execute();
				$D["elective"] = $sth->fetchAll(PDO::FETCH_ASSOC);
				foreach ($D["elective"] as $elective) {
					?>
					<tr>
						<td><?=htmlentities($elective["classid"])?></td>
						<td><?=htmlentities($elective["classname"])?></td>
						<td>
							<button type="submit" name="delete" value="<?=$student["stuid"]?>-<?=$elective["classid"]?>" class="btn btn-danger btn-sm"><i class="fa fa-trash" aria-hidden="true"></i> 退選</button>
						</td>
					</tr>
					<?php
				}
			}
			?>
			</table>
		</div>
	</form>
</div>

<?php
}
require("footer.php");
?>
<script src="https://code.jquery.com/jquery-3.1.1.slim.min.js" integrity="sha384-A7FZj7v+d/sdmMqp/nOQwliLvUsJfDHW+k9Omg/a/EheAdgtzNs3hpfag6Ed950n" crossorigin="anonymous"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/tether/1.4.0/js/tether.min.js" integrity="sha384-DzthAPBWPRXSA/3eYEEUWrWCy7G5KFbe8fFjk5JAIxUYHKkDx6Qin1DkWx51bBrb" crossorigin="anonymous"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/js/bootstrap.min.js" integrity="sha384-vBWWzlZJ8ea9aCX4pEW3rVHjgjt7zpkNpZk+02D9phzyeVkE+jo0ieGizqPLForn" crossorigin="anonymous"></script>
<script src="https://use.fontawesome.com/4c0a12abc0.js"></script>
<script type="text/javascript">
$(function () {
	$('[data-toggle="tooltip"]').tooltip()
})
</script>
</body>
</html>
