<?php 
  session_start();
  require('dbconnect.php');

  echo '<br>';
  echo '<br>';
  echo '<br>';
  echo '<br>';
  echo 'post:';
  var_dump($_POST);
  echo '<br>';
  echo 'files:';
  var_dump($_FILES);
  echo '<br>';
  echo 'session:';
  var_dump($_SESSION);

  // ログインちぇっく
  // 一時間ログインしていない場合、再度ログイン
  if(isset($_SESSION['id']) && $_SESSION['time'] + 3600 > time()){
    // ログインしている
    // ログイン時間の更新
    $_SESSION['time'] = time();

      // ログインユーザーの情報を取得
    $login_sql = 'SELECT * FROM `members` WHERE `member_id` = ? ';
    $login_data = array($_SESSION['id']);
    $login_stmt = $dbh->prepare($login_sql);
    $login_stmt->execute($login_data);

    $login_user = $login_stmt->fetch(PDO::FETCH_ASSOC);
  }else{
    // ログインしていない,または時間切れ
    // ログイン画面へ強制遷移する
    header('Location: login.php');
  }

  // 呟くボタンが押された時
  if (!empty($_POST) && !empty($_POST['action']) && $_POST['action'] == 'post') {
    // 入力チェック

    $ext = substr($_FILES['image_path']['name'], -3);
    $ext = strtolower($ext);

    if ($ext === 'jpg' || $ext == 'png' || $ext == 'gif') {
          // 画像のアップロード処理
          // data関数で"確認画面へボタン"を押した時の日付を取得し、ファイル名に文字列連結している
          // なぜ？->emailと同様に重複する可能性があるため
          $image_path = date('YmdHis').$_FILES['image_path']['name'];

          // アップロード
          // move_uploaded_file = 画像を指定したディレクトリに保存する。
          // move_uploaded_file(ファイル名,　保存先のディレクトリの位置)
          move_uploaded_file($_FILES['image_path']['tmp_name'], 'image_path/'.$image_path);

        }
    if ($_POST['contents'] == '') {
      $error['contents'] = 'blank';
    }

    if (!isset($error)) {
      if (!isset($image_path)) {
        $image_path = "";
      }
      echo $image_path;
      $sql = 'INSERT INTO `diary` SET `user_id` = ?, `title` = ?, `contents` = ?, `image_path` = ?, `reply_diary_id` = -1, `created` = NOW(), `modified` = NOW()';
      $data = array($_SESSION['id'], $_POST['title'], $_POST['contents'], $image_path);
      $stmt = $dbh->prepare($sql);
      $stmt->execute($data);
    }
  }

  // search機能
  if (isset($_GET['search']) && $_GET['search'] != '') {
    // 検索かかっている時
    $page = '';

    if (isset($_GET['page'])) {
      $page = $_GET['page'];
    }else{
      $page = 1;
    }

      $page = max($page, 1);

      $page_number = 5;

      echo 'search';
      echo '<br>';
      $page_sql = "SELECT COUNT(*) AS `page_count` FROM `diary` WHERE `delete_flag` = 0 AND `contents` LIKE '%"."{$_GET['search']}"."%'";
      $page_stmt = $dbh->prepare($page_sql);
      $page_stmt->execute();

      $page_count = $page_stmt->fetch(PDO::FETCH_ASSOC);
      var_dump($page_count);
      echo '<br>';

      $all_page_number = ceil($page_count['page_count'] / $page_number);
      $page = min($page, $all_page_number);

      $start = ($page - 1) * $page_number;

      $diary_sql = "SELECT `diary`.*, `members`.`nick_name`, `members`.`picture_path`, `likes_count`, `like_on`
                    FROM `diary` 
                    LEFT JOIN `members` ON `diary`.`user_id` = `members`.`member_id` 
                    LEFT JOIN (SELECT diary_id, COUNT(`diary_id`) AS `likes_count` FROM `likes` GROUP BY `diary_id`) table1 ON `diary`.`diary_id` = `table1`.`diary_id`
                    LEFT JOIN (SELECT diary_id, COUNT(`diary_id`) AS `like_on` FROM `likes` WHERE `member_id` = ? GROUP BY `diary_id`) table2 ON `diary`.`diary_id` = `table2`.`diary_id`
                    WHERE `delete_flag` = 0 AND `contents` LIKE '%"."{$_GET['search']}"."%'"."
                    ORDER BY `diary`.`modified` DESC LIMIT $start, $page_number";
      $diary_data = array($_SESSION['id']);
      $diary_stmt = $dbh->prepare($diary_sql);
      $diary_stmt->execute($diary_data);

      $diary_list = array();
      while(true){

        $diary = $diary_stmt->fetch(PDO::FETCH_ASSOC);
        if($diary == false){
          break;
        }
        if ($diary['likes_count'] == null) {
          $diary['likes_count'] = 0;
        }
        if ($diary['like_on'] == null) {
          $diary['like_on'] = 0;
        }
        $diary_list[] = $diary;
      }
      var_dump($diary_list);
  }else{
    // 検索無しの時
    // ページング機能
    // 空の変数を用意
    $page = '';

    // パラメータが存在していた場合ページ番号を代入
    if (isset($_GET['page'])) {
      $page = $_GET['page'];
    }else{
      $page = 1;
    }

      // 1以外のイレギュラーな数字が入ってきた時、ページ番号を強制的に１とする
      // max　カンマ区切りで羅列された数字の中から最大の数字を取得する
      $page = max($page, 1);

      // 1ページ分の表示件数を指定
      $page_number = 5;

      // データの件数から最大ページを計算する
      $page_sql = 'SELECT COUNT(*) AS `page_count` FROM `diary` WHERE `delete_flag` = 0';
      $page_stmt = $dbh->prepare($page_sql);
      $page_stmt->execute();

      $page_count = $page_stmt->fetch(PDO::FETCH_ASSOC);

      $all_page_number = ceil($page_count['page_count'] / $page_number);
      // パラメータのページ番号が最大ページを超えていれば、強制的に最後のページとする
      $page = min($page, $all_page_number);

      // 表示するデータの取得開始場所
      $start = ($page - 1) * $page_number;






      // 一覧用の投稿全件取得
      // テーブル結合
      //  INNER JOIN と OUTER JOIN(left join と right join)
      // INNER JOIN = 両方のテーブルに存在するデータのみ取得
      // OUTER JOIN(left join と right join) = 複数のテーブルがあり、それらを結合するときに優先テーブルをひとつきめ、そこにある情報はすべて表示しながら、他のテーブルの情報についになるデータがあれば表示する。
      // 優先テーブルに指定されるとそのテーブルの情報はすべて表示される。
      $diary_sql = "SELECT `diary`.*, `members`.`nick_name`, `members`.`picture_path`, `likes_count`, `like_on`
                    FROM `diary` 
                    LEFT JOIN `members` ON `diary`.`user_id` = `members`.`member_id` 
                    LEFT JOIN (SELECT diary_id, COUNT(`diary_id`) AS `likes_count` FROM `likes` GROUP BY `diary_id`) table1 ON `diary`.`diary_id` = `table1`.`diary_id`
                    LEFT JOIN (SELECT diary_id, COUNT(`diary_id`) AS `like_on` FROM `likes` WHERE `member_id` = ? GROUP BY `diary_id`) table2 ON `diary`.`diary_id` = `table2`.`diary_id`
                    WHERE `delete_flag` = 0 ORDER BY `diary`.`modified` DESC LIMIT $start, $page_number";
      $diary_data = array($_SESSION['id']);
      $diary_stmt = $dbh->prepare($diary_sql);
      $diary_stmt->execute($diary_data);

      $diary_list = array();
      while(true){

        $diary = $diary_stmt->fetch(PDO::FETCH_ASSOC);
        if($diary == false){
          break;
        }
        if ($diary['likes_count'] == null) {
          $diary['likes_count'] = 0;
        }
        if ($diary['like_on'] == null) {
          $diary['like_on'] = 0;
        }
        $diary_list[] = $diary;
    }
  }
  // echo '<pre>';
  // var_dump($diary_list);
  // echo '</pre>';

  // いいねの数の取得
  $likes_sql = "SELECT COUNT(`diary_id`) FROM `likes` ";
  $likes_stmt = $dbh->prepare($likes_sql);
  $likes_stmt->execute();

  $likes_list = array();
  while(true){

    $like = $likes_stmt->fetch(PDO::FETCH_ASSOC);
    if($like == false){
      break;
    }
    $likes_list[] = $like;
  }
  var_dump($likes_list);


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
              <a class="navbar-brand" href="join/index.php"><span class="strong-title"><i class="fa fa-twitter-square"></i>Diary</span></a>
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
      <div class="col-md-4 content-margin-top">
        <legend>ようこそ<a href="profile.php"><?php echo $login_user['nick_name'] ?></a>さん！</legend>
        <form method="post" action="" class="form-horizontal" role="form" enctype="multipart/form-data">
            <input type="hidden" name="action" value="post">
            <!-- つぶやき -->
            <div class="form-group">
              <span>Picture:</span><input type="file" name="image_path" style="display: inline-block;"><br>
              <span>title:</span><input type="text" name="title" style="display: inline-block;"><br>
              <label class="col-sm-4 control-label">今日の日記。</label>
              <div class="col-sm-8">
                <textarea name="contents" cols="50" rows="5" class="form-control" placeholder="例：Hello World!"></textarea>
                <?php if(isset($error['contents']) && $error['contents'] == 'blank'): ?>
                  <p class="error">日記の内容を入力してください。</p>
                <?php endif ?>
              </div>
            </div>
          <ul class="paging">
            <input type="submit" class="btn btn-info" value="つぶやく">
                &nbsp;&nbsp;&nbsp;&nbsp;
                <?php if ($page == 1): ?>
                  <li>前</li>
                <?php else: ?>
                  <li><a href="index.php?page=<?php echo $page -1; ?>&search=<?php echo $_GET['search'] ?>" class="btn btn-default">前</a></li>
                <?php endif ?>
                &nbsp;&nbsp;|&nbsp;&nbsp;
                <?php if ($page == $all_page_number): ?>
                  <li>次</li>
                <?php else: ?>
                  <li><a href="index.php?page=<?php echo $page +1; ?>&search=<?php echo $_GET['search'] ?>" class="btn btn-default">次</a></li>
                <?php endif ?>
                <li><?php echo $page; ?>/<?php echo $all_page_number; ?></li>
          </ul>
        </form>
        <?php for($i=1; $i<=$all_page_number; $i++): ?>
          <a href="index.php?page=<?php echo $i ?>" style="font-size: 20px;"><?php echo $i ?></a>
        <?php endfor ?>
      </div>

      <div class="col-md-8 content-margin-top">
        <div class="search">
          <form action="" method="get">
            <input type="text" name="search" placeholder="検索">
            <input type="submit" value="検索">
          </form>
        </div>
        <div>
          <?php if (isset($_GET['search'])): ?>
          <p>検索: <?php echo $_GET['search'] ?></p>
          <?php endif ?>
        </div>
        <?php foreach($diary_list as $diary): ?>
        <div class="msg">
          <?php if ($diary['reply_diary_id'] > -1): ?>
            <?php echo '------' ?>
          <?php endif ?>
          <img src="picture_path/<?php echo $diary['picture_path'] ?>" width="48" height="48">
          <p style="display: inline-block; float:left;">
            <span class="name"> (<?php echo $diary['nick_name']; ?>) </span>
            <?php if ($diary['user_id'] != $login_user['member_id']): ?>
            [<a href="reply.php?diary_id=<?php echo $diary['diary_id'] ?>">Re</a>]
            <?php endif ?>
          </p>
          <div class="day" style="float: left;">
            <a href="view.php?diary_id=<?php echo $diary['diary_id'] ?>">
              <?php echo date('y-m-d h:i', strtotime($diary['modified'])); ?>
            </a>
            <?php if ($diary['user_id'] == $login_user['member_id']): ?>
            [<a href="edit.php?diary_id=<?php echo $diary['diary_id'] ?>" style="color: #00994C;">編集</a>]
            [<a href="delete.php?action=delete&diary_id=<?php echo $diary['diary_id']; ?>" style="color: #F33;">削除</a>]
            <?php endif ?>
          </div>
          <br>
          <?php if ($diary['reply_diary_id'] == -1): ?>
          <p><?php echo $diary['title']; ?></p><br>
          <?php endif ?>
          <p><?php echo $diary['contents']; ?></p>
          <?php if (!empty($diary['image_path'])): ?>
            <div class="post_image" style="width: 500px; height:230px; display: block;">
              <img src="image_path/<?php echo $diary['image_path'] ?>" alt="" style="width: 300px;, height: 300px;, display: block;">
            </div><br>
          <?php endif ?>
          <!-- いいね -->
          <?php if ($diary['user_id'] != $login_user['member_id'] && $diary['like_on'] == 0): ?>
            <div class="favorite">
              <a href="likes.php?diary_id=<?php echo $diary['diary_id'] ?>"><img src="" alt="">iine</a>
              <?php echo $diary['likes_count'] ?>
            </div>
          <?php elseif($diary['user_id'] != $login_user['member_id'] && $diary['like_on'] == 1): ?>
            <div class="favorite">
              <a href="likes.php?diary_id=<?php echo $diary['diary_id'] ?>" style="color: red;"><img src="" alt="">iine</a>
              <?php echo $diary['likes_count'] ?>
            </div>
          <?php else: ?>
            <div class="favorite">
              <span>iine</span>
              <?php echo $diary['likes_count'] ?>
            </div>
          <?php endif ?>
        </div>
        <?php endforeach; ?>
      </div>

    </div>
  </div>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="assets/js/jquery-3.1.1.js"></script>
    <script src="assets/js/jquery-migrate-1.4.1.js"></script>
    <script src="assets/js/bootstrap.js"></script>
    <script src="index.js"></script>
  </body>
</html>
