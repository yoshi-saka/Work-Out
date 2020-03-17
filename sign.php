<?php
// 共通変数・関数ファイルを読み込む
require('function.php');

// post送信されていた場合
if (!empty($_POST)) {

  // 変数にユーザー情報を代入
  $email = $_POST['email'];
  $pass = $_POST['pass'];
  $pass_re = $_POST['pass_re'];

  // 未入力チェック
  validRequired($email, 'email');
  validRequired($pass, 'pass');
  validRequired($pass_re, 'pass_re');

  if (empty($err_msg)) {
    // emailの形式チェック
    validEmail($email, 'email');
    // emailの最大文字数チェック
    validMax($email, 'email');

    // パスワードの半角数字チェック
    validHalf($pass, 'pass');
    // パスワードの最大文字数チェック
    validMax($pass, 'pass');
    // パスワードの最小文字数チェック
    validMin($pass, 'pass');

    if (empty($err_msg)) {
      // パスワードとパスワード再入力が合っているかチェック
      validMatch($pass, $pass_re, 'pass_re');


      if (empty($err_msg)) {
        // 例外処理
        try {
          // DBへ接続
          $dbh = dbConnect();
          $sql = 'INSERT INTO users(email,password,login_time,create_date) VALUES(:email, :pass, :login_time, :create_date)';
          $data = array(':email' => $email, ':pass' => password_hash($pass, PASSWORD_DEFAULT), ':login_time' => date('Y-m-d H:i:s'), ':create_date' => date('Y-m-d H:i:s'));
          $stmt = queryPost($dbh, $sql, $data);
          // クエリ成功の場合
          if ($stmt) {
            // ログイン有効時間を１時間とする
            $sesLimit = 60 * 60;
            // 最終ログイン日時を現在日時に
            $_SESSION['login_date'] = time();
            $_SESSION['login_limit'] = $sesLimit;
            // 
            $_SESSION['user_id'] = $dbh->lastInsertId();
            // マイページへ
            header("Location:mypage.php");
          }
        } catch (Exception $e) {
          echo 'DBerror:' . $e->getMessage();
        }
      }
    }
  }
}
?>


<?php
$siteTitle = 'ユーザー登録';
require('head.php');
?>
<style>
  .form {
    width: 400px;
  }
</style>

<body>
  <?php require('header.php'); ?>

  <div class="content">
    <section class="section">
      <div class="container">
        <form method="post" class="form">
          <h2 class="title">ユーザー登録</h2>
          <div class="area-msg">
            <?php echo getErrMsg('common'); ?>
          </div>
          <label class="<?php echo getErr('email'); ?>">
            <input type="text" name="email" placeholder="Email" value="<?php echo getFormData('email'); ?>" autocomplete="off">
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

          <label class="<?php echo getErr('pass_re'); ?>">
            <input type="password" name="pass_re" placeholder="パスワード（再入力）" value="<?php echo getFormData('pass_re'); ?>">
          </label>
          <div class="area-msg">
            <?php echo getErrMsg('pass_re'); ?>
          </div>
          <div class="btn-container">
            <input type="submit" value="送信" class="btn">
          </div>
        </form>
      </div>
    </section>
  </div>

  <?php require('footer.php'); ?>