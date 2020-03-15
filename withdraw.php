<?php
// 共通変数・関数ファイルを読み込み
require('function.php');

debug('-------------------------------------');
debug('  退会ページ  ');
debug('--------------------------------------');
debugLogStart();

// ログイン認証
require('auth.php');

// 画面処理
// POST送信されていた場合
if(!empty($_POST)){
  debug('POST送信があります。');
  // 例外処理
  try{
    $dbh = dbConnect();
    $sql1 = 'UPDATE users SET delete_flg = 1 WHERE id = :us_id';
    $sql2 = 'UPDATE record SET delete_flg = 1 WHERE user_id = :us_id';
    $sql3 = 'UPDATE like SET delete_flg = 1 WHERE user_id = :us_id';

    $data = array(':us_id' => $_SESSION['user_id']);

    $stmt1 = queryPost($dbh, $sql1, $data);
    $stmt2 = queryPost($dbh, $sql2, $data);
    $stmt3 = queryPost($dbh, $sql3, $data);

    if($stmt1){
      //セッション削除
       session_destroy();
       debug('セッション変数の中身：'.print_r($_SESSION,true));
       debug('トップページへ遷移します。');
       header("Location:index.php");
     }else{
       debug('クエリが失敗しました。');
       $err_msg['common'] = 'エラーが発生しました';
     }
 
   } catch (Exception $e) {
     error_log('エラー発生:' . $e->getMessage());
     $err_msg['common'] = 'エラーが発生しました';
   }
 }
 debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>

<?php
$siteTitle = '退会';
require('head.php');
?>
<style>
.form{
  width: 400px;
}
input[type="submit"]{
  float: none;
  margin: 0 auto 30px;
}
.form .title {
    margin-bottom: 40px;
    text-align: center;
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
<form action="" method="post" class="form">
<h2 class="title">退会</h2>
<div class="area-msg">
<?php if(!empty($err_msg['common'])) echo $err_msg['common']; ?>
</div>
<div class="btn-container">
<input type="submit" value="退会" name="submit" class="btn btn-center">
</div>
<a href="mypage.php">&lt;マイページに戻る</a>
</form>
</div>

</section>
</div>

<?php require('footer.php'); ?>