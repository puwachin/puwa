const Koibumi = (function() {
  'use strict';

  const koibumiMessageVisibleTime = 6000;
  // お礼メッセージを表示する時間の長さを変更できます（単位はミリ秒。6000＝6秒）

  // ※==================================※
  //
  // ここから下は基本的にいじらないでください
  //
  // ※==================================※

  // スクリプトのURL取得
  const root = (() => {
    const scripts = document.getElementsByTagName("script");
    for (let i = scripts.length - 1; i >= 0; i--) {
      const match = scripts[i].src.match(/(^|.*\/)koibumi\.js$/);
      if (match) {
        return match[1];
      }
    }
    return '';
  })();

  const ajaxPath = root + '_ajax.php';

  // 各要素の取得
  const elements = {
    token: document.getElementById('koibumi_token'),
    limitIP: document.getElementById('koibumi_limitIP'),
    limitMessage: document.getElementById('koibumi_limitMessage'),
    alert: document.getElementById('koibumi_alert'),
    thanks: document.getElementById('koibumi_thanks'),
    nowLength: document.getElementById('koibumi_nowlength'),
    textInput: document.getElementById('koibumi_text')
  };

  const pathname = location.href;
  const pageTitle = document.title;('koibumi_limitMessage');

  //  お礼やエラーメッセージを表示させる
  const fadeout = function(element, delay) {
    setTimeout(() => element.classList.add('koibumifadeout'), delay);
    setTimeout(() => {
      element.style.display = "none";
      element.classList.remove('alert', 'success', 'koibumifadeout');
    }, delay + 1000);
  };

  // メッセージパターン
  const MESSAGES = {
    emptyMessage: 'メッセージを入力してください',
    tokenMismatch: 'トークンが一致しません',
    submissionDenied: '投稿を受け付けることができません',
    NGword: '投稿内にNGワードが含まれています',
    maxLengthExceeded: '文字数が制限を超えています',
    maxDayCount: '一日の上限送信回数を超えています',
  };

  // エラーメッセージを出す
  const showAlert = function(res) {
    elements.alert.innerHTML = res;
    elements.alert.classList.add('alert');
    elements.alert.style.display = "block";
    fadeout(elements.alert, 4000);
  }

  // お礼メッセージの表示
  const koibumiSendSuccess = function() {
    if(elements.thanks) {
      elements.thanks.style.display = "block";
      fadeout(elements.thanks, koibumiMessageVisibleTime);
    }
    elements.textInput.value = '';
  }

  // クリック後の挙動の制御
  const handleResponse = function(res) {
    if (res === 'success' || res === 'invalidAddress' || res === 'sendMailError') {
      koibumiSendSuccess();
      return;
    }
  
    const alertMessages = {
      token: MESSAGES.tokenMismatch,
      deny: MESSAGES.submissionDenied,
      NGword: MESSAGES.NGword,
      ip: MESSAGES.maxDayCount,
      text: `文字数が${elements.limitMessage.innerHTML}文字を超えています（${elements.textInput.value.length}文字）`,
    };
  
    const message = alertMessages[res] || '何か問題が起きたようです';
    showAlert(message);
  };
  
  // ページ読み込み時の初期操作
  const init = function() {
    elements.textInput.addEventListener('keyup', () => {
      if (elements.nowLength) {
        elements.nowLength.innerHTML = elements.textInput.value.length;
      }
    });
    
  // Ajax処理
  jQuery(document).ready( function(){
    jQuery.ajax({
      type: 'GET',
      url : ajaxPath,
    }).fail(function(){
      console.log('コイブミの初期設定に失敗しました。設置方法に誤りがないか確認してください。');
    }).done(function(res){
      try {
        const data = JSON.parse(res);
        // 必要なデータの妥当性をチェック
        if (!data || !Array.isArray(data) || data.length < 3) {
          showAlert('内部処理が上手くいかなかった可能性があります。');
        }
        elements.token.value = data[0];
        if(elements.limitIP) {
          elements.limitIP.innerHTML = data[1];
        }
        if(elements.limitMessage) {
        elements.limitMessage.innerHTML = data[2];
        }
        
        // 最大文字数をセット (数値であることを確認)
        const maxLength = parseInt(data[2], 10);
        elements.textInput.setAttribute('maxlength', maxLength);
      } catch (error) {
        showAlert('サーバーエラーが発生しました。');
      }
    });
  });
};

// イベントリスナーの登録
const addEventListeners = function() {
  jQuery('#koibumi_btn').on('click', handleKoibumiClick);
};

// コイブミ送信ボタンをクリックしたときの処理
const handleKoibumiClick = function(e) {
	e.preventDefault();
  let message = elements.textInput.value;
  if(message == '') {
    showAlert(MESSAGES.emptyMessage);
    return;
  }

    // ajax処理
    jQuery.post(ajaxPath, {
      path: pathname,
      title: pageTitle,
      message: message,
      token: elements.token.value,
      mode: 'check'
    }).fail(function(){
      console.log('コイブミの送信に失敗しました。設置方法に誤りがないか確認してください。');
    }).done(function(res){
      handleResponse(res);
    });
}

return {
  init,
  addEventListeners
};

})();

// ページ読み込み時の処理
jQuery(document).ready(function() {
  Koibumi.init();               // 初期化処理を実行
  Koibumi.addEventListeners();  // コイブミ送信時の処理を実行
});