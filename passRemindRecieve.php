<?php 

// 共通変数・関数ファイルの読み込み
require('function.php');
debug('-----------------------------------------------------------');
debug('  パスワード再発行認証キー入力ページ  ');
debug('-----------------------------------------------------------');
debugLogStart();

//セッションに認証キーがあるか確認
if(empty($_SESSION['auth_key'])){
  // 認証キーを送信
  header("Location:passReissue.php");
}

// 画面処理
// POST送信されていた場合
if(!empty($_POST)){
  debug('POST送信があります。');
  debug('POST情報：'.print_r($_POST, true));

  // 認証キーを代入
  $auth_key = $_POST['token'];

  // 未入力チェック
  validRequired($auth_key, 'token');

  if(empty($err_msg)){
    debug('未入力チェックOK。');

    // 固定数チェック
    validLength($auth_key, 'token');
    // 半角チェック
    validHalf($auth_key, 'token');

    if(empty($err_msg)){
      debug('バリデーションOK。');

      if($auth_key !== $_SESSION['auth_key']){
        $err_msg['common'] = '正しくありません。';
      }
      if(time() > $_SESSION['auth_key_limit']){
        $err_msg['common'] = '有効期限が切れています。';
      }

      if(empty($err_msg)){
        debug('認証OK。');

        // パスワード生成
        $pass = makeRandKey();

        // 例外処理
        try{
          $dbh = dbConnect();
          $sql = 'UPDATE users SET password = :pass WHERE email = :email AND delete_flg = 0';
          $data = array(':email' => $_SESSION['auth_email'], ':pass' => password_hash($pass, PASSWORD_DEFAULT));
          $stmt = queryPost($dbh, $sql, $data);

          if($stmt){
            debug('クエリ成功。');
            
            // メールを送信
            $from = 'aaaaaa@gmail.com';
            $to = $_SESSION['auth_email'];
            $subject = '【パスワード再発行完了】';
            $comment = <<<EOT
本メールアドレス宛にパスワードの再発行を致しました。
下記のURLにて再発行パスワードをご入力いただき、ログインください。

ログインページ：http://http://localhost:8888/op/php/webs-cp/login.php
再発行パスワード：{$pass}
※ログイン後、パスワードのご変更をお願い致します

/////////////////////////////////////////
カスタマーセンター
URL
email 
/////////////////////////////////////////
EOT;
           sendMail($from, $to, $subject, $comment);

          //  セッション削除
          session_unset();
          $_SESSION['msg_success'] = 'メールを送信しました';
          debug('セッション変数の中身：'.print_r($_SESSION, true));
          // ログインページへ
          header("Location:login.php");
          }else{
            debug('クエリに失敗しました。');
            $err_msg['common'] = 'エラーが発生しました。';
          }
        }catch(Exception $e){
          error_log('エラー発生：'.$e->getMessage());
          $err_msg['common'] = 'エラーが発生しました。';
        }
      }
    }
  }
}
?>


<?php 
$siteTitle = "パスワード再発行認証";
require('head.php');
?>
<style>
.underline{
  text-decoration: underline;
}
</style>
<body>
<!-- メニュー -->
<?php require('header.php'); ?>
<p id="js-show-msg">
<?php echo getSessionFlash('msg_success'); ?>
</p>

<!-- メインコンテンツ -->
<div class="content">

<!-- メイン -->
<section class="section">
<div class="container">
<form method="post" class="form">
<p>ご指定のメールアドレスをお送りしました。【パスワード再発行認証】メール内にある「認証」をご入力ください。</p>
<div class="area-msg">
<?php echo getErrMsg('common'); ?>
</div>

<label class="<?php echo getErr('token'); ?>">

認証キー
<input type="text" name="token" id="<?php echo getFormData('token'); ?>">
</label>
<div class="area-msg">
<?php echo getErrMsg('token'); ?>
</div>

<div class="btn-container">
<input type="submit" value="送信" class="btn">
</div>

</form>
</div>
<a href="passReissue.php" class="underline">&lt; パスワード再発行メールを再度送信する</a>
</section>
</div>

<!-- フッター -->
<?php require('footer.php'); ?>