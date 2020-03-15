<?php
//共通変数・関数ファイルを読込み
require('function.php');
debug('------------------------------------------------------');
debug('  マイページ ');
debug('------------------------------------------------------');
debugLogStart();

// 画面処理
// ログイン認証
require('auth.php');

// 画面表示データ取得
$u_id = $_SESSION['user_id'];
// DBから商品データを取得
$recordData = getMyrecords($u_id);
// DBから連絡掲示板データを取得
$boardData = getMyMsgAndBoard($u_id);

debug('取得したデータ：' . print_r($recordData, true));
debug('取得した掲示板データ：' . print_r($boardData, true));

debug('画面表示処理終了 ----------------------------------------');
?>


<?php
$siteTitle = 'マイページ';
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
</style>

<body>
  <!-- メニュー -->
  <?php require('header.php'); ?>

  <p id="js-show-msg">
    <?php echo getSessionFlash('msg_success'); ?>
  </p>


  <!-- メインコンテンツ -->
  <div class="content">
    <h1 class="title">My page</h1>
    <div class="colum">
      <!-- メイン -->
      <section class="section page-2colum">
        <section class="panel-list">
          <div class="container">
            <h2 class="container-title">一覧</h2>
            <?php if (!empty($recordData)) :
              foreach ($recordData as $key => $val) :
            ?>
                <a href="registrecord.php<?php echo (!empty(appendGetParam())) ? appendGetParam() . '&p_id=' . $val['id'] : '?p_id=' . $val['id']; ?>" class="panel">
                  <div class="panel-head">
                    <img src="<?php echo showImg(sanitize($val['pic1'])); ?>" alt="<?php echo sanitize($val['name']); ?>" style="display:block;">
                  </div>
                  <div class="panel-body">
                    <p class="panel-title"><?php echo sanitize($val['name']); ?></p>
                  </div>
                </a>
            <?php
              endforeach;
            endif;
            ?>
          </div>
        </section>

        <div class="list-table">
          <h2 class="title">連絡掲示板</h2>
          <table class="table">
            <thead>
              <tr>
                <th>最新送信日時</th>
                <th>相手</th>
                <th>メッセージ</th>
              </tr>
            </thead>
            <tbody>
              <?php if (!empty($boardData)) {
                foreach ($boardData as $key => $val) {
                  if (!empty($val['msg'])) {
                    $msg = array_shift($val['msg']); ?>
                    <tr>
                      <td><?php echo sanitize(date('Y.m.d H:i:s', strtotime($msg['send_date']))); ?></td>
                      <td><?php echo sanitize($msg['username']);  ?></td>
                      <td><a href="msg.php?m_id=<?php echo sanitize($val['id']); ?>"><?php echo mb_substr(sanitize($msg['msg']), 0, 40); ?>...</a></td>
                    </tr>
                  <?php } else { ?>
                    <tr>
                      <td>--</td>
                      <td>〇〇〇〇</td>
                      <td><a href="msg.php?m_id=<?php echo sanitize($val['id']); ?>">まだメッセージはありません</a></td>
                    </tr>
              <?php
                  }
                }
              }
              ?>
            </tbody>
          </table>
            </div>


        
      </section>



      <!-- サイドバー -->
      <?php require('sidebar.php'); ?>

    </div>
  </div>

  <!-- フッター -->
  <?php require('footer.php'); ?>