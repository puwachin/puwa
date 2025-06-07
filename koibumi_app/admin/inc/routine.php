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

class koibumiMailSender {
  public static function sendMail($to, $subject, $message, $from) {
    $headers = "From: " . $from;
  
    if (!mb_send_mail($to, $subject, $message, $headers)) {
      error_log("Failed to send email to " . $to);
      return 'sendMailError';
    }
    return true;
  }
}

class koibumiRoutine {
  public $config;
  
  // コンストラクタ宣言
  public function __construct() {
    $this->config = KoibumiConfig::getInstance();
  }

  public static function dailyreport($day, $datas) {
    $txt = '';
    $txt .= '◆' . date('Y年m月d日', strtotime($day)) . 'のコイブミ＝＝＝＝＝＝＝＝＝＝＝＝' . PHP_EOL . PHP_EOL;
  
    foreach ($datas as $data) {
      $message = html_entity_decode(str_replace('\\n', PHP_EOL, $data[4]));
      $cards[$data[1]][$data[0]][] = array(
        'message' => $message,
        'time' => $data[3],
        'title' => $data[2]
      );
    }
    $i = 1;
  
    foreach($cards as $ip => $card) {
      $txt .= 'コイブミ'.$i.'通目---------------------------' . PHP_EOL;
      foreach ($card as $url => $values) {
        foreach ($values as $k => $value) {
          $txt .= $value['message'] . '（'.$value['time'] . '送信）' . PHP_EOL;
          $txt .= PHP_EOL;
        }
        $txt .= PHP_EOL . '（送信元：'.$value['title'].'　'.$url.'）' . PHP_EOL;
        $txt .= '-----------------------------------------' . PHP_EOL . PHP_EOL;
      } // 送信元URLごとにメッセージを分ける
      $i++;
    } // IPごとにカードを分ける PHP_EOL . PHP_EOL;
  
    return $txt;
  }
  
  public static function loginURL() {
    // $url = (empty($_SERVER['HTTPS']) ? 'http://' : 'https://') . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
    // $url = str_replace('inc/routine.php', '', $url);
    // $txt = '管理画面ログイン：' . $url;
    $txt = '';
    return $txt;
  }
}

$routine = new koibumiRoutine();
$to = $routine->config->get('noticeAddress');
$from = $routine->config->get('senderAddress');

if($noticeMail === 'everyday') {
  $yesterday = date('Ymd', strtotime('-1 day'));
  $datas = adminCsvHandler::openCSV($yesterday);
  if($datas !== false) {
    $txt = '';
    $txt .= koibumiRoutine::dailyreport($yesterday, $datas);
    $txt .= koibumiRoutine::loginURL();

    if(koibumiMailSender::sendMail($to, '【コイブミ】デイリーレポートの送付', $txt, $from)) {
      $Message = "デイリーレポートを送付しました。";
      Logger::log($Message); // エラーを記録
    } else {
      $Message = "ERROR：デイリーレポートの送付に失敗しました。";
      Logger::log($Message); // エラーを記録
    }
  } else {
    $Message = "デイリーレポートを確認しましたが、データがありませんでした。";
    Logger::log($Message); // エラーを記録
  }
} elseif($noticeMail === 'weekly') {
  $end = date('Ymd', strtotime('-1 day'));
  $start = date('Ymd', strtotime('-1 week'));
  $diff = (strtotime($end) - strtotime($start)) / ( 60 * 60 * 24);
  $datas = array();
  for($i = 0; $i <= $diff; $i++) {
    $day = date('Ymd', strtotime($start . '+' . $i . 'days'));
    if(adminCsvHandler::openCSV($day) !== false) {
      $datas[$day] = adminCsvHandler::openCSV($day);
    }
  }

  if (empty($datas)) {
    $Message = "ウィークリーレポートを確認しましたが、データがありませんでした。";
    Logger::log($Message); // エラーを記録
    return;
  }

  $txt = '';
  $txt .= date('Y年m月d日', strtotime($start)) . '～' . date('Y年m月d日', strtotime($end)) . 'のウィークリーレポート' . PHP_EOL . PHP_EOL;

  foreach ($datas as $day => $data) {
    $txt .= koibumiRoutine::dailyreport($day, $data);
  }
  $txt .= koibumiRoutine::loginURL();

  if(koibumiMailSender::sendMail($to, '【コイブミ】ウィークリーレポートの送付', $txt, $from)) {
    $Message = "ウィークリーレポートを送付しました。";
    Logger::log($Message); // エラーを記録
  } else {
    $Message = "ERROR：ウィークリーレポートの送付に失敗しました。";
    Logger::log($Message); // エラーを記録
  }

} elseif($noticeMail === 'monthly') {
  $end = date('Ymd', strtotime('-1 day'));
  $start = date('Ymd', strtotime('-1 month'));
  $diff = (strtotime($end) - strtotime($start)) / ( 60 * 60 * 24);
  $datas = array();
  for($i = 0; $i <= $diff; $i++) {
    $day = date('Ymd', strtotime($start . '+' . $i . 'days'));
    if(adminCsvHandler::openCSV($day) !== false) {
      $datas[$day] = adminCsvHandler::openCSV($day);
    }
  }

  if (empty($datas)) {
    $Message = "マンスリーレポートを確認しましたが、データがありませんでした。";
    Logger::log($Message); // エラーを記録
    return;
  }

  $txt = '';
  $txt .= date('Y年m月d日', strtotime($start)) . '～' . date('Y年m月d日', strtotime($end)) . 'のマンスリーレポート' . PHP_EOL . PHP_EOL;

  foreach ($datas as $day => $data) {
    $txt .= koibumiRoutine::dailyreport($day, $data);
  }

  $txt .= koibumiRoutine::loginURL();

  if (koibumiMailSender::sendMail($to, '【コイブミ】マンスリーレポートの送付', $txt, $from)) {
    $Message = "マンスリーレポートを送付しました。";
    Logger::log($Message); // エラーを記録
  } else {
    $Message = "ERROR：マンスリーレポートの送付に失敗しました。";
    Logger::log($Message); // エラーを記録
  }
}
