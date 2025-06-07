<?php
session_start();
$subtitle = ' | ログ';
include_once('inc/_header.php');
?>

<main>
  <?php include_once('inc/_sidebar.php'); ?>

  <div id="contents">
    <h2>コイブミ動作ログ</h2>
    <?php
    echo showLogs::createLogs();
    ?>
  </div>
</main>

<?php include_once('inc/_footer.php'); ?>
