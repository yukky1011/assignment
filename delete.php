<?php 
  require('dbconnect.php');
  // DELETE
  if (!empty($_GET['action']) && $_GET['action'] == 'delete') {
    $delete_sql = 'UPDATE `diary` SET `delete_flag` = 1  WHERE `diary_id` = ? ';
    $delete_data = array($_GET['diary_id']);
    $delete_stmt = $dbh->prepare($delete_sql);
    $delete_stmt->execute($delete_data);

    header('Location: index.php');
    exit;

  }
 ?>