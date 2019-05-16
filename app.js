$(function() {
  //==================
  //キャラ選択モーダル
  //==================
  $("#js-start-modal").on("click", function() {
    $("#js-target-modal").show();
    $(".js-modal-cover").show();
  });

  //==================
  //プレイログのcookie保存
  //==================

  //プレイ回数
  $("#js-start-modal").on("click", function() {
    var playNum = getCookie("playNum");

    if (playNum) {
      playNum++;
      document.cookie = "playNum=" + playNum;
    } else {
      document.cookie = "playNum=1";
    }
  });

  //クリア回数
  $("#js-clear-view").on("click", function() {
    var clearNum = getCookie("clearNum");

    if (clearNum) {
      clearNum++;
      document.cookie = "clearNum=" + clearNum;
    } else {
      document.cookie = "clearNum=1";
    }
  });

  //ゲームオーバー回数
  //JQuery使って属性を取得する、JSON.parseで扱える形に変換
  var $script = $("#script");
  var result = JSON.parse($script.attr("data-param"));
  if (result === 1) {
    var overNum = getCookie("overNum");
    if (overNum) {
      overNum++;
      document.cookie = "overNum=" + overNum;
    } else {
      document.cookie = "overNum=1";
    }
  }

  //各ログをcookieから取り出し描画する
  var playLog = "プレイ回数:" + getCookie("playNum");
  var clearLog = "クリア回数:" + getCookie("clearNum");
  var overLog = "ゲームオーバー回数:" + getCookie("overNum");

  $("#js-play-log").text(playLog);
  $("#js-clear-log").text(clearLog);
  $("#js-over-log").text(overLog);

  //cookieの取得
  function getCookie(key) {
    //cookieに入った値をすべて取得
    var cookieString = document.cookie;

    // 要素ごとに;で区切られているので;で切り出しを行う
    var cookieKeyArray = cookieString.split(";");

    for (var i = 0; i < cookieKeyArray.length; i++) {
      var targetCookie = cookieKeyArray[i];

      targetCookie = targetCookie.replace(/^\s+|\s+$/g, "");

      var valueIndex = targetCookie.indexOf("=");

      var targetSubst = targetCookie.substring(0, valueIndex);

      if (targetCookie.substring(0, valueIndex) === key) {
        return decodeURIComponent(targetCookie.slice(valueIndex + 1));
      }
    }
    return 0;
  }
});
