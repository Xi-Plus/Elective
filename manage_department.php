<!DOCTYPE html>
<?php
require(__DIR__."/config/config.php");
require(__DIR__."/func/department_list.php");
?>
<html lang="zh-Hant-TW">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css" integrity="sha384-rwoIResjU2yc3z8GV/NPeZWAv56rSmLldC3R/AZzGRnGxQQKnKkoFVhFQhNUwEyJ" crossorigin="anonymous">
<title><?=$C["titlename"]?>/管理科系</title>

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
		if (isset($D["department"][$_POST["depid"]])) {
			?>
			<div class="alert alert-danger alert-dismissible" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				已有科系 <?=htmlentities($_POST["depid"])?>
			</div>
			<?php
		} else if ($_POST["name"] === "" || $_POST["director"] === "") {
			?>
			<div class="alert alert-danger alert-dismissible" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				名稱及系主任不可為空
			</div>
			<?php
		} else {
			$sth = $G["db"]->prepare("INSERT INTO `department` (`depid`, `name`, `director`) VALUES (:depid, :name, :director)");
			$sth->bindValue(":depid", $_POST["depid"]);
			$sth->bindValue(":name", $_POST["name"]);
			$sth->bindValue(":director", $_POST["director"]);
			$sth->execute();
			$D["department"][$_POST["depid"]] = array("depid"=>$_POST["depid"], "name"=>$_POST["name"], "director"=>$_POST["director"]);
			?>
			<div class="alert alert-success alert-dismissible" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				已新增 <?=htmlentities($_POST["name"])?>
			</div>
			<?php
		}
	} else {
		if (!isset($D["department"][$_POST["depid"]])) {
			?>
			<div class="alert alert-danger alert-dismissible" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				找不到科系 <?=htmlentities($_POST["depid"])?>
			</div>
			<?php
		} else {
			if ($_POST["name"] !== "") {
				$sth = $G["db"]->prepare("UPDATE `department` SET `name` = :name WHERE `depid` = :depid");
				$sth->bindValue(":name", $_POST["name"]);
				$sth->bindValue(":depid", $_POST["depid"]);
				$sth->execute();
				$D["department"][$_POST["depid"]]["name"] = $_POST["name"];
				?>
				<div class="alert alert-success alert-dismissible" role="alert">
					<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					已修改 <?=htmlentities($_POST["depid"])?> 的名稱
				</div>
				<?php
			}
			if ($_POST["director"] !== "") {
				$sth = $G["db"]->prepare("UPDATE `department` SET `director` = :director WHERE `depid` = :depid");
				$sth->bindValue(":director", $_POST["director"]);
				$sth->bindValue(":depid", $_POST["depid"]);
				$sth->execute();
				$D["department"][$_POST["depid"]]["director"] = $_POST["director"];
				?>
				<div class="alert alert-success alert-dismissible" role="alert">
					<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					已修改 <?=htmlentities($_POST["depid"])?> 的系主任
				</div>
				<?php
			}
		}
	}
} else if (isset($_POST["delete"])) {
	if (isset($D["department"][$_POST["delete"]])) {
		$sth = $G["db"]->prepare("DELETE FROM `department` WHERE `depid` = :depid");
		$sth->bindValue(":depid", $_POST["delete"]);
		$res = $sth->execute();
		if ($res === false) {
			?>
			<div class="alert alert-danger alert-dismissible" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				刪除科系 <?=htmlentities($D["department"][$_POST["delete"]]["name"])?> 失敗<?php
				if ($sth->errorCode() == "23000") {
					echo "，仍有學生屬於此科系";
				}
				?>
			</div>
			<?php
		} else {
			?>
			<div class="alert alert-success alert-dismissible" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				已刪除科系 <?=htmlentities($D["department"][$_POST["delete"]]["name"])?>
			</div>
			<?php
			unset($D["department"][$_POST["delete"]]);
		}
	} 
}

require("header.php");
if ($showform) {
?>
<div class="container">
	<h2>管理科系</h2>
	<form action="" method="post">
		<div class="table-responsive">
			<table class="table">
				<tr>
					<th>代碼</th>
					<th>名稱</th>
					<th>系主任</th>
					<th>刪除</th>
				</tr>
				<?php
				foreach ($D["department"] as $class) {
					?>
					<tr>
						<td><?=htmlentities($class["depid"])?></td>
						<td><?=htmlentities($class["name"])?></td>
						<td><?=htmlentities($class["director"])?></td>
						<td>
							<button type="submit" name="delete" value="<?=$class["depid"]?>" class="btn btn-danger btn-sm"><i class="fa fa-trash" aria-hidden="true"></i> 刪除</button>
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
			<label class="col-sm-2 form-control-label"><i class="fa fa-user" aria-hidden="true"></i> 代碼</label>
			<div class="col-sm-10">
				<input class="form-control" type="text" name="depid" placeholder="必填">
			</div>
		</div>
		<div class="row">
			<label class="col-sm-2 form-control-label"><i class="fa fa-hashtag" aria-hidden="true"></i> 名稱</label>
			<div class="col-sm-10">
				<input class="form-control" type="text" name="name" placeholder="新增時必填，不修改留空">
			</div>
		</div>
		<div class="row">
			<label class="col-sm-2 form-control-label"><i class="fa fa-user" aria-hidden="true"></i> 系主任</label>
			<div class="col-sm-10">
				<input class="form-control" type="text" name="director" placeholder="新增時必填，不修改留空">
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
