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

$include = get_included_files();
if (array_shift($include) === __FILE__) {
    die('このファイルへの直接のアクセスは禁止されています。');
}

include_once(dirname(__FILE__).'/_config.php');
include_once(dirname(__FILE__).'/../../koibumi.php');

class adminCsvHandler extends csvHandler {
    public static function openCSV($day) {
      // 渡ってきた値が日付のみであれば、CSVパスに変換する
      if(preg_match('/^[0-9]{8}$/', $day)) {
        $filename = dirname(__FILE__). '/../../datas/'.$day.'.csv';
      } else {
        $filename = $day;
      }

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

class koibumiMakeCard {
  public static function messageCard($datas, $day) {
    $favfilename = dirname(__FILE__) . '/../../datas/fav/' . $day . '.csv';
    $favDatas = file_exists($favfilename) ? adminCsvHandler::openCSV($favfilename) : [];
    $cards = self::processData($datas, $favDatas);

    $html = '';
    foreach ($cards as $ip => $card) {
      $html .= '<div class="wrap_message">';
      foreach ($card as $url => $values) {
        $html .= self::generateCardHTML($url, $values, $day);
      }
      $html .= '</div>';
    }
    return $html;
  }

  private static function processData($datas, $favDatas) {
    $cards = [];
    foreach ($datas as $i => $data) {
      if (empty($data[3])) continue;

      $fav = in_array($data, $favDatas, true);
      $cards[$data[1]][$data[0]][] = [
        'message' => $data[4],
        'time' => $data[3],
        'title' => $data[2],
        'num' => $i,
        'fav' => $fav
      ];
    }
    return $cards;
  }

  private static function generateCardHTML($url, $values, $day) {
    $html = '';
    foreach ($values as $value) {
      $message = str_replace('\\n', PHP_EOL, $value['message']);
      $message = html_entity_decode($message, ENT_QUOTES | ENT_HTML5, 'UTF-8');
      $html .= '<div class="inner_message">';
      $html .= '<p class="message">' . nl2br(htmlspecialchars($message)) . '</p>';
      $html .= '<div class="commands">';
      $html .= '<p class="meta time">' . htmlspecialchars($value['time']) . '</p>';
      $html .= self::generateDeleteForm($value['num'], $day);
      $html .= self::generateFavoriteForm($value['fav'], $value['num'], $day);
      $html .= self::generateCheckbox($value['num'], $day);
      $html .= '</div>';
      $html .= '</div>';
    }
    $html .= '<p class="meta url"><a href="' . htmlspecialchars($url) . '" target="_blank">送信元：' . htmlspecialchars($values[0]['title']) . '</a></p>';
    return $html;
  }

  private static function generateDeleteForm($num, $day) {
    return '<form method="post" action="inc/_func.php" class="single" onsubmit="return submitDelete()">'
      . '<input type="hidden" name="mode" value="delete">'
      . '<input type="hidden" name="data" value="' . htmlspecialchars($day . '-' . $num) . '">'
      . '<button type="submit"><span class="material-icons">delete</span></button>'
      . '</form>';
  }

  private static function generateFavoriteForm($fav, $num, $day) {
    $mode = $fav ? 'defav' : 'favorite';
    $iconClass = $fav ? 'favorited' : '';
    return '<form method="post" action="inc/_func.php" class="single">'
      . '<input type="hidden" name="mode" value="' . $mode . '">'
      . '<input type="hidden" name="data" value="' . htmlspecialchars($day . '-' . $num) . '">'
      . '<button type="submit"><span class="material-icons ' . $iconClass . '">favorite</span></button>'
      . '</form>';
  }

  private static function generateCheckbox($num, $day) {
    return '<label class="label">'
      . '<input type="checkbox" name="datas[]" value="' . htmlspecialchars($day . '-' . $num) . '" form="archive">'
      . '<span class="checkbox"></span>'
      . '</label>';
  }
}

class koibumiCreateReport {
  public static function monthlyReport($month) {
    $csvs = glob(dirname(__FILE__, 3) . '/datas/*.csv') ?: []; // ここで false を防ぐ
    $days = array_map(function($data) use ($month) {
      if (preg_match('/\/(\d{8})\.csv$/', $data, $matches)) {
          return strpos($matches[1], $month) === 0 ? $matches[1] : null;
      }
      return null;
  }, $csvs);
  
    
    $days = array_filter($days); // null を削除
    $monthlyReport = []; 
  
    foreach($days as $day) {
      if(adminCsvHandler::openCSV($day) === false) {
        continue;
      } else {
        $monthlyReport[$day] = adminCsvHandler::openCSV($day);
      }
    }

    $html = '';

    if (empty($monthlyReport)) {
      $html .= '<p>メッセージが見つかりませんでした。お手数をおかけしますが、以下の情報をフォーラムあてご報告ください。</p>';
      $html .= '<h4>$daysの値</h4>';
      $html .= '<pre>'.print_r($days, true).'</pre>';
      return $html;
    } else {
    krsort($monthlyReport);
      foreach($monthlyReport as $day => $datas) {
        if (!$timestamp = strtotime($day)) {
          $html = '<p>日付の形式が不正です。</p>';
          return $html;
        } elseif(!$datas) {
          $html .= '<p>不測のエラーにより、$datasの値がfalseになっています。お手数をおかけしますが、以下の情報をフォーラムあてご報告ください。URLやIPが映っている箇所は隠してくださってかまいません。</p>';
          $html .= '<h4>$daysの値</h4>';
          $html .= '<pre>'.print_r($days, true).'</pre>';
          $html .= '<h4>$csvsの値</h4>';
          $html .= '<pre>'.print_r($csvs, true).'</pre>';
          $html .= '<h4>$monthlyReportの値</h4>';
          $html .= '<pre>'.print_r($monthlyReport, true).'</pre>';
          return $html;
        }

        $html .= '<div class="wrap_month">';
        $html .= '<h3>' .date('Y年m月d日', $timestamp). '</h3>';
        $html .= koibumiMakeCard::messageCard($datas, $day);
        $html .= '</div>';
      }
    }

    return $html;
  }

  public static function weeklyReport() {
    define('SEC_PER_DAY', 86400);
    $now = time();
    $days = array();

    for($i=0;$i<7;$i++){
      $days[$i] = date("Ymd", $now - SEC_PER_DAY * $i);
    }

    $weeklyReport = array();

    foreach ($days as $day) {
      if(adminCsvHandler::openCSV($day) === false) {
        continue;
      } else {
        $weeklyReport[$day] = adminCsvHandler::openCSV($day);
      }
    }

    $html = '';

    if (empty($weeklyReport)) {
      $html .= '<p>メッセージはありません。</p>';
    } else{
      foreach($weeklyReport as $day => $datas) {
        $html .= '<div class="wrap_month">';
        $html .= '<h3>' .date('Y年m月d日',strtotime($day)). '</h3>';
        $html .= koibumiMakeCard::messageCard($datas, $day);
        $html .= '</div>';
      }
    }

    return $html;
  }

  public static function dailyReport($day) {
    $datas = array();
    $datas = adminCsvHandler::openCSV($day);
    $html = '';

    if (empty($datas)) {
      $html .= '<p>メッセージはありません。</p>';
    } else{
      $html .= '<h3>' .date('Y年m月d日',strtotime($day)). '</h3>';
      $html .= koibumiMakeCard::messageCard($datas, $day);
    }

    return $html;
  }

  public static function createMonthlyLists() {
    // データファイルを取得し、日付を分類
    $datas = glob("../datas/*.csv");
    if(!$datas) return;
    $dates = [];

    foreach ($datas as $data) {
        if (preg_match('/\d{8}/', $data, $matches)) {
          $day = $matches[0];
          $dateObj = DateTime::createFromFormat('Ymd', $day);
          $yyyy = $dateObj->format('Y');
          $mm = $dateObj->format('m');
          $dd = $dateObj->format('d');
          $dates[$yyyy][$mm][] = $dd;
        }
    }

    // 年と月を降順で並び替え
    krsort($dates);
    foreach ($dates as $year => &$months) { // 参照渡しにする
        krsort($months);
    }
    unset($months); // 参照を解除
    

    // HTML生成
    $html = '';
    $isFirstYear = true;

    foreach ($dates as $year => $months) {
        if ($isFirstYear) {
            // 最新の年のリストを生成
            foreach ($months as $month => $days) {
                $html .= '<ul class="list_month">';
                $html .= '<li class="month"><a href="archive.php?month=' . $year . $month . '">' . $year . '年' . $month . '月</a></li>';
                foreach ($days as $day) {
                    $html .= '<li><a href="archive.php?day=' . $year . $month . $day . '">' . $year . '年' . $month . '月' . $day . '日</a></li>';
                }
                $html .= '</ul>';
            }
            $isFirstYear = false;
        } else {
            // その他の年のリストを生成
            $html .= '<ul class="list_year">';
            $html .= '<li class="year"><a>' . $year . '年</a>';
            $html .= '<ul class="list_month child_ul">';
            foreach ($months as $month => $days) {
                $html .= '<ul class="list_month">';
                $html .= '<li class="month"><a href="archive.php?month=' . $year . $month . '">' . $year . '年' . $month . '月</a></li>';
                foreach ($days as $day) {
                    $html .= '<li><a href="archive.php?day=' . $year . $month . $day . '">' . $year . '年' . $month . '月' . $day . '日</a></li>';
                }
                $html .= '</ul>';
            }
            $html .= '</ul>';
            $html .= '</li></ul>';
        }
    }

    return $html;
  }

}

class koibumiShowFav {
  private $config;

  // コンストラクタ宣言
  public function __construct() {
    $this->config = KoibumiConfig::getInstance();
  }
  
  // 日付をファイル名から抽出するヘルパーメソッド
  private static function extractDayFromFilename($filename) {
      // ファイル名から日付部分を抽出
      if (preg_match('/(\d{8})/', $filename, $matches)) {
          return $matches[1]; // マッチした日付を返す
      }
      return null; // マッチしなかった場合
  }

  public function showFav($page) {
    $showFavCards = $this->config->get('showFavCards');
    $favfilenames = glob(dirname(__FILE__, 3). '/datas/fav/*.csv');
    $favDatas = array();

    foreach ($favfilenames as $favfilename) {
      $day = self::extractDayFromFilename($favfilename);
      if ($day !== null) {
          $favDatas[$day] = adminCsvHandler::openCSV($favfilename);
      }
    }

    if(empty($favDatas)) {
      return '<p>まだお気に入りのメッセージがありません。<br>
      特にうれしかったメッセージはお気に入りに登録して、いつでも見られるようにしましょう！</p>';
    }

    $favData = array();
    $setDays = array();
    foreach($favDatas as $day => $favdata) {
      foreach ($favdata as $data) {
        $filename = dirname(__FILE__, 3). '/datas/'.$day.'.csv';
        $defData = adminCsvHandler::openCSV($filename);
        if($defData) {
          $key = array_search($data, $defData);
          $data[] = $day;
          $setDays[] = $day;
          $data[] = $key;
          $favData[] = $data;
        }
      }
    }
    array_multisort($setDays, SORT_DESC, $favData);

    $nums = range($showFavCards * ($page-1), $showFavCards * $page - 1);

    $html = '';
    foreach ($nums as $num) {
      if(array_key_exists($num, $favData)) {
        $data = $favData[$num];
        $day = date('Y-m-d', strtotime($data[5]));
        $date = date('Ymd', strtotime($data[5]));
        $message = str_replace('\\n', PHP_EOL, $data[4]);
        $message = html_entity_decode($message, ENT_QUOTES | ENT_HTML5, 'UTF-8');

        $html .= '<div class="wrap_message">';
        $html .= '<div class="inner_message"><p class="message">'.nl2br(htmlspecialchars($message)).'</p>';
        $html .= '<div class="commands"><p class="meta time">'.$day.' '.$data[3].'</p><form method="post" action="inc/_func.php" class="single" onsubmit="return submitDelete()"><input type="hidden" name="mode" value="delete"><input type="hidden" name="data" value="'.$date.'-'.$data[6].'"><button type="submit"><span class="material-icons">delete</span></button></form>';
        $html .= '<form method="post" action="inc/_func.php" class="single"><input type="hidden" name="mode" value="defav"><input type="hidden" name="data" value="'.$date.'-'.$data[6].'"><button type="submit"><span class="material-icons favorited">favorite</span></button></form>';
        $html .= '<label class="label"><input type="checkbox" name="datas[]" value="'.$date.'-'.$data[6].'" form="archive"><span class="checkbox"></span></label></div>';
        $html .= '<p class="meta url"><a href="'.$data[0].'" target="_blank">送信元：'.$data[2].'</a></p>';
        $html .= '</div></div>';
      }
    }

    $nextPage = $page+1;
    $prevPage = $page-1;
    $keys = array_keys($favData);
    $key = array_pop($keys);

    $html .= '<div class="page">';

    if ($page > 1) {
      $html .= '<a href="?page='.$prevPage.'" class="prev"><span class="material-icons">navigate_before</span>前のページ</a><div></div>';
    }
    if ($key > $nums[$showFavCards-1]) {
      $html .= '<div></div><a href="?page='.$nextPage.'" class="next">次のページ<span class="material-icons">navigate_next</span></a>';
    }

    $html .= '</div>';

    return $html;
  }
}

class koibumiDeleteData {
  
  public static function deleteData($day, $num, $mode, $retData = null) {
    $filename = dirname(__FILE__). '/../../datas/'.$day.'.csv';

    if ($mode === 'default') {
      $arr = adminCsvHandler::openCSV($filename);
      $retData = $arr[$num];
      unset($arr[$num]);

      $fp = fopen($filename, 'w');
      // if( flock($fp, LOCK_SH) ) {

        foreach ($arr as $v) {
          $v[2] = koibumiCheckSendData::doublequotation($v[2]);
          $v[4] = koibumiCheckSendData::doublequotation($v[4]);
          $line = implode(',' , $v);
          fwrite($fp, $line . "\n");
        }
        // ファイルを閉じる
      //   flock($fp, LOCK_UN);
      // }
      fclose($fp);

      $d = adminCsvHandler::openCSV($filename);
      if( empty($d) ) {
        unlink($filename);
      }

      return $retData;

    } elseif($mode === 'fav') {

      $favfilename = dirname(__FILE__). '/../../datas/fav/'.$day.'.csv';
      $favarr = adminCsvHandler::openCSV($favfilename);
      if (!empty($favarr)) {
        $key = array_search($retData, $favarr);
        if($key !== false) {
          unset($favarr[$key]);

          $fp = fopen($favfilename, 'w');

          foreach ($favarr as $v) {
            $v[2] = koibumiCheckSendData::doublequotation($v[2]);
            $v[4] = koibumiCheckSendData::doublequotation($v[4]);
            $line = implode(',' , $v);
            fwrite($fp, $line . "\n");
          }
          fclose($fp);
        }
      }

      $d = adminCsvHandler::openCSV($favfilename);
      if( empty($d) ) {
        unlink($favfilename);
      }

      return $retData;
    }
  }
}

class koibumiShowDatas {
  public static function showDenyIP() {
    $html = '';
    if(!file_exists(dirname(__FILE__). '/../../datas/setting/deny.dat')) {
      $html = '<p>現在拒否しているIPアドレスはありません。</p>';
    } else {
      $IPs = file(dirname(__FILE__). '/../../datas/setting/deny.dat', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
      if( empty($IPs) ) {
        $html = '<p>現在拒否しているIPアドレスはありません。</p>';
      } else {

        $html .= '<table>';
        foreach($IPs as $IP) {
            $html .= '<tr>';
            $html .= '<th>'.$IP.'</th>';
            $html .= '<td><label>削除<input type="checkbox" name="ips[]" value="'.$IP.'"></label></td>';
            $html .= '</tr>';
        }
        $html .= '</table>';
      }
    }

    return $html;
  }

  public static function showNGwords() {
      $html = '';
      if(!file_exists(dirname(__FILE__). '/../../datas/setting/NGwords.dat')) {
        $html = '<p>現在NGワードはありません。</p>';
      } else {
        $words = file(dirname(__FILE__). '/../../datas/setting/NGwords.dat', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        if( empty($words) ) {
          $html = '<p>現在NGワードはありません。</p>';
        } else {

          $html .= '<table style="margin-top:20px;">';
          $html .= '<thead style="background:#e9e9e9;"><tr><th>NGワード</th><th>削除する</th></tr></thead>';
          foreach($words as $word) {
              $html .= '<tr>';
              $html .= '<th>'.$word.'</th>';
              $html .= '<td><label><input type="checkbox" name="words[]" value="'.$word.'"></label></td>';
              $html .= '</tr>';
          }
          $html .= '</table>';
        }
      }

    return $html;
  }
}

?>
