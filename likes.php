<?php 
  session_start();
  require('dbconnect.php');

  echo '<br>';
  echo '<br>';
  var_dump($_SESSION);
  echo '<br>';
  var_dump($_GET);
  echo '<br>';

  $count_sql = 'SELECT COUNT(*) AS `count` FROM `likes` WHERE `member_id` = ? AND `diary_id` = ?';
  $count_data = array($_SESSION['id'], $_GET['diary_id']);
  $count_stmt = $dbh->prepare($count_sql);
  $count_stmt->execute($count_data);
  $count = $count_stmt->fetch(PDO::FETCH_ASSOC);
  var_dump($count);

  if ($count['count'] == 0) {
    $sql = 'INSERT INTO `likes` SET `member_id` = ?, `diary_id` = ?';
    $data = array($_SESSION['id'], $_GET['diary_id']);
    $stmt = $dbh->prepare($sql);
    $stmt->execute($data);
  }elseif ($count['count'] == 1) {
    $delete_sql = 'DELETE FROM `likes` WHERE `member_id` = ? AND `diary_id` = ?';
    $delete_data = array($_SESSION['id'], $_GET['diary_id']);
    $delete_stmt = $dbh->prepare($delete_sql);
    $delete_stmt->execute($delete_data);
  }

  header('Location: index.php');
  exit;
 ?>