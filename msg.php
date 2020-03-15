<?php
// 共通・関数ファイル
require('function.php');
debug('--------------------------------------------------');
debug('  連絡掲示板ページ  ');
debug('--------------------------------------------------');
debugLogStart();

// 画面処理
// カレントページ
$currentPageNum = (!empty($_GET['p'])) ? $_GET['p'] : 1;
// カテゴリー
$category = (!empty($_GET['c_id'])) ? $_GET['c_id'] : '';
// ソート
$sort = (!empty($_GET['sort'])) ? $_GET['sort'] : '';
if(!is_int((int)$currentPageNum)){
  error_log('エラーが発生：指定ページに不正な値が入りました。');
  header('Location:index.php');
}

// 表示件数
$listSpan = 20;
$currentMinNum = (($currentPageNum - 1)*$listSpan);
$dbRecordData = getRecordList($currentMinNum, $category, $sort);
$dbCategoryData = getCategory();
// 画面処理
$partnerUserId = '';
$partnerUserInfo = '';
$myUserInfo = '';
$recordInfo = '';

// 画面表示データ
$m_id = (!empty($_GET['m_id'])) ? $_GET['m_id'] : '';
$viewData = getMsgAndBoard($m_id);
debug('取得したDBデータ：' . print_r($viewData, true));
if (empty($viewData)) {
  error_log('エラー発生：指定ページに不正な値が入りました');
  header("Location:mypage.php");
}

// 情報を取得
$recordInfo = getRecordOne($viewData[0]['record_id']);
debug('取得したdbデータ：' . print_r($recordInfo, true));

// 情報があるかチェック
if (empty($recordInfo)) {
  error_log('エラー発生：情報が取得できませんでした');
  header("Location:mypage.php");
}

$dealUserIds[] = $viewData[0]['receive_user'];
$dealUserIds[] = $viewData[0]['send_user'];
if (($key = array_search($_SESSION['user_id'], $dealUserIds)) !== false) {
  unset($dealUserIds[$key]);
}
$partnerUserId = array_shift($dealUserIds);
debug('取得した相手のユーザーID：' . $partnerUserId);
if (isset($partnerUserId)) {
  $partnerUserInfo = getUser($partnerUserId);
}
if (empty($partnerUserInfo)) {
  error_log('エラー発生：相手のユーザー情報が取得できませんでした');
  header("Location:mypage.php");
}

$myUserInfo = getUser($_SESSION['user_id']);
debug('取得したユーザーデータ：' . print_r($partnerUserInfo, true));
if (empty($myUserInfo)) {
  error_log('エラー発生：自分のユーザー情報が取得できませんでした');
  header("Location:mypage.php");
}

// POST送信されていた場合
if (!empty($_POST)) {
  debug('POST送信があります。');

  // ログイン認証
  require('auth.php');

  $msg = (isset($_POST['msg'])) ? $_POST['msg'] : '';
  validMaxcm($msg, 'msg');
  validRequired($msg, 'msg');

  if (empty($err_msg)) {
    debug('バリデーションOK.');
    // 例外処理
    try {
      $dbh = dbConnect();
      $sql = 'INSERT INTO message (board_id, send_date, to_user, from_user, msg, create_date) VALUES (:b_id, :send_date, :to_user, :from_user, :msg, :date)';
      $data = array(':b_id' => $m_id, ':send_date' => date('Y-m-d H:i:s'), ':to_user' => $partnerUserId, ':from_user' => $_SESSION['user_id'], ':msg' => $msg, ':date' => date('Y-m-d H:i:s'));
      $stmt = queryPost($dbh, $sql, $data);

      if ($stmt) {
        $_POST = array();
        debug('連絡掲示板へ遷移します。');
        header("Location:" . $_SERVER['PHP_SELF'] . '?m_id=' . $m_id);
      }
    } catch (Exception $e) {
      error_log('エラー発生：' . $e->getMessage());
      $err_msg['common'] = 'エラーが発生しました。';
    }
  }
}
debug('画面処理終了 -----------------------------------------------');
?>

<?php
$siteTitle = '連絡掲示板';
require('head.php');
?>
<style>
  /* 連絡掲示板 */
  .msg-info {
    background: #f6f5f4;
    padding: 15px;
    overflow: hidden;
    margin-bottom: 15px;
    display: flex;
    justify-content: space-between;
  }

  .msg-info .avatar {
    width: 80px;
    height: 80px;
    border-radius: 40px;
  }

  .msg-info .avatar-img {
    text-align: center;
    width: 100px;
    float: left;
  }

  .msg-info .avatar-info {
    float: left;
    padding-left: 15px;
    width: 500px;
  }

  .msg-info .record-info {
    float: left;
    padding-left: 15px;
    width: 315px;
  }

  /* .msg-info .record-info .left,
  .msg-info .record-info .right {
    float: left;
  } */

  .msg-info .record-info .right {
    padding-left: 15px;
  }

  .msg-info .record-info .price {
    display: inline-block;
  }

  .area-board {
    height: 500px;
    overflow-y: scroll;
    background: #f6f5f4;
    padding: 15px;
  }

  .area-send-msg {
    background: #f6f5f4;
    padding: 15px;
    overflow: hidden;
  }

  .area-send-msg textarea {
    width: 100%;
    background: white;
    height: 100px;
    padding: 15px;
  }

  .area-send-msg .btn-send {
    width: 150px;
    float: right;
    margin-top: 0;
  }

  .area-board .msg-cnt {
    width: 80%;
    overflow: hidden;
    margin-bottom: 30px;
  }

  .area-board .msg-cnt .avatar {
    width: 5.2%;
    overflow: hidden;
    float: left;
  }

  .area-board .msg-cnt .avatar img {
    width: 40px;
    height: 40px;
    border-radius: 20px;
    float: left;
  }

  .area-board .msg-cnt .msg-inrTxt {
    width: 85%;
    float: left;
    border-radius: 5px;
    padding: 10px;
    margin: 0 0 0 25px;
    position: relative;
  }

  .area-board .msg-cnt.msg-left .msg-inrTxt {
    background: #f6e2df;
  }

  .area-board .msg-cnt.msg-left .msg-inrTxt>.triangle {
    position: absolute;
    left: -20px;
    width: 0;
    height: 0;
    border-top: 10px solid transparent;
    border-right: 15px solid #f6e2df;
    border-left: 10px solid transparent;
    border-bottom: 10px solid transparent;
  }

  .area-board .msg-cnt.msg-right {
    float: right;
  }

  .area-board .msg-cnt.msg-right .msg-inrTxt {
    background: #d2eaf0;
    margin: 0 25px 0 0;
  }

  .area-board .msg-cnt.msg-right .msg-inrTxt>.triangle {
    position: absolute;
    right: -20px;
    width: 0;
    height: 0;
    border-top: 10px solid transparent;
    border-left: 15px solid #d2eaf0;
    border-right: 10px solid transparent;
    border-bottom: 10px solid transparent;
  }

  .area-board .msg-cnt.msg-right .msg-inrTxt {
    float: right;
  }

  .area-board .msg-cnt.msg-right .avatar {
    float: right;
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
      <div class="msg-info">
        <div class="avatar-img">
        <?php echo sanitize($partnerUserInfo['username']); ?>
          <img src="<?php echo showImg(sanitize($partnerUserInfo['pic'])); ?>" class="avatar">
        </div>
        <!-- <div class="avatar-info">
          
        </div> -->
        <div class="record-info">
          <div class="left">
          筋トレした部位：<?php echo sanitize($recordInfo['name']); ?><br>
        <a href="recordDetail.php"><img src="<?php echo showImg(sanitize($recordInfo['pic1'])); ?>" alt="" style="height: 80px; width:80;"></a>
          </div>

        </div>
      </div>
      <div class="area-board" id="js-scroll-bottom">
        <?php
        if (!empty($viewData)) {
          foreach ($viewData as $key => $val) {
            if (!empty($val['from_user']) && $val['from_user'] == $partnerUserId) {
        ?>
              <div class="msg-cnt msg-left">
                <div class="avatar">
                  <img src="<?php echo sanitize(showImg($partnerUserInfo['pic'])); ?>" class="avatar">
                </div>
                <p class="msg-inrTxt">
                  <span class="triangle"></span>
                  <?php echo sanitize($val['msg']); ?>
                </p>
                <div style="font-size: .5em;"><?php echo sanitize($val['send_date']); ?></div>
              </div>
            <?php } else { ?>
              <div class="msg-cnt msg-right">
                <div class="avatar">
                  <img src="<?php echo sanitize(showImg($myUserInfo['pic'])); ?>" class="avatar">
                </div>
                <p class="msg-inrTxt">
                  <span class="triangle"></span>
                  <?php echo sanitize($val['msg']); ?>
                </p>
                <div style="font-size: .5em; text-align:right;"><?php echo sanitize($val['send_date']); ?></div>
              </div>
          <?php
            }
          }
        } else {
          ?>
          <p style="text-align: center; line-height: 20px;">メッセージ投稿はまだありません</p>
        <?php } ?>
      </div>
      <div class="area-send-msg">
        <form action="" method="post">
          <textarea name="msg" id="" cols="30" rows="10"></textarea>
          <input type="submit" value="送信" class="btn">
        </form>
      </div>
    </section>

    <script src="https://code.jquery.com/jquery-3.3.1.min.js" integrity="sha256-FgpCb/KJQlLNfOu91ta32o/NMZxltwRo8QtmkMRdAu8=" crossorigin="anonymous"></script>
    <script>
      $(function() {
        $('#js-scroll-bottom').animate({
          scrollTop: $('#js-scroll-bottom')[0].scrollHeight
        }, 'slow');
      });
    </script>
  </div>

  <?php require('footer.php'); ?>