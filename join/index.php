<?php 
  session_start();
  require('../dbconnect.php');

  // echo '<br>';
  // echo '<br>';
  // echo '<br>';
  // var_dump($_POST);

  // POST送信された時
  if (!empty($_POST)){

    // 入力チェック
    // $_POSTの値がからだった時に$errorという配列にエラーの情報を格納する
    // もし$_POST['nick_name']の値がからだった時に
    if ($_POST['nick_name'] == '') {
      $error['nick_name'] = 'blank';
    }
    // もし$_POST['nick_name']の値がからだった時に
    if ($_POST['email'] == '') {
      $error['email'] = 'blank';
    }
    // もし$_POST['nick_name']の値がからだった時に
    // strlen() = 文字の長さ（文字数）を数字で返してくれる関数
    if ($_POST['password'] == '') {
      $error['password'] = 'blank';
    } elseif(strlen($_POST['password']) < 4) {
      $error['password'] = 'length';
    }

    // 入力チェック後、エラーが何もなければ、check.phpに遷移する
    // $errorという変数が存在していなかった場合、入力が正常と認識
    if (!isset($error)) {
      // emailの重複チェック
      // DBに同じemailの登録があるかチェックする
      // なぜ？ -> メールアドレスが重複していた場合、メールでのやSELECT文での取得の際に重複してしまう可能性がある

      // 検索条件にヒットした件数を取得するSQL文を書く必要がある
      // COUNT() SQL文の関数。ヒットした数を取得する
      $sql = 'SELECT COUNT(*) AS `mail_count` FROM `members` WHERE `email` = ?';
      $data = array($_POST['email']);
      $stmt = $dbh->prepare($sql);
      $stmt->execute($data);

      // 重複しているかの結果の取得。
      $mail_count = $stmt->fetch(PDO::FETCH_ASSOC);

      // もし$mail_count['mail_count']が0でない時
      if ($mail_count['mail_count'] != 0) {
        $error['email'] = 'duplicated';
      }

      // 上の$error['email']に値が入ってない時
      if (!isset($error)) {
        // 画像の拡張子チェック（画像が送られてきているかどうか、ファイルなどの拡張子ではないかのチェック）
        // jpg, png, gifの拡張子はOK
        // substr = 文字列から範囲を指定して一部分の文字を取得する関数
        // substr(文字列、　切り出す文字のスタートの数)
        $ext = substr($_FILES['picture_path']['name'], -3);
        // var_dump($ext);
          // echo '<pre>';
          // var_dump($_FILES);
          // echo '</pre>';

        $ext = strtolower($ext);

        if ($ext === 'jpg' || $ext == 'png' || $ext == 'gif') {
          // 画像のアップロード処理
          // data関数で"確認画面へボタン"を押した時の日付を取得し、ファイル名に文字列連結している
          // なぜ？->emailと同様に重複する可能性があるため
          $picture_path = date('YmdHis').$_FILES['picture_path']['name'];

          // アップロード
          // move_uploaded_file = 画像を指定したディレクトリに保存する。
          // move_uploaded_file(ファイル名,　保存先のディレクトリの位置)
          move_uploaded_file($_FILES['picture_path']['tmp_name'], '../picture_path/'.$picture_path);

          // $_SESSION = SESSAION変数に入力された値を保存
          // session_start()を書かなければいけない！！
          $_SESSION['join'] = $_POST;
          $_SESSION['join']['picture_path'] = $picture_path;

          // あたいの取り方(二次元配列) $_SESSION['join']['nick_name'];

          // check.phpに遷移
          header('Location: check.php');

          // これ以下のコードを無駄に処理しないように、このページの処理を終了させる。
          exit();

        } else {
          $error['image'] = 'type';
        }

      }
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
    <link href="../assets/css/bootstrap.css" rel="stylesheet">
    <link href="../assets/font-awesome/css/font-awesome.css" rel="stylesheet">
    <link href="../assets/css/form.css" rel="stylesheet">
    <link href="../assets/css/timeline.css" rel="stylesheet">
    <link href="../assets/css/main.css" rel="stylesheet">
    <!--
      designフォルダ内では2つパスの位置を戻ってからcssにアクセスしていることに注意！
     -->

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
              <a class="navbar-brand" href="index.php"><span class="strong-title"><i class="fa fa-twitter-square"></i>Diary</span></a>
          </div>
          <!-- Collect the nav links, forms, and other content for toggling -->
          <div class="collapse navbar-collapse" id="bs-example-navbar-collapse-1">
              <ul class="nav navbar-nav navbar-right">
              </ul>
          </div>
          <!-- /.navbar-collapse -->
      </div>
      <!-- /.container-fluid -->
  </nav>

  <div class="container">
    <div class="row">
      <div class="col-md-6 col-md-offset-3 content-margin-top">
        <legend>会員登録</legend>
        <!-- 1.enctype="multipart/form-data"は画像ファイルを送る際に必要 -->
        <!-- 2.input type="file"にする -->
        <form method="post" action="" class="form-horizontal" role="form" enctype="multipart/form-data">
          <!-- ニックネーム -->
          <div class="form-group">
            <label class="col-sm-4 control-label">ニックネーム</label>
            <div class="col-sm-8">
              <input type="text" name="nick_name" class="form-control" placeholder="例： Seed kun">
              <?php if (isset($error['nick_name']) && $error['nick_name'] == 'blank'): ?>
                <p class="error">* ニックネームを入力してください。</p>
              <?php endif ?>
            </div>
          </div>
          <!-- メールアドレス -->
          <div class="form-group">
            <label class="col-sm-4 control-label">メールアドレス</label>
            <div class="col-sm-8">
              <input type="email" name="email" class="form-control" placeholder="例： seed@nex.com">
              <?php if (isset($error['email']) && $error['email'] == 'blank'): ?>
                <p class="error">* emailを入力してください。</p>
              <?php elseif(isset($error['email']) && $error['email'] == 'duplicated'): ?>
                <p class="error">* 入力されたメールアドレスは登録済みです。</p>
              <?php endif ?>
            </div>
          </div>
          <!-- パスワード -->
          <div class="form-group">
            <label class="col-sm-4 control-label">パスワード</label>
            <div class="col-sm-8">
              <input type="password" name="password" class="form-control" placeholder="">
              <?php if (isset($error['password']) && $error['password'] == 'blank'): ?>
                <p class="error">* パスワードを入力してください。</p>
              <?php elseif (isset($error['password']) && $error['password'] == 'length'):?>
                <p class="error">* パスワードを四文字以上入力してください。</p>
              <?php endif ?>
            </div>
          </div>
          <!-- プロフィール写真 -->
          <div class="form-group">
            <label class="col-sm-4 control-label">プロフィール写真</label>
            <div class="col-sm-8">
              <input type="file" name="picture_path" class="form-control">
              <?php if (isset($error['image']) && $error['image'] == 'type'): ?>
                <p class="error">* jpg,png,gifファイルを選択してください
                。</p>
              <?php endif ?>
            </div>
          </div>

          <input type="submit" class="btn btn-default" value="確認画面へ">
        </form>
      </div>
    </div>
  </div>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="../../assets/js/jquery-3.1.1.js"></script>
    <script src="../../assets/js/jquery-migrate-1.4.1.js"></script>
    <script src="../../assets/js/bootstrap.js"></script>
  </body>
</html>
