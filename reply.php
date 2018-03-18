<?php 
  session_start();
  require('dbconnect.php');

  if (isset($_GET['diary_id'])) {
    $sql = "SELECT `diary`.*, `members`.`nick_name`, `members`.`picture_path` FROM `diary` LEFT JOIN `members` ON `diary`.`user_id` = `members`.`member_id` WHERE `diary_id` = ?";
    $data = array($_GET['diary_id']);
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);

    $diary = $stmt->fetch(PDO::FETCH_ASSOC);
    $reply_msg = "@".$diary['contents']."(".$diary['nick_name'].")";

    echo '<br>';
    echo '<br>';
    echo '<br>';
    var_dump($diary);
  }

  // bottunが押された時
  if (!empty($_POST)) {
    if (isset($_POST['contents']) && $_POST['contents'] == '') {
      $error = 'blank';
    }

    if (!isset($error)) {
      $reply_sql = 'INSERT INTO `diary` SET `user_id` = ?, `title` = ?, `contents` = ?, `image_path` = ?,  `reply_diary_id` = ?, `created` = NOW(), `modified` = NOW()';
      $reply_data = array($_SESSION['id'], '', $_POST['contents'], '', $_GET['diary_id']);
      $reply_stmt = $dbh->prepare($reply_sql);
      $reply_stmt ->execute($reply_data);

      header('Location: index.php');
      exit;

    }
  }
?>

<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>SeedSNS</title>

    <!-- Bootstrap -->
    <link href="assets/css/bootstrap.css" rel="stylesheet">
    <link href="assets/font-awesome/css/font-awesome.css" rel="stylesheet">
    <link href="assets/css/form.css" rel="stylesheet">
    <link href="assets/css/timeline.css" rel="stylesheet">
    <link href="assets/css/main.css" rel="stylesheet">

  </head>
  <body>
  <nav class="navbar navbar-default navbar-fixed-top">
      <div class="container">
          <!-- Brand and toggle get grouped for better mobile display -->
          <div class="navbar-header page-scroll">
              <button type="button" class="navbar-toggle" data-toggle="collapse" data-target="#bs-example-navbar-collapse-1">
                  <span class="sr-only">Toggle navigation</span>
                  <span class="icon-bar"></span>
                  <span class="icon-bar"></span>
                  <span class="icon-bar"></span>
              </button>
              <a class="navbar-brand" href="index.html"><span class="strong-title"><i class="fa fa-twitter-square"></i> Seed SNS</span></a>
          </div>
          <!-- Collect the nav links, forms, and other content for toggling -->
          <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
              <ul class="nav navbar-nav navbar-right">
                <li><a href="logout.php">ログアウト</a></li>
              </ul>
          </div>
          <!-- /.navbar-collapse -->
      </div>
      <!-- /.container-fluid -->
  </nav>

  <div class="container">
    <div class="row">
      <div class="col-md-6 col-md-offset-3 content-margin-top">
        <h4>投稿に返信しましょう</h4>
        <div class="msg">
          <form method="post" action="" class="form-horizontal" role="form">
              <!-- つぶやき -->
              <div class="form-group">
                <label class="col-sm-4 control-label">投稿に返信</label>
                <div class="col-sm-8">
                    <textarea name="contents" cols="50" rows="5" class="form-control" placeholder="例：Hello World!"><?php echo $reply_msg; ?></textarea>
                  <?php if (isset($error) && $error == 'blank'): ?>
                    <p class="error">* 何か呟いてください。</p>
                  <?php endif ?>
                </div>
              </div>
            <ul class="paging">
              <input type="submit" class="btn btn-info" value="返信としてつぶやく">
            </ul>
          </form>
        </div>
        <a href="index.php">&laquo;&nbsp;一覧へ戻る</a>
      </div>
    </div>
  </div>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="assets/js/jquery-3.1.1.js"></script>
    <script src="assets/js/jquery-migrate-1.4.1.js"></script>
    <script src="assets/js/bootstrap.js"></script>
  </body>
</html>
