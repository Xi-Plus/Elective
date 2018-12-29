<!DOCTYPE html>
<?php
require(__DIR__."/config/config.php");
require(__DIR__."/func/class_list.php");
?>
<html lang="zh-Hant-TW">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0-alpha.6/css/bootstrap.min.css" integrity="sha384-rwoIResjU2yc3z8GV/NPeZWAv56rSmLldC3R/AZzGRnGxQQKnKkoFVhFQhNUwEyJ" crossorigin="anonymous">
<title><?=$C["titlename"]?>/管理課程</title>

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
		if (isset($D["class"][$_POST["classid"]])) {
			?>
			<div class="alert alert-danger alert-dismissible" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				已有課程 <?=htmlentities($_POST["classid"])?>
			</div>
			<?php
		} else if ($_POST["name"] === "" || $_POST["credit"] === "") {
			?>
			<div class="alert alert-danger alert-dismissible" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				名稱及學分數不可為空
			</div>
			<?php
		} else {
			$sth = $G["db"]->prepare("INSERT INTO `class` (`classid`, `name`, `credit`) VALUES (:classid, :name, :credit)");
			$sth->bindValue(":classid", $_POST["classid"]);
			$sth->bindValue(":name", $_POST["name"]);
			$sth->bindValue(":credit", $_POST["credit"]);
			$sth->execute();
			$D["class"][$_POST["classid"]] = array("classid"=>$_POST["classid"], "name"=>$_POST["name"], "credit"=>$_POST["credit"]);
			?>
			<div class="alert alert-success alert-dismissible" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				已新增 <?=htmlentities($_POST["name"])?>
			</div>
			<?php
		}
	} else {
		if (!isset($D["class"][$_POST["classid"]])) {
			?>
			<div class="alert alert-danger alert-dismissible" role="alert">
				<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
				找不到課程 <?=htmlentities($_POST["classid"])?>
			</div>
			<?php
		} else {
			if ($_POST["name"] !== "") {
				$sth = $G["db"]->prepare("UPDATE `class` SET `name` = :name WHERE `classid` = :classid");
				$sth->bindValue(":name", $_POST["name"]);
				$sth->bindValue(":classid", $_POST["classid"]);
				$sth->execute();
				$D["class"][$_POST["classid"]]["name"] = $_POST["name"];
				?>
				<div class="alert alert-success alert-dismissible" role="alert">
					<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					已修改 <?=htmlentities($_POST["classid"])?> 的名稱
				</div>
				<?php
			}
			if ($_POST["credit"] !== "") {
				$sth = $G["db"]->prepare("UPDATE `class` SET `credit` = :credit WHERE `classid` = :classid");
				$sth->bindValue(":credit", $_POST["credit"]);
				$sth->bindValue(":classid", $_POST["classid"]);
				$sth->execute();
				$D["class"][$_POST["classid"]]["credit"] = $_POST["credit"];
				?>
				<div class="alert alert-success alert-dismissible" role="alert">
					<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
					已修改 <?=htmlentities($_POST["classid"])?> 的學分數
				</div>
				<?php
			}
		}
	}
} else if (isset($_POST["delete"])) {
	if (isset($D["class"][$_POST["delete"]])) {
		$sth = $G["db"]->prepare("DELETE FROM `class` WHERE `classid` = :classid");
		$sth->bindValue(":classid", $_POST["delete"]);
		$sth->execute();
		unset($D["class"][$_POST["delete"]]);
		?>
		<div class="alert alert-success alert-dismissible" role="alert">
			<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			已刪除課程 <?=htmlentities($_POST["delete"])?>
		</div>
		<?php
	} 
}

require("header.php");
if ($showform) {
?>
<div class="container">
	<h2>管理課程</h2>
	<form action="" method="post">
		<div class="table-responsive">
			<table class="table">
				<tr>
					<th>編號</th>
					<th>名稱</th>
					<th>學分數</th>
					<th>刪除</th>
				</tr>
				<?php
				foreach ($D["class"] as $class) {
					?>
					<tr>
						<td><?=htmlentities($class["classid"])?></td>
						<td><?=htmlentities($class["name"])?></td>
						<td><?=$class["credit"]?></td>
						<td>
							<button type="submit" name="delete" value="<?=$class["classid"]?>" class="btn btn-danger btn-sm"><i class="fa fa-trash" aria-hidden="true"></i> 刪除</button>
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
			<label class="col-sm-2 form-control-label"><i class="fa fa-user" aria-hidden="true"></i> 編號</label>
			<div class="col-sm-10">
				<input class="form-control" type="text" name="classid" placeholder="必填">
			</div>
		</div>
		<div class="row">
			<label class="col-sm-2 form-control-label"><i class="fa fa-hashtag" aria-hidden="true"></i> 名稱</label>
			<div class="col-sm-10">
				<input class="form-control" type="text" name="name" placeholder="新增時必填，不修改留空">
			</div>
		</div>
		<div class="row">
			<label class="col-sm-2 form-control-label"><i class="fa fa-header" aria-hidden="true"></i> 學分數</label>
			<div class="col-sm-10">
				<input class="form-control" type="number" min="0" step="1" name="credit" placeholder="新增時必填，不修改留空">
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