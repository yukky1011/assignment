<?php 
  require('dbconnect.php');
  var_dump($_GET);
  var_dump($_POST);

  if (isset($_GET)) {
    $sql = 'SELECT * FROM `diary` WHERE `diary_id` = ? ';
    $data = array($_GET['diary_id']);
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);
    $diary_edit= $stmt->fetch(PDO::FETCH_ASSOC);
    var_dump($diary_edit);
  }

  if (!empty($_POST['new_comment'])) {
    $edit_sql = 'UPDATE `diary` SET `contents` = ?, `modified` = NOW()  WHERE `diary_id` = ? ';
    $edit_data = array($_POST['new_comment'], $_GET['diary_id']);
    $edit_stmt = $dbh->prepare($edit_sql);
    $edit_stmt->execute($edit_data);

    header('Location: index.php');
    exit;
  }elseif(isset($_POST['new_comment'])){
    $error['edit'] = 'blank';
  }
 ?>

<!--  <!DOCTYPE html>
 <html lang="ja">
 <head>
   <meta charset="UTF-8">
   <title>edit.php</title>
 </head>
 <body>
   <form action="" method="post">
     <textarea name="new_comment" id="" cols="30" rows="10"><?php echo $tweet_edit['tweet']; ?></textarea>
     <input type="submit" value="完了">
   </form>
   <?php if (isset($error['edit']) && $error['edit'] == 'blank'): ?>
    <p class="error">* コメント内容を入力してください。</p>
   <?php endif ?>
 </body>
 </html> -->

 <!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Diary</title>

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
              <a class="navbar-brand" href="index.html"><span class="strong-title"><i class="fa fa-twitter-square"></i> Diary</span></a>
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
        <h4>投稿編集</h4>
        <div class="msg">
          <form method="POST" action="" class="form-horizontal" role="form">
            <textarea name="new_comment" id="" cols="30" rows="10"><?php echo $diary_edit['contents']; ?></textarea>
            <ul class="paging">
              <input type="submit" class="btn btn-info" value="更新">
            </ul>
          </form>
        </div>
        <?php if (isset($error['edit']) && $error['edit'] == 'blank'): ?>
          <p class="error">* 日記の内容を入力してください。</p>
        <?php endif ?>
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