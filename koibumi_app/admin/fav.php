<?php
session_start();
$subtitle = ' | お気に入り';
include_once('inc/_header.php');
?>

<main>
  <?php include_once('inc/_sidebar.php'); ?>

  <div id="contents">
    <h2>お気に入り</h2>
    <?php include_once('inc/_selector.php'); ?>
    <?php
    $page = 1;
    if(isset($_GET["page"])) {
      $page = $_GET["page"];
    }
    $koibumiShowFav = new koibumiShowFav;
    echo $koibumiShowFav->showFav($page);

    ?>
  </div>
</main>

<?php include_once('inc/_footer.php'); ?>
