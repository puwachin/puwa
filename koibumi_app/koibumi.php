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

header('Content-Type: text/html; charset=UTF-8');

$include = get_included_files();
if (array_shift($include) === __FILE__) {
    die('このファイルへの直接のアクセスは禁止されています。');
}

$koibumiVersion = '2.0.4';

class koibumiConfig {

  private static $instance = null;
  private $settings = [];

  private function __construct() {
      // _config.php をインクルードして設定値を取得
      $settingsFile = dirname(__FILE__) . '/admin/inc/_config.php';

      if (file_exists($settingsFile)) {
          include $settingsFile;

          date_default_timezone_set("Asia/Tokyo");

          // _config.php 内の変数をクラスの設定に取り込む
          $this->settings = [
              'password' => $password ?? 'pass',
              'limitMessage' => $limitMessage ?? 5,
              'limitPost' => $limitPost ?? 10,
              'showFavCards' => $showFavCards ?? 20,
              'noticeMail' => $noticeMail ?? '',
              'noticeAddress' => $noticeAddress ?? '',
              'senderAddress' => $senderAddress,
              'today' => date("Ymd"),
              'time' => date("H:i:s"),
              'csvToday' => 'datas/' . date("Ymd") . '.csv',
              'visitorIP' => $_SERVER["REMOTE_ADDR"],
          ];
      } else {
        throw new Exception("設定ファイルが見つかりません: $settingsFile");
      }
  }

  public static function getInstance() {
      if (self::$instance === null)  self::$instance = new self();
      return self::$instance;
  }

  public function get($key) {
      return $this->settings[$key] ?? null;
  }
}

class Logger {
  private static $logDir = __DIR__ . '/datas/setting'; // ディレクトリパス
  private static $logFile = '/log.dat';              // ファイル名
  public static $maxLines = 1000;                   // 保持する最大行数
  private $config;
  
  // コンストラクタ宣言
  public function __construct() {
    var_dump($this->config);
  }

  public static function log($message) {
    $config = KoibumiConfig::getInstance();
    if (!is_dir(self::$logDir)) {
      self::initializeLogDirectory();
    }

    $fullLogPath = self::$logDir . self::$logFile;

    if (!file_exists($fullLogPath)) {
      self::initializeLogFile($fullLogPath);
    }

    $formattedDate = DateTime::createFromFormat('Ymd', $config->get('today'))->format('Y-m-d');
    $timestamp = $formattedDate.' '.$config->get('time');
    $logMessage = "[{$timestamp}] {$message}" . PHP_EOL;

    // 新しいログを先頭に追加
    self::prependLog($fullLogPath, $logMessage);
  }

  private static function initializeLogDirectory() {
    if (!mkdir(self::$logDir, 0755, true)) {
      throw new Exception("ログディレクトリの生成に失敗しました：" . self::$logDir);
    }
  }

  private static function initializeLogFile($filePath) {
    $initialMessage = "ログファイルを生成しました：" . date('Y-m-d H:i:s') . PHP_EOL;
    file_put_contents($filePath, $initialMessage);
  }

  private static function prependLog($filePath, $newLog) {
    // 現在のログを読み込む
    $existingLogs = file_exists($filePath) ? file_get_contents($filePath) : '';

    // 新しいログを先頭に結合し、保存
    $updatedLogs = $newLog . $existingLogs;
    file_put_contents($filePath, $updatedLogs);

    // 行数が多すぎる場合、トリム処理を実行
    self::trimLogFile($filePath);
  }

  private static function trimLogFile($filePath) {
    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    if (count($lines) > self::$maxLines) {
      // 新しい行が上部にあるので、古い行を末尾から削除
      $lines = array_slice($lines, 0, self::$maxLines);
      file_put_contents($filePath, implode(PHP_EOL, $lines) . PHP_EOL);
    }
  }
}

class showLogs {
  private static $logFile = __DIR__ . '/datas/setting/log.dat'; 

  public static function createLogs() {
    $maxLines = Logger::$maxLines;
    $html = '<p>最大'.$maxLines.'件まで保持できます。</p>';
    if(file_exists(self::$logFile)) {
      $lines = file(self::$logFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
      $html .= '<table class="logs">';
      foreach($lines as $line) {
          $html .= '<tr><td>'
          . $line
          . '</td></tr>';
        }
      $html .= '</table>';
    } else {
      $html .= '<p>ログはまだありません。</p>';
    }

    return $html;
  }
}

class koibumiCheckSendData {

  // URL名がindex.htmlもしくはindex.phpで終わる場合はURLを丸める
  public static function checkURL($url) {
    $filenames = array('index.html', 'index.php');
    foreach ($filenames as $filename) {
      if (strpos($url, $filename) !== false)  $url = rtrim($url, $filename);
    }
    return $url;
  }

  // タグなどの送信を拒否
  public static function entity($txt) {
    $newTxt = htmlentities($txt);
    return $newTxt;
  }

  public static function doublequotation($txt) {
    $newTxt = '"' .$txt. '"';
    return $newTxt;
  }

}

class koibumiToken {
  private $config;
  
  // コンストラクタ宣言
  public function __construct() {
    $this->config = KoibumiConfig::getInstance();
  }

  public function makeToken() {
    $limitPost = $this->config->get('limitPost');
    $limitMessage = $this->config->get('limitMessage');

    // 暗号学的に安全なランダムなバイナリを生成し、それを16進数に変換することでASCII文字列に変換します
    if (function_exists('random_bytes')) {
      $toke_byte = random_bytes(16);
    } elseif (function_exists('openssl_random_pseudo_bytes')) {
        $toke_byte = openssl_random_pseudo_bytes(16);
    } else {
        // 最後の手段として mt_rand() ベースの疑似ランダムを使用
        $toke_byte = '';
        for ($i = 0; $i < 16; $i++) {
            $toke_byte .= chr(mt_rand(0, 255));
        }
    }
  
    $csrf_token = bin2hex($toke_byte);

    // 生成したトークンをセッションに保存します
    $_SESSION['csrf_token'] = $csrf_token;
    $res = array($csrf_token, $limitPost, $limitMessage);
    return json_encode($res);
  }
}

class koibumiSendEveryMail {
  private $config;
  
  // コンストラクタ宣言
  public function __construct() {
    $this->config = KoibumiConfig::getInstance();
  }

  public function createMail($postPath, $title, $message) {
    $message = str_replace('\\n', PHP_EOL, $message);
    $txt = "";
    $txt .= "コイブミからメッセージが送信されました。" . PHP_EOL . PHP_EOL;
    $txt .= "---------------------------------------" . PHP_EOL;
    $txt .= "送信日時：" .  date("Y/m/d H:i:s")  . PHP_EOL;
    $txt .= "送信元ページ：" . $title . "（" . $postPath . "）" . PHP_EOL;
    $txt .= "以下メッセージ：" . PHP_EOL . PHP_EOL;
    $txt .= html_entity_decode($message) . PHP_EOL . PHP_EOL;
    $txt .= "---------------------------------------" . PHP_EOL;
    return $txt;
  }

  public function sendEveryMail($postPath, $title, $message) {
    $noticeAddress = $this->config->get('noticeAddress');
    $senderAddress = $this->config->get('senderAddress');

    if (!filter_var($noticeAddress, FILTER_VALIDATE_EMAIL)) {
      error_log("Invalid email address: " . $noticeAddress);
      $Message = "メールアドレスの形式が誤っているようです。";
      Logger::log($Message); // エラーを記録
      return 'invalidAddress';
    }

    $subject = "【コイブミ】メッセージを受信しました";
    $text = $this->createMail($postPath, $title, $message);
    $headers = "From: " . $senderAddress . "\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";
    $headers .= "Content-Transfer-Encoding: 8bit\r\n";

    if (!mb_send_mail($noticeAddress, $subject, $text, $headers)) {
      $Message = "コイブミ通知メールの送信に失敗しました。";
      Logger::log($Message); // エラーを記録
      return 'sendMailError';
    } else {
      $Message = "コイブミ通知メールを送信しました。";
      Logger::log($Message); // エラーを記録
    }

    return true;
  }
}

class csvHandler {
  // 指定された日のデータをチェックする
  public static function openCSV($day) {
    $filename = 'datas/'.$day.'.csv';
    if (!file_exists($filename)) return false;

    $csvArray = [];
    
    // ファイルの行を読み込み
    $file = fopen($filename, 'r');
    while (($row = fgetcsv($file, null, ",", "\"", "\\")) !== false) {
      // 行ごとにCSVを解析して配列に保存
      $csvArray[] = $row;
    }
    fclose($file);

    return $csvArray;
  }
}

class koibumi {
  private $config;

  	// コンストラクタ宣言
  	public function __construct() {
      $this->config = KoibumiConfig::getInstance();
  	}
  
    // PHP5.5以下でもarray_columnに相当する関数を使う
    public function check_column($target_data, $column_key, $index_key = null) {
      if (is_array($target_data) === FALSE || count($target_data) === 0) return false;

      $result = array();
      foreach ($target_data as $array) {
        if (array_key_exists($column_key, $array) === FALSE) continue;
        if (is_null($index_key) === FALSE && array_key_exists($index_key, $array) === TRUE) {
          $result[$array[$index_key]] = $array[$column_key];
          continue;
        }
        $result[] = $array[$column_key];
      }

      if (count($result) === 0) return false;
      return $result;
    }
    
    public function checkDenyIP($visitorip) {
      $filePath = 'datas/setting/deny.dat';
      if(!file_exists($filePath)) return false;
      $denyIPs = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
  
      foreach ($denyIPs as $denyIP) {
          // ワイルドカードを正規表現に変換
          $pattern = str_replace(['.', '*'], ['\.', '.*'], $denyIP);
          $pattern = '/^' . $pattern . '$/';
  
          // 訪問者のIPと一致するかチェック
          if (preg_match($pattern, $visitorip)) return true;
      }
      return false;
    }

    // 今日の分のデータの中に同一アドレスIPが記録されているか確認する
    public function checkIP($ip) {
      $limitPost = $this->config->get('limitPost');

      $csvData = csvHandler::openCSV($this->config->get('today'));
      if($csvData === false) return false;
      $csvKeys = $this->check_column($csvData, 1);
      if($csvKeys === false)  return false;

      // 訪問者と同じIPアドレスが、今日の記録のうちにいくつあるかを数える
      $countKeys = count(array_keys($csvKeys, $ip, true));
      return ($countKeys >= $limitPost) ? true : false;
    }

    public function checkNGword($message) {
      $filePath = 'datas/setting/NGwords.dat';
  
      // ファイルが存在しない場合はチェックしない
      if (!file_exists($filePath)) return false;
  
      $NGwords = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
      $NGwords = array_filter($NGwords, 'strlen');
  
      // NGワードがない場合はチェックしない
      if (empty($NGwords)) return false;
  
      $pattern = '/' . implode('|', array_map('preg_quote', $NGwords)) . '/';
      return preg_match($pattern, $message) === 1;
  }
  
    // メッセージを記録する
    public function koibumiCount($postPath, $message, $title, $token) {
      $limitMessage = $this->config->get('limitMessage');
      $noticeMail = $this->config->get('noticeMail');
      $koibumiSendEveryMail = new koibumiSendEveryMail;

      // スパム防止のトークン確認
      if ($token === $_SESSION['csrf_token']) {

        if ($this->checkIP($this->config->get('visitorIP')) === true) {
          // 一日の投稿数上限を超える場合は投稿を拒否する
          $Message = '一日の投稿回数上限を超える投稿を拒否しました。';
          Logger::log($Message); // エラーを記録
          echo 'ip';
        } elseif ($this->checkNGword($message) === true) {
          // NGワードを含む投稿を拒否する
          $Message = 'NGワード「'.$message.'」を含む投稿を拒否しました。';
          Logger::log($Message); // エラーを記録
          echo 'NGword';
        } elseif (mb_strlen($message, 'UTF-8') > $limitMessage) {
          // 制限文字数を超える投稿を拒否する
          $Message = '制限文字数を超える投稿を拒否しました。';
          Logger::log($Message); // エラーを記録
          echo 'text';
        } elseif ($this->checkDenyIP($this->config->get('visitorIP')) === true) {
          // 指定されたIPアドレスからの投稿を拒否する
          $Message = 'IPアドレス：'.$this->config->get('visitorIP')."からの送信を拒否しました。";
          Logger::log($Message); // エラーを記録
          echo 'deny';
        } else {
          // そうでなければ投稿を受け付ける
          $newtitle = koibumiCheckSendData::doublequotation($title);
          $message = preg_replace('/\r\n|\r|\n/', '\\n', $message);
          $newmessage = koibumiCheckSendData::doublequotation($message);
          $data = array($postPath, $this->config->get('visitorIP'), $newtitle, $this->config->get('time'), $newmessage);
          $fp = fopen($this->config->get('csvToday'), 'a');
          if(flock($fp, LOCK_EX)) {
            $line = implode(',' , $data);
            fwrite($fp, $line . "\n");
            flock($fp, LOCK_UN);
          }
          fclose($fp);
          if ($noticeMail === 'every') {
            $mailsend = $koibumiSendEveryMail->sendEveryMail($postPath, $title, $message);
            switch ($mailsend) {
              case true:
                echo 'success';
                break;
              default:
                return $mailsend;
                break;
            }
          } else {
            echo 'success';
          }
          $Message = 'コイブミを受信しました。';
          Logger::log($Message);
        }
      } else {
        // トークンに問題が発生した場合
        $Message = 'コイブミ送信時、トークンに問題が発生したようです。';
        Logger::log($Message); // エラーを記録
        echo 'token';
      }

    }

} // end class koibumi

 ?>
