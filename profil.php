<?php
// 共通変数・関数ファイル
require('function.php');
debug('----------------------------------------------------');
debug('  プロフィール編集ページ  ');
debug('----------------------------------------------------');
debugLogStart();

// ログイン認証
require('auth.php');

// 画面処理
// DBからユーザー情報を取得
$dbFormData = getUser($_SESSION['user_id']);
debug('取得したユーザー情報：'.print_r($dbFormData, true));

// POST送信されていた場合
if(!empty($_POST)){
debug('POST送信があります。');
debug('POST情報：'.print_r($_POST, true));
debug('FILE情報：'.print_r($_FILES, true));


// 変数にユーザー情報を代入
$username = $_POST['username'];
$email = $_POST['email'];
$pic = (!empty($_FILES['pic']['name'])) ? uploadImg($_FILES['pic'],'pic') : '';
$pic = (empty($pic) && !empty($dbFormData['pic'])) ? $dbFormData['pic'] : $pic;

if($dbFormData['username'] !== $username){
  validMax($username, 'username');
}

if($dbFormData['email'] !== $email){
  validMax($email, 'email');
 } 
  if(empty($err_msg['email'])){
    validEmail($email, 'email');
    validRequired($email, 'email');
  }


if(empty($err_msg)){
  debug('バリデーションOKです。');

  try{
    $dbh = dbConnect();
    $sql = 'UPDATE users SET username = :u_name, email = :email, pic = :pic WHERE id = :u_id';
    $data = array(':u_name' => $username, ':email' => $email, ':pic' => $pic, ':u_id' => $dbFormData['id']);
    $stmt = queryPost($dbh, $sql, $data);

    if($stmt){
      debug('マイページへ遷移します。');
      header("Location:mypage.php");
    }
   }catch(Exception $e){
     error_log('エラー発生：'.$e->getMessage());
     $err_msg['common'] = 'エラーが発生しました。';
   }
 }
}
debug('画面処理終了 --------------------------------------------');

?>


<?php
$siteTitle = 'プロフィール';
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

</style>

<body>
  <!-- メニュー -->
  <?php require('header.php'); ?>

  <!-- メインコンテンツ -->
  <div class="content">
    <h1 class="title">プロフィール編集</h1>
    <div class="colum">
      <!-- メイン -->
      <section class="section page-2colum">
        <div class="container">
          <form action="" method="post" class="form" enctype="multipart/form-data">
            <div class="area-msg">
              <?php echo getErrMsg('common'); ?>
            </div>
            名前
            <label class="<?php echo getErr('username'); ?>">
              <input type="text" name="username" placeholder="name" value="<?php echo getFormData('username'); ?>">
            </label>
            <div class="area-msg">
            </div>
            メールアドレス
            <label class="<?php echo getErr('email'); ?>">
              <input type="text" name="email" placeholder="Email" value="<?php echo getFormData('email'); ?>">
            </label>
            <div class="area-msg">
              <?php echo getErrMsg('email'); ?>
            </div>

            プロフィール画像
            <label class="area-drop <?php echo getErr('pic'); ?>" style="height:400px;">
              <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
              <input type="file" name="pic" class="input-file">
              <img src="<?php echo getFormData('pic'); ?>" class="prev-img" height="100%">
            </label>
            <div class="area-msg">
              <?php echo getErrMsg('pic'); ?>
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