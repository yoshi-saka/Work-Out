<?php
// ログイン認証・自動ログアウト
// ログインしている場合
if(!empty($_SESSION['login_date'])){
  debug('ログイン済みユーザーです。');
  
  // 最終ログイン日時＋有効期限を超えていた場合
  if(($_SESSION['login_date'] + $_SESSION['login_limit']) < time()){
    debug('ログイン有効期限オーバーです。');

    // セッション削除
    session_destroy();
    header("Location:login.php");
  }else{
    debug('ログイン有効期限以内です。');
    // 最終ログイン日時を現在日時に更新
    $_SESSION['login_date'] = time();

    // $_SERVER['PHP_SELF']はドメインからのパスを返すため
    // basename関数を使うことでファイル名だけを取り出せる
    if(basename($_SERVER['PHP_SELF']) === 'login.php'){
      debug('マイページへ遷移します。');
      // マイページへ
      header("Location:mypage.php");
    }
  }
}else{
  debug('未ログインユーザーです。');
  if(basename($_SERVER['PHP_SELF']) !== 'login.php'){
    // ログインページへ
    header("Location:login.php");
  }
}


?>