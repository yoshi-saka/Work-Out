<?php
// 共通変数・関数ファイル読み込み
require('function.php');
debug('---------------------------------------');
debug('　ログインページ　');
debug('---------------------------------------');
debugLogStart();

// ログイン認証
require('auth.php');

// ログイン画面処理
// post送信されていた場合
if(!empty($_POST)){
  debug('POST送信があります。');

  // 変数ユーザー情報を代入
  $email = $_POST['email'];
  $pass = $_POST['pass'];
  $pass_save = (!empty($_POST['pass_save'])) ? true : false;

  // emailの形式チェック
  validEmail($email, 'email');
  // 最大文字数
  validMax($email, 'email');

  // パスワード半角数字チェック
  validHalf($pass, 'pass');
  // 最大文字数
  validMax($pass, 'pass');
  // 最小文字数
  validMin($pass, 'pass');

  // 未入力チェック
  validRequired($email, 'email');
  validRequired($pass, 'pass');
  
  if(empty($err_msg)){
    debug('バリデーションOKです。');

    // 例外処理
    try{
      $dbh = dbConnect();
      $sql = 'SELECT password,id FROM users WHERE email = :email AND delete_flg = 0';
      $data = array(':email' => $email);
      $stmt = queryPost($dbh, $sql, $data);
      $result = $stmt->fetch(PDO::FETCH_ASSOC);
      debug('クエリ結果の中身：'.print_r($result, true));

      // パスワード照合
      if(!empty($result) && password_verify($pass, array_shift($result))){
        debug('パスワードがマッチしました！');


        // ログイン有効期限を１時間をする
        $sesLimit = 60 * 60;
        $_SESSION['login_date'] = time();
        
        // ログイン保持にチェックがある場合
        if($pass_save){
          debug('ログイン保持にチェックがあります。');
          $_SESSION['login_limit'] = $sesLimit * 24 * 30;
        }else{
          debug('ログイン保持にチェックはありません。');
          $_SESSION['login_limit'] = $sesLimit;
        }
        
        // ユーザーIDを格納
        $_SESSION['user_id'] = $result['id'];

        debug('セッション変数の中身：'.print_r($_SESSION, true));
        debug('マイページへ遷移します。');
        header("Location:mypage.php");
      }else{
        debug('パスワードがアンマッチです。');
        $err_msg['common'] = 'メールアドレスまたはパスワードが違います。';
      }
    }catch(Exception $e){
      error_log('エラー発生：'.$e->getMessage());
      $err_msg['common'] = 'エラーが発生しました。';
    }
  }
}
debug('画面表示処理終了---------------------------');
?>


<?php 
$siteTitle = "ログインページ";
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
<h2 class="title">ログイン</h2>
<div class="area-msg">
<?php echo getErrMsg('common'); ?>
</div>

<label class="<?php echo getErr('email'); ?>">
<input type="text" name="email" placeholder="メールアドレス" value="<?php echo getFormData('email'); ?>">
</label>
<div class="area-msg">
<?php echo getErrMsg('email'); ?>
</div>

<label class="<?php echo getErr('pass'); ?>">
<input type="password" name="pass" placeholder="パスワード" value="<?php echo getFormData('pass'); ?>">
</label>
<div class="area-msg">
<?php echo getErrMsg('pass'); ?>
</div>

<label for="">
<input type="checkbox" name="pass_save">次回ログインを省略する
</label>

<div class="btn-container">
<input type="submit" value="ログイン" class="btn">
</div>
<p>
パスワードを忘れた方は<a href="passReissue.php" class="underline">コチラ</a>
</p>
</form>

</div>
</section>
</div>

<!-- フッター -->
<?php require('footer.php'); ?>