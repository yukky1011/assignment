<!-- <?php 
var_dump($_POST);
echo '<br>';
var_dump(isset($_POST));
echo '<br>';
var_dump(isset($_POST['name']));
$_POST[] = null;
echo '<br>';
var_dump($_POST);
var_dump(isset($_POST));

 ?> -->


<!-- sessionを使うやり方 -->
 <?php 
  session_start();
  if (!isset($_SESSION['count'])) {
  $_SESSION['count'] = 0;
  }elseif(!empty($_POST['action']) && $_POST['action'] == 'count'){
    $_SESSION['count'] ++;
  }

  ?>

 <!DOCTYPE html>
 <html lang="ja">
 <head>
   <meta charset="UTF-8">
   <title>Document</title>
 </head>
 <body>
  <form action="" method="post">
    <input type="hidden" name="action" value="count">
    <input type="submit" value="count">

  </form>
   <?php 
    echo $_SESSION['count'];
    ?>
 </body>
 </html>


<!-- POSTを使うやり方 -->
  <?php 
  if (!isset($_POST['count'])) {
  $_POST['count'] = 0;
  }elseif(!empty($_POST['action']) && $_POST['action'] == 'count'){
    $_POST['count'] ++;
  }

  ?>

 <!DOCTYPE html>
 <html lang="ja">
 <head>
   <meta charset="UTF-8">
   <title>Document</title>
 </head>
 <body>
  <form action="" method="post">
    <input type="hidden" name="action" value="count">
    <input type="hidden" name="count" value="<?php echo $_POST['count']; ?>">
    <input type="submit" value="count">
  </form>
   <?php 
    echo $_POST['count'];
    ?>
 </body>
 </html>