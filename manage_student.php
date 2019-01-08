<!DOCTYPE html>
<?php
require(__DIR__."/config/config.php");
require(__DIR__."/func/student_list.php");
require(__DIR__."/func/department_list.php");
?>
<html lang="zh-Hant-TW">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css" integrity="sha384-rwoIResjU2yc3z8GV/NPeZWAv56rSmLldC3R/AZzGRnGxQQKnKkoFVhFQhNUwEyJ" crossorigin="anonymous">
<title><?=$C["titlename"]?>/管理學生</title>

<style type="text/css">
body {
	padding-top: 4.5rem;
}
</style>
</head>
<body>
<?php
$showform = true;
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
} else if (isset($_POST["action"])) {
	if ($_POST["action"] === "new") {
		if (isset($D["student"][$_POST["stuid"]])) {
			?>
			<div class="alert alert-danger alert-dismissible" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				已有學生 <?=htmlentities($_POST["stuid"])?>
			</div>
			<?php
		} else if ($_POST["name"] === "" || $_POST["depid"] === "") {
			?>
			<div class="alert alert-danger alert-dismissible" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				姓名及科系不可為空
			</div>
			<?php
		} else {
			$sth = $G["db"]->prepare("INSERT INTO `student` (`stuid`, `name`, `depid`, `password`) VALUES (:stuid, :name, :depid, :password)");
			$sth->bindValue(":stuid", $_POST["stuid"]);
			$sth->bindValue(":name", $_POST["name"]);
			$sth->bindValue(":depid", $_POST["depid"]);
			$sth->bindValue(":password", password_hash($_POST["stuid"], PASSWORD_DEFAULT));
			$sth->execute();
			$D["student"][$_POST["stuid"]] = array("stuid"=>$_POST["stuid"], "name"=>$_POST["name"], "depid"=>$_POST["depid"]);
			?>
			<div class="alert alert-success alert-dismissible" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				已新增 <?=htmlentities($_POST["name"])?>
			</div>
			<?php
		}
	} else {
		if (!isset($D["student"][$_POST["stuid"]])) {
			?>
			<div class="alert alert-danger alert-dismissible" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				找不到學生 <?=htmlentities($_POST["stuid"])?>
			</div>
			<?php
		} else {
			if ($_POST["name"] !== "") {
				$sth = $G["db"]->prepare("UPDATE `student` SET `name` = :name WHERE `stuid` = :stuid");
				$sth->bindValue(":name", $_POST["name"]);
				$sth->bindValue(":stuid", $_POST["stuid"]);
				$sth->execute();
				$D["student"][$_POST["stuid"]]["name"] = $_POST["name"];
				?>
				<div class="alert alert-success alert-dismissible" role="alert">
					<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					已修改 <?=htmlentities($_POST["stuid"])?> 的姓名
				</div>
				<?php
			}
			if ($_POST["depid"] !== "") {
				$sth = $G["db"]->prepare("UPDATE `student` SET `depid` = :depid WHERE `stuid` = :stuid");
				$sth->bindValue(":depid", $_POST["depid"]);
				$sth->bindValue(":stuid", $_POST["stuid"]);
				$sth->execute();
				$D["student"][$_POST["stuid"]]["depid"] = $_POST["depid"];
				?>
				<div class="alert alert-success alert-dismissible" role="alert">
					<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					已修改 <?=htmlentities($_POST["stuid"])?> 的科系
				</div>
				<?php
			}
		}
	}
} else if (isset($_POST["delete"])) {
	if (isset($D["student"][$_POST["delete"]])) {
		$sth = $G["db"]->prepare("DELETE FROM `student` WHERE `stuid` = :stuid");
		$sth->bindValue(":stuid", $_POST["delete"]);
		$res = $sth->execute();
		if ($res === false) {
			?>
			<div class="alert alert-danger alert-dismissible" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				刪除學生 <?=htmlentities($D["student"][$_POST["delete"]]["name"])?> 失敗<?php
				if ($sth->errorCode() == "23000") {
					echo "，該學生仍有課程尚未退選";
				}
				?>
			</div>
			<?php
		} else {
			?>
			<div class="alert alert-success alert-dismissible" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				已刪除學生 <?=htmlentities($D["student"][$_POST["delete"]]["name"])?>
			</div>
			<?php
			unset($D["student"][$_POST["delete"]]);
		}
	} 
}

require("header.php");
if ($showform) {
?>
<div class="container">
	<h2>管理學生</h2>
	<form action="" method="post">
		<div class="table-responsive">
			<table class="table">
				<tr>
					<th>學號</th>
					<th>姓名</th>
					<th>科系</th>
					<th>管理</th>
				</tr>
				<?php
				foreach ($D["student"] as $student) {
					?>
					<tr>
						<td><?=htmlentities($student["stuid"])?></td>
						<td><?=htmlentities($student["name"])?></td>
						<td><?=htmlentities($student["depid"])?> <?=htmlentities($D["department"][$student["depid"]]["name"])?></td>
						<td>
							<a href="<?=$C["path"]?>/manageelective/?stuid=<?=htmlentities($student["stuid"])?>">選課表</a>&nbsp;&nbsp;&nbsp;
							<button type="submit" name="delete" value="<?=$student["stuid"]?>" class="btn btn-danger btn-sm"><i class="fa fa-trash" aria-hidden="true"></i> 刪除</button>
						</td>
					</tr>
					<?php
				}
				?>
			</table>
		</div>
	</form>
	<h3>新增/修改</h3>
	<form action="" method="post">
		<div class="row">
			<label class="col-sm-2 form-control-label"><i class="fa fa-user" aria-hidden="true"></i> 學號</label>
			<div class="col-sm-10">
				<input class="form-control" type="text" name="stuid" placeholder="必填">
			</div>
		</div>
		<div class="row">
			<label class="col-sm-2 form-control-label"><i class="fa fa-hashtag" aria-hidden="true"></i> 姓名</label>
			<div class="col-sm-10">
				<input class="form-control" type="text" name="name" placeholder="新增時必填，不修改留空">
			</div>
		</div>
		<div class="row">
			<label class="col-sm-2 form-control-label"><i class="fa fa-header" aria-hidden="true"></i> 科系</label>
			<div class="col-sm-10">
				<select class="form-control" name="depid">
					<option value="">未選取/不修改</option>
					<?php
					foreach ($D["department"] as $depid => $department) {
						?>
						<option value="<?=$depid?>"><?=$department["name"]?></option>
						<?php
					}
					?>
				</select>
			</div>
		</div>
		<div class="row">
			<div class="col-sm-10 offset-sm-2">
				<button type="submit" class="btn btn-success" name="action" value="new"><i class="fa fa-plus" aria-hidden="true"></i> 新增</button>
				<button type="submit" class="btn btn-success" name="action" value="edit"><i class="fa fa-pencil" aria-hidden="true"></i> 修改</button>
			</div>
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
