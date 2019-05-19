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
    var CookieName = "playNum";
    var CookieNum = getCookie(CookieName);

    addNumber(CookieName, CookieNum);
  });

  //クリア回数

  var clearContent = $("#js-clear-view").val();
  console.log(clearContent);
  if (clearContent != null) {
    var CookieName = "clearNum";
    var CookieNum = getCookie(CookieName);

    addNumber(CookieName, CookieNum);
  }

  //ゲームオーバー回数
  //JQuery使って属性を取得する、JSON.parseで扱える形に変換
  var $script = $("#script");
  var result = JSON.parse($script.attr("data-param"));
  if (result === 1) {
    var CookieName = "overNum";
    var CookieNum = getCookie(CookieName);

    addNumber(CookieName, CookieNum);
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

      if (targetCookie.substring(0, valueIndex) === key) {
        return decodeURIComponent(targetCookie.slice(valueIndex + 1));
      }
    }
    return 0;
  }

  function addNumber(cookieName, cookieNum) {
    if (cookieNum !== null) {
      cookieNum++;
      document.cookie = cookieName + "=" + cookieNum;
    } else {
      document.cookie = cookieName + "=1";
    }
  }
});
