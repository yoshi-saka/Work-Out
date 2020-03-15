<?php
// 共通変数・関数ファイルを読み込み
require('function.php');

debug('--------------------------------------------------------');
debug('  パスワード変更ページ ');
debug('--------------------------------------------------------');
debugLogStart();

// ログイン認証
require('auth.php');

// 画面処理
$userData = getUser($_SESSION['user_id']);
debug('取得したユーザー情報：'.print_r($userData, true));

// POST送信されていた場合
if(!empty($_POST)){
  debug('POST送信があります。');
  debug('POST情報：'.print_r($_POST, true));

  // 変数にユーザー情報を代入
  $pass_old = $_POST['pass_old'];
  $pass_new = $_POST['pass_new'];
  $pass_new_re = $_POST['pass_new_re'];

  validRequired($pass_old, 'pass_old');
  validRequired($pass_new, 'pass_new');
  validRequired($pass_new_re, 'pass_new_re');

  if(empty($err_msg)){
    debug('未入力チェックOK。');

    // パスワードの半角数字チェック
    validHalf($pass_old, 'pass_old');
    // パスワードの最大文字数チェック
    validMax($pass_old, 'pass_old');
    // パスワードの最小文字数チェック
    validMin($pass_old, 'pass_old');

    // パスワードの半角数字チェック
    validHalf($pass_new, 'pass_new');
    // パスワードの最大文字数チェック
    validMax($pass_new, 'pass_new');
    // パスワードの最小文字数チェック
    validMin($pass_new, 'pass_new');

    // 古いパスワードとDBパスワードを照合
    if(!password_verify($pass_old, $userData['password'])){
      $err_msg['pass_old'] = '古いパスワードが違います。';
    }

    // 新しいパスワードと古いパスワードが同じかチェック
    if($pass_old === $pass_new){
      $err_msg['pass_new'] = '古いパスワードが同じです。';
    }

    validMatch($pass_new, $pass_new_re, 'pass_new_re');

    if(empty($err_msg)){
      debug('バリデーションOK。');

      // 例外処理
      try{
        $dbh = dbConnect();
        $sql = 'UPDATE users SET password = :pass WHERE id = :id';
        $data = array(':id' => $_SESSION['user_id'], ':pass' => password_hash($pass_new, PASSWORD_DEFAULT));
        $stmt = queryPost($dbh, $sql, $data);
        // クエリ成功の場合
        if($stmt){
          $_SESSION['msg_success'] = 'パスワードを変更しました';

          // メールを送信
          $username = ($user['username']) ? $userData['username'] : '名無し';
          $from = 'zerotenhundred@gmail.com';
          $to = $userData['email'];
          $subject = 'パスワード変更通知';
          $comment = <<<EOT
{$username}さん
パスワードが変更されました。

/////////////////////////////////////////
カスタマーセンター
URL aaaaaaaaaaaaaaa
E-mail aaaaaaaaaaaa
/////////////////////////////////////////
EOT;
                sendMail($from, $to, $subject, $comment);
                header("Location:mypage.php");
          
        }
      }catch(Exception $e){
        error_log('エラー発生：'.$e->getMessage());
        $err_msg['common'] = 'エラー発生しました。';
      }
    }
  }
}


?>



<?php
$siteTitle = 'パスワード変更';
require('head.php');
?>
<style>
  .colum {
    display: flex;
    justify-content: space-between;
    margin-bottom: 50px;
  }

  .page-2colum {
    width: 76%;
    min-height: 600px;
    background: #f6f5f4;
    box-sizing: border-box;
    padding: 20px;
  }
  .form{
    width: 400px;
  }

  .area-drop {
    width: 400px;
    height: 400px;
    background: aliceblue;
    margin-bottom: 15px;
    color: #333;
    text-align: center;
    line-height: 200px;
    position: relative;
    box-sizing: border-box;
  }
</style>

<body>
  <!-- メニュー -->
  <?php require('header.php'); ?>

  <!-- メインコンテンツ -->
  <div class="content">
    <h1 class="title">パスワード変更</h1>
    <div class="colum">
      <!-- メイン -->
      <section class="section page-2colum">
        <div class="container">
          <form method="post" class="form">
            <div class="area-msg">
              <?php getErrMsg('common'); ?>
            </div>
            <label class="<?php echo getErr('pass_old'); ?>">
            古いパスワード
            <input type="password" name="pass_old" value="<?php echo getFormData('pass_old'); ?>">
            </label>
            <div class="area-msg">
            <?php echo getErrMsg('pass_old'); ?>
            </div>

            <label class="<?php echo getErr('pass_new'); ?>">
            新しいパスワード
            <input type="password" name="pass_new" value="<?php echo getFormData('pass_new'); ?>">
            </label>
            <div class="area-msg">
            <?php echo getErrMsg('pass_new'); ?>
            </div>

            <label class="<?php echo getErr('pass_new_re'); ?>">
            新しいパスワード（再入力）
            <input type="password" name="pass_new_re" value="<?php echo getFormData('pass_new_re'); ?>">
            </label>
            <div class="area-msg">
            <?php echo getErrMsg('pass_new_re'); ?>
            </div>

            <div class="btn-container">
              <input type="submit" value="送信" class="btn">
            </div>
          </form>
        </div>
      </section>

      <!-- サイドバー -->
      <?php require('sidebar.php'); ?>

    </div>
  </div>

  <!-- フッター -->
  <?php require('footer.php'); ?>