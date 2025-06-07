<?php

///////////////////////////////////////////////////
// 個人サイト向けひとことフォーム コイブミ Ver2.0.4
// 製作者    ：ガタガタ
// サイト    ：https://do.gt-gt.org/
// ライセンス：MITライセンス
// 全文      ：https://ja.osdn.net/projects/opensource/wiki/licenses%2FMIT_license
// 公開日    ：2020.09.13
// 最終更新日：2025.03.25
//
// このプログラムはどなたでも無償で利用・複製・変更・
// 再配布および複製物を販売することができます。
// ただし、上記著作権表示ならびに同意意志を、
// このファイルから削除しないでください。
///////////////////////////////////////////////////

include_once(dirname(__FILE__).'/_core.php');
$url = $_SERVER['HTTP_REFERER'];
if(parse_url($url, PHP_URL_QUERY)) {
  $url = str_replace('?'.parse_url($url, PHP_URL_QUERY), '', $url);
  
}

if (isset($_POST['limitMessage']) && isset($_POST['limitPost']) && isset($_POST['showFavCards'])) {

  $newLimitMessage = $_POST['limitMessage'];
  $newLimitPost = $_POST['limitPost'];
  $newShowFav = $_POST['showFavCards'];
  $newPassword = $password;
  $newNoticeMail = $_POST['notice'];
  $newMailAddress = $_POST['mailaddress'];
  $newSenderMail = $_POST['senderaddress'];
  $changepass = false;

  if($_POST['newpw'] !== '') {
    if($_POST['newpw-confirm'] === $_POST['newpw']) {
      $newPassword = htmlspecialchars($_POST['newpw'], ENT_QUOTES, 'UTF-8');
      $changepass = true;
    }
  }

  $setting = array($newPassword, $newLimitMessage, $newLimitPost, $newShowFav, $newNoticeMail, $newMailAddress, $newSenderMail);
  
  $configFile = dirname(__FILE__).'/../../datas/setting/config.dat'; // config.dat のパス
  $denyFile = dirname(__FILE__).'/../../datas/setting/deny.dat';
  $NGFile = dirname(__FILE__).'/../../datas/setting/NGwords.dat';
  
  // ファイルが存在するか確認
  if (file_exists($configFile)) {
      // 現在のパーミッションを取得
      $currentPermissions = substr(sprintf('%o', fileperms($configFile)), -3);
  
      // パーミッションが 664 でない場合に変更
      if ($currentPermissions !== '664') {
          if (chmod($configFile, 0664)) {
          } else {
              echo "パーミッションの変更に失敗しました。";
          }
      }
  } else {
      echo "ファイルが見つかりません: {$configFile}";
  }

  $fp = fopen($configFile, 'w');

  foreach ($setting as $v) {
    fwrite($fp, $v . "\n");
  }
  // ファイルを閉じる
  fclose($fp);

  if(isset($_POST['ips']) && is_array($_POST['ips'])) {
    $ips = $_POST['ips'];
    $denyIPs = file($denyFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($ips as $ip) {
      $key = array_search($ip, $denyIPs);
      array_splice($denyIPs, $key, 1);
    }

    $fp = fopen($denyFile, 'w');

    foreach ($denyIPs as $v) {
      fwrite($fp, $v . "\n");
    }
    // ファイルを閉じる
    fclose($fp);

  }

  if(isset($_POST['newDenyIP']) && $_POST['newDenyIP'] !== '') {
    $newIP = $_POST['newDenyIP'];

    // ファイルが存在するか確認
    if (file_exists($denyFile)) {
      // 現在のパーミッションを取得
      $currentPermissions = substr(sprintf('%o', fileperms($denyFile)), -3);
    
      // パーミッションが 664 でない場合に変更
      if ($currentPermissions !== '664') {
         chmod($denyFile, 0664);
      }
    } else {
      $dir = dirname($denyFile);
      if (!is_dir($dir)) {
          mkdir($dir, 0664, true); // 必要に応じてディレクトリを作成
      }

      // 空のファイルを作成
      file_put_contents($denyFile, '');
    }
    
    $denyIPs = file($denyFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $denyIPs[] = $newIP;

    $fp = fopen($denyFile, 'w');

    foreach ($denyIPs as $v) {
      fwrite($fp, $v . "\n");
    }
    // ファイルを閉じる
    fclose($fp);

  }

  if(isset($_POST['words']) && is_array($_POST['words'])) {
    $deletewords = $_POST['words'];
    $NGwords = file($NGFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($deletewords as $deleteword) {
      $key = array_search($deleteword, $NGwords);
      array_splice($NGwords, $key, 1);
    }

    $fp = fopen($NGFile, 'w');

    foreach ($NGwords as $v) {
      fwrite($fp, $v . "\n");
    }
    // ファイルを閉じる
    fclose($fp);

  }

  if(isset($_POST['newNGword']) && $_POST['newNGword'] !== '') {
    $newword = $_POST['newNGword'];

    // ファイルが存在するか確認
    if (file_exists($NGFile)) {
      // 現在のパーミッションを取得
      $currentPermissions = substr(sprintf('%o', fileperms($NGFile)), -3);
    
      // パーミッションが 664 でない場合に変更
      if ($currentPermissions !== '664') {
         chmod($NGFile, 0664);
      }
    } else {
      $dir = dirname($NGFile);
      if (!is_dir($dir)) {
          mkdir($dir, 0664, true); // 必要に応じてディレクトリを作成
      }

      // 空のファイルを作成
      file_put_contents($NGFile, '');
    }

    $NGwords = file($NGFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    $NGwords[] = $newword;

    $fp = fopen($NGFile, 'w');

    foreach ($NGwords as $v) {
      fwrite($fp, $v . "\n");
    }
    // ファイルを閉じる
    fclose($fp);

  }

  if($changepass === true) {
      header("Location:../index.php?mode=logout");
  } else {
    header("Location:$url?mode=success");
  }
} else {
  header("Location:$url");
}

  ?>
