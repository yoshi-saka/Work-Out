<?php
// 共通・関数ファイル
require('function.php');
debug('----------------------------------------------------------');
debug('  記録ページ  ');
debug('----------------------------------------------------------');
debugLogStart();

// ログイン認証
require('auth.php');

// 画面処理
// GETデータを格納
$p_id = (!empty($_GET['p_id'])) ? $_GET['p_id'] : '';
// DBから商品データを取得
$dbFormData = (!empty($p_id)) ? getrecord($_SESSION['user_id'], $p_id) : '';
// 新規登録画面か編集画面か判別
$edit_flg = (empty($dbFormData)) ? false : true;
// DBからカテゴリーデータを取得
$dbCategoryData = getCategory();
debug('商品ID：' . $p_id);
debug('フォーム用DBデータ：' . print_r($dbFormData, true));
debug('カテゴリーデータ：' . print_r($dbCategoryData, true));

// パラメータ改ざんチェック
if (!empty($p_id) && empty($dbFormData)) {
  debug('GETパラメータの商品IDが違います。マイページへ遷移します。');
  header("Location:mypage.php");
}

// POST送信時
if (!empty($_POST)) {
  debug('POST送信があります。');
  debug('POST情報：' . print_r($_POST, true));
  debug('FILE情報：' . print_r($_FILES, true));

  // 変数にユーザー情報を代入
  $date = $_POST['date'];
  $time = $_POST['time'];
  $comment = $_POST['comment'];

  $id = $_POST['id'];
  $name = $_POST['name'];
  $category = $_POST['category_id'];
  $comment = $_POST['comment'];
  // 画像をアップロードし、パスを格納
  $pic1 = (!empty($_FILES['pic1']['name'])) ? uploadImg($_FILES['pic1'], 'pic1') : '';
  // すでにDBに登録されている場合
  $pic1 = (empty($pic1) && !empty($dbFormData['pic1'])) ? $dbFormData['pic1'] : $pic1;
  $pic2 = (!empty($_FILES['pic2']['name'])) ? uploadImg($_FILES['pic2'], 'pic2') : '';
  $pic2 = (empty($pic2) && !empty($dbFormData['pic2'])) ? $dbFormData['pic2'] : $pic2;
  $pic3 = (!empty($_FILES['pic3']['name'])) ? uploadImg($_FILES['pic3'], 'pic3') : '';
  $pic3 = (empty($pic3) && !empty($dbFormData['pic3'])) ? $dbFormData['pic3'] : $pic3;

  // 更新時はDBの情報と入力情報が異なる場合にパリデーションを行う
  if (empty($dbFormData)) {
    // 未入力チェック
    validRequired($name, 'name');
    // 最大文字数チェック
    validMax($name, 'name');
    // セレクトボックスチェック
    validSelect($category, 'category_id');
    // 最大文字数チェック500
    validMaxcm($comment, 'comment');
  } else {
    if ($dbFormData['name'] !== $name) {
      // 未入力チェック
      validRequired($name, 'name');
      // 最大文字数チェック
      validMax($name, 'name');
    }
    if ($dbFormData['category_id'] !== $category) {
      // セレクトボックスチェック
      validSelect($category, 'category_id');
    }
  }

  // コメント最大文字数チェック 500文字
  validMaxcm($comment, 'comment');

  if (empty($err_msg)) {
    debug('バリデーションOK.');

    // 例外処理
    try {
      $dbh = dbConnect();
      // 編集時はUPDATE文、新規登録時はINSERT文を生成
      if ($edit_flg) {
        debug('DB更新です。');
        $sql = 'UPDATE record SET tr_day = :date, tr_time = :time, name = :name, category_id = :category, comment = :comment, pic1 = :pic1, pic2 = :pic2, pic3 = :pic3 WHERE user_id = :u_id AND id = :p_id';
        $data = array(':date' => $date, ':time' => $time,':name' => $name, ':category' => $category, ':comment' => $comment, ':pic1' => $pic1, ':pic2' => $pic2, ':pic3' => $pic3, 'u_id' => $_SESSION['user_id'], ':p_id' => $p_id);
      } else {
        debug('DB新規登録です。');
        $sql = 'INSERT into record (tr_day, tr_time, name, category_id, comment, pic1, pic2, pic3, user_id, create_date) value(:date, :time, :name, :category, :comment, :pic1, :pic2, :pic3, :u_id, :date)';
        $data = array(':date' => $date, ':time' => $time, ':name' => $name, ':category' => $category, ':comment' => $comment, ':pic1' => $pic1, ':pic2' => $pic2, ':pic3' => $pic3, 'u_id' => $_SESSION['user_id'], 'date' => date('Y-m-d H:i:s'));
      }
      debug('SQL:' . $sql);
      debug('流し込みデータ：' . print_r($data, true));
      $stmt = queryPost($dbh, $sql, $data);

      if ($stmt) {
        $_SESSION['msg_success'] = '登録しました';
        debug('マイページへ遷移します。');
        header("Location:mypage.php");
      }
    } catch (Exception $e) {
      error_log('エラー発生：' . $e->getMessage());
      $err_msg['common'] = 'エラーが発生しました。';
    }

if (null !== $_POST["delete"]) { //削除ボタンが押され方どうかを確認
  $sql = "DELETE FROM record WHERE id=:id";
  $data = array(":id" => $_POST["id"]);
  $stmt = queryPost($dbh, $sql, $data);
}


  }
}



debug('画面表示処理終了 ----------------------------------------');

?>


<?php
$siteTitle = (!$edit_flg) ? '記録' : '編集';
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
 .form {
    width: 100%;
    box-sizing: border-box;
  }

</style>

<body>
  <!-- メニュー -->
  <?php require('header.php'); ?>

  <!-- メインコンテンツ -->
  <div class="content">
    <h1 class="title"><?php echo (!$edit_flg) ? '記録' : '編集'; ?></h1>
    <div class="colum">
      <!-- メイン -->
      <section class="section page-2colum">
        <div class="container">
          <form method="post" class="form" enctype="multipart/form-data">
            <div class="area-msg">
              <?php echo getErrMsg('common'); ?>
            </div>

            <label class="<?php echo getErr('name'); ?>">
            部位<span class="required">必須</span>
              <input type="text" name="name" value="<?php echo getFormData('name'); ?>" autocomplete="off">
            </label>
            <div class="area-msg">
              <?php echo getErrMsg('name'); ?>
            </div>

            <label class="<?php echo getErr('category_id'); ?>">
              カテゴリー<span class="required">必須</span>
              <select name="category_id">
                <option value="0" <?php if (getFormData('category_id') == 0) {
                    echo 'selected';
                  } ?>>
                  選択してください
                </option>
                <?php foreach ($dbCategoryData as $key => $val) { ?>
                  <option value="<?php echo $val['id']; ?>"
                    <?php if (getFormData('category_id') == $val['id']) {
                      echo 'selected';
                    } ?>>
                    <?php echo $val['name']; ?>
                  </option>
                <?php } ?>
              </select>
            </label>
            <div class="area-msg">
              <?php echo getErrMsg('category_id'); ?>
            </div>

            <label class="<?php if (!empty($err_msg['date'])) echo 'err'; ?>">
              日時
              <input type="date" name="date" value="<?php if (!empty($_POST['date'])) echo $_POST['date']; ?>">
            </label>
            <div class="area-msg"><?php if (!empty($err_msg['date'])) echo $err_msg['date']; ?></div>

            <label class="<?php if (!empty($err_msg['time'])) echo 'err'; ?>">
              時間
              <input type="time" name="time" value="<?php if (!empty($_POST['time'])) echo $_POST['time']; ?>">
            </label>
            <div class="area-msg"><?php if (!empty($err_msg['time'])) echo $err_msg['time']; ?></div>

              内容
              <textarea name="comment" id="js-count" style="height:150px;"><?php echo getFormData('comment'); ?></textarea>
            </label>

            <div class="area-msg">
              <?php echo getErrMsg('comment'); ?>
              <p class="counter-text"><span id="js-count-view">0</span>/500文字</p>

            </div>

            <div class="img-content" style="overflow: hidden;">

              <div class="img-container">
                画像１
                <label class="area-drop <?php echo getErr('pic1'); ?>">
                  <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                  <input type="file" name="pic1" class="input-file">
                  <img src="<?php echo getFormData('pic1'); ?>" alt="" class="prev-img">
                </label>
                <div class="area-msg">
                  <?php echo getErrMsg('pic1'); ?>
                </div>
              </div>

              <div class="img-container">
                画像2
                <label class="area-drop <?php echo getErr('pic2'); ?>">
                  <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                  <input type="file" name="pic2" class="input-file">
                  <img src="<?php echo getFormData('pic2'); ?>" alt="" class="prev-img">
                </label>
                <div class="area-msg">
                  <?php echo getErrMsg('pic2'); ?>
                </div>
              </div>

              <div class="img-container">
                画像3
                <label class="area-drop <?php echo getErr('pic3'); ?>">
                  <input type="hidden" name="MAX_FILE_SIZE" value="3145728">
                  <input type="file" name="pic3" class="input-file">
                  <img src="<?php echo getFormData('pic3'); ?>" alt="" class="prev-img">
                </label>
                <div class="area-msg">
                  <?php echo getErrMsg('pic3'); ?>
                </div>
              </div>

              <div class="btn-container">
                <input type="submit" value="<?php echo (!$edit_flg) ? '登録する' : '更新する'; ?>" class="btn">

                
                
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