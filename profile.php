<?php 
  session_start();
  require('dbconnect.php');


  $sql = 'SELECT * FROM `members` WHERE `member_id` = ?';
  $data = array($_SESSION['id']);
  $stmt = $dbh->prepare($sql);
  $stmt->execute($data);

  $user = $stmt->fetch(PDO::FETCH_ASSOC);

  if (!empty($_FILES)) {
    $ext = substr($_FILES['new_image']['name'], -3);
    $ext = strtolower($ext);

    if ($ext === 'jpg' || $ext == 'png' || $ext == 'gif') {
          // 画像のアップロード処理
          // data関数で"確認画面へボタン"を押した時の日付を取得し、ファイル名に文字列連結している
          // なぜ？->emailと同様に重複する可能性があるため
          $image_path = date('YmdHis').$_FILES['new_image']['name'];

          // アップロード
          // move_uploaded_file = 画像を指定したディレクトリに保存する。
          // move_uploaded_file(ファイル名,　保存先のディレクトリの位置)
          move_uploaded_file($_FILES['new_image']['tmp_name'], 'picture_path/'.$image_path);

        }else{
          $error['ext'] = 'false';
        }

        if (!isset($error)) {
          $update_sql = 'UPDATE `members` SET `picture_path` = ? WHERE `member_id` = ?';
          $update_data = array($image_path ,$_SESSION['id']);
          $update_stmt = $dbh->prepare($update_sql);
          $update_stmt->execute($update_data);
        }
  }


 ?>

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
        <h4>Your Profile</h4>
        <div class="msg">
          <form method="post" action="" class="form-horizontal" role="form" enctype="multipart/form-data">
              <!-- つぶやき -->
              <!-- <div class="col-sm-4">
                <p>Name:</p>
              </div> -->
              <label class="col-sm-4 control-label">Name</label>
              <div class="col-sm-8" style="height: 27px">
                <p><?php echo $user['nick_name'] ?></p>
              </div>
              <div class="form-group">
                <label class="col-sm-4 control-label">Profile image</label>
                <div class="col-sm-8">
                    <img src="picture_path/<?php echo $user['picture_path'] ?>" style="width: 200px; height: 200px;">
                  <?php if (isset($error) && $error['ext'] == 'false'): ?>
                    <p class="error">* jpg,gif,pngファイルを選択してください。</p>
                  <?php endif ?>
                  <input type="file" name="new_image">
                </div>
              </div>
            <ul class="paging">
              <input type="submit" class="btn btn-info" value="profile更新">
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
