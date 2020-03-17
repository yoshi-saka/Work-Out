<?php 

// 共通変数・関数ファイルの読み込み
require('function.php');
debug('-----------------------------------------------------------');
debug('  パスワード再発行メール送信ページ  ');
debug('-----------------------------------------------------------');
debugLogStart();


// 画面処理
// POST送信されていた場合
if(!empty($_POST)){
  debug('POST送信があります。');
  debug('POST情報：'.print_r($_POST, true));

  // 変数にPOST情報を代入
  $email = $_POST['email'];

  // 未入力チェック
  validRequired($email, 'email');

  if(empty($err_msg)){
    debug('未入力チェックOK。');

    // emailの形式チェック
    validEmail($email, 'email');
    // emailの最大文字数チェック
    validMax($email, 'email');

    if(empty($err_msg)){
      debug('バリデーションOK。');

      // 例外処理
      try{
        $dbh = dbConnect();
        $sql = 'SELECT count(*) FROM users WHERE email = :email AND delete_flg =0';
        $data = array(':email' => $email);
        $stmt = queryPost($dbh, $sql, $data);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        //  emailがDBに登録されている場合
        if($stmt && array_shift($result)){
          debug('クエリ成功。DB登録あり。');
          $_SESSION['msg_success'] = 'メールが送信されました。';

          // 認証キー生成
          $auth_key = makeRandKey();

          // メールを送信
          $from = 'aaaaaa@gmail.com';
          $to = $email;
          $subject = '「パスワード再発行認証」';
          $comment = <<<EOT
本メールアドレス宛にパスワード再発行のご依頼がありました。
下記のURLにて認証キーをご入力いただくとパスワードが再発行されます。

パスワード再発行認証キー入力ページ：http://http://localhost:8888/op/php/webs-cp/passReissue.php
認証キー：{$auth_key}
*認証キーの有効期限は30分となります。

認証キーを再発行されたい場合は下記ページより再度再発行をお願い致します。
http://http://localhost:8888/op/php/webs-cp/passReissue.php

////////////////////////////////////////////////
ウェブカツマーケットカスタマーセンター
URL aaaaaaaaaaaa
email aaaaaaaaaa
////////////////////////////////////////////////
EOT;
          sendMail($from, $to, $subject, $comment);

          // 認証に必要な情報をセッションへ保存
          $_SESSION['auth_key'] = $auth_key;
          $_SESSION['auth_email'] = $email;
          // 現在時刻より30分後のUNIXタイムスタンプを入れる
          $_SESSION['auth_key_limit'] = time() + (60 * 30);

          debug('セッション変数の中身：'.print_r($_SESSION, true));
          // 認証キー入力ページへ
          header("Location:passRemindRecieve.php");
        }else{
          debug('クエリに失敗したかDBに登録のないemailが入力されました。');
          $err_msg['common'] = 'エラーが発生しました。';
        }
      }catch(Exception $e){
        error_log('エラー発生：'.$e->getMessage());
        $err_msg['common'] = 'エラーが発生しました。';
      }
    }
  }
}
?>


<?php 
$siteTitle = "パスワード再発行メール";
require('head.php');
?>
<style>
.underline{
  text-decoration: underline;
}
.form{
  width: 400px;
}
</style>
<body>
<!-- メニュー -->
<?php require('header.php'); ?>

<!-- メインコンテンツ -->
<div class="content">

<!-- メイン -->
<section class="section">
<div class="container">
<form method="post" class="form">
<p>ご指定のメールアドレス宛にパスワード再発行用のURLと認証キーをお送り致します。</p>
<div class="area-msg">
<?php echo getErrMsg('common'); ?>
</div>

<label class="<?php echo getErr('email'); ?>">
<input type="text" name="email" placeholder="メールアドレス" value="<?php echo getFormData('email'); ?>">
</label>
<div class="area-msg">
<?php echo getErrMsg('email'); ?>
</div>

<div class="btn-container">
<input type="submit" value="送信" class="btn">
</div>

</form>
</div>
<a href="mypage.php" class="underline">&lt; マイページへ戻る</a>
</section>
</div>

<!-- フッター -->
<?php require('footer.php'); ?>