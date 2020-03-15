<?php
//共通変数・関数ファイルを読込み
require('function.php');

debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debug(' 商品詳細ページ ');
debug('「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「「');
debugLogStart();

//================================
// 画面処理
//================================

// 画面表示用データ取得
//================================
// 商品IDのGETパラメータを取得
$r_id = (!empty($_GET['p_id'])) ? $_GET['p_id'] : '';
// DBから商品データを取得
$viewData = getRecordOne($r_id);
debug('取得したDBデータ：' . print_r($viewData, true));
// パラメータに不正な値が入っているかチェック
if (empty($viewData)) {
  error_log('エラー発生:指定ページに不正な値が入りました');
  header("Location:index.php"); //トップページへ
}
debug('取得したDBデータ：' . print_r($viewData, true));

// post送信されていた場合
if (!empty($_POST['submit'])) {
  debug('POST送信があります。');

  //ログイン認証
  require('auth.php');

  //例外処理
  try {
    // DBへ接続
    $dbh = dbConnect();
    // SQL文作成
    $sql = 'INSERT INTO board (receive_user,send_user,record_id, create_date) VALUES (:r_uid, :s_uid, :r_id, :date)';
    $data = array(':r_uid' => $viewData['user_id'], ':s_uid' => $_SESSION['user_id'], ':r_id' => $r_id, ':date' => date('Y-m-d H:i:s'));
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);


    // クエリ成功の場合
    if ($stmt) {
      $_SESSION['msg_success'] = '相手と連絡を取りましょう！';
      debug('連絡掲示板へ遷移します。');
      header("Location:msg.php?m_id=" . $dbh->lastInsertId()); //連絡掲示板へ
    }
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
    $err_msg['common'] = 'エラーが発生しました';
  }
}
debug('画面表示処理終了 <<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<<');
?>
<?php
$siteTitle = '商品詳細';
require('head.php');
?>

<body class="page-recordDetail page-1colum">
  <style>
    .badge {
      padding: 5px 10px;
      color: white;
      background: #7acee6;
      margin-right: 10px;
      font-size: 20px;
      vertical-align: middle;
      position: relative;
      top: -4px;
    }

    .title {
      font-size: 28px;
      padding: 10px 0;
    }

    .record-img-container {
      overflow: hidden;
    }

    .record-img-container img {
      width: 100%;
    }

    .record-img-container .img-main {
      width: 750px;
      float: left;
      padding-right: 15px;
      box-sizing: border-box;
    }

    .record-img-container .img-sub {
      width: 230px;
      float: left;
      background: #f6f5f4;
      padding: 15px;
      box-sizing: border-box;
    }

    .record-img-container .img-sub:hover {
      cursor: pointer;
    }

    .record-img-container .img-sub img {
      margin-bottom: 15px;
    }

    .record-img-container .img-sub img:last-child {
      margin-bottom: 0;
    }

    .record-detail {
      background: #f6f5f4;
      padding: 15px;
      margin-top: 15px;
      min-height: 150px;
    }

    .record-buy {
      overflow: hidden;
      margin-top: 15px;
      margin-bottom: 50px;
      height: 50px;
      line-height: 50px;
    }

    .record-buy .item-left {
      float: left;
    }

    .record-buy .item-right {
      float: right;
    }

    .record-buy .price {
      font-size: 32px;
      margin-right: 30px;
    }

    .record-buy .btn {
      border: none;
      font-size: 18px;
      padding: 10px 30px;
    }

    .record-buy .btn:hover {
      cursor: pointer;
    }

    /*お気に入りアイコン*/
    .icn-like {
      float: right;
      color: #ddd;
    }

    .icn-like:hover {
      cursor: pointer;
    }

    .icn-like.active {
      float: right;
      color: #fe8a8b;
    }

  </style>

  <!-- ヘッダー -->
  <?php
  require('header.php');
  ?>

  <!-- メインコンテンツ -->
  <div class="content">

    <!-- メイン -->
    <section class="section">

      <div class="title"><?php echo getUser('username'); ?>
        <span class="badge">場所</span><?php echo sanitize($viewData['category']); ?>
        <span class="badge">部位</span><?php echo sanitize($viewData['name']); ?>
        <span class="badge">日付</span><?php echo sanitize($viewData['tr_day']); ?>
        <span class="badge">時間</span><?php echo sanitize($viewData['tr_time']); ?>
      </div>
      <div class="record-img-container">
        <div class="img-main">
          <img src="<?php echo showImg(sanitize($viewData['pic1'])); ?>" alt="メイン画像：<?php echo sanitize($viewData['name']); ?>" id="js-switch-img-main">
        </div>
        <div class="img-sub">
          <img src="<?php echo showImg(sanitize($viewData['pic1'])); ?>" alt="画像1：<?php echo sanitize($viewData['name']); ?>" class="js-switch-img-sub">
          <img src="<?php echo showImg(sanitize($viewData['pic2'])); ?>" alt="画像2：<?php echo sanitize($viewData['name']); ?>" class="js-switch-img-sub">
          <img src="<?php echo showImg(sanitize($viewData['pic3'])); ?>" alt="画像3：<?php echo sanitize($viewData['name']); ?>" class="js-switch-img-sub">
        </div>
      </div>
      <div class="record-detail">
        <p><?php echo sanitize($viewData['comment']); ?></p>
      </div>
      <div class="record-buy">
        <div class="item-left">
          <a href="index.php<?php echo appendGetParam(array('r_id')); ?>">&lt; 一覧に戻る</a>
        </div>
        <form action="" method="post">
          <!-- formタグを追加し、ボタンをinputに変更し、style追加 -->
          <div class="item-right">
            <input type="submit" value="メッセージを送る" name="submit" class="btn" style="margin-top:0; width:100%;">
          </div>
        </form>
        <div class="item-right">


        </div>
      </div>

    </section>

  </div>

  <!-- footer -->
  <?php
  require('footer.php');
  ?>