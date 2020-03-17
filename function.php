<?php
// ログを取るか
// ini_set('log_errors', 'off');
// // ログの出力ファイル
// ini_set('error_log', 'php.log');

// デバッグフラグ
$debug_flg = true;
// デバッグフラグ
function debug($str)
{
  global $debug_flg;
  if (!empty($debug_flg)) {
    error_log('デバッグ：' . $str);
  }
}

// セッションファイルの置き場を変更
session_save_path("/var/tmp/");
// セッション有効期限の変更
ini_set('session.gc_maxlifetime', 60 * 60 * 24 * 14);
// クッキー有効期限を延ばす
ini_set('session.cookie_lifetime', 60 * 60 * 24 * 14);
session_start();
// 現在のセッションIDを新しく生成したものと置き換える（なりすましのセキュリティ対策）
session_regenerate_id();

function debugLogStart()
{
  debug('-----------------------画面表示処理開始');
  debug('セッションID：' . session_id());
  debug(' セッション変数の中身：' . print_r($_SESSION, true));
  debug('現在日時タイムスタンプ：' . time());
  if (!empty($_SESSION['login_date']) && !empty($_SESSION['login_limit'])) {
    debug('ログイン期限日時タイムスタンプ：' . ($_SESSION['login_date'] + $_SESSION['login_limit']));
  }
}

// エラーメッセージ格納
$err_msg = array();

// 未入力チェック
function validRequired($str, $key)
{
  if ($str === '') {
    global $err_msg;
    $err_msg[$key] = '入力必須です';
  }
}

// Email形式チェック
function validEmail($str, $key)
{
  if (!preg_match("/^([a-zA-Z0-9])+([a-zA-Z0-9\._-])*@([a-zA-Z0-9_-])+([a-zA-Z0-9\._-]+)+$/", $str)) {
    global $err_msg;
    $err_msg[$key] = 'Emailの形式で入力してください';
  }
}

// パスワード同値チェック
function validMatch($str1, $str2, $key)
{
  if ($str1 !== $str2) {
    global $err_msg;
    $err_msg[$key] = 'パスワード（再入力）が合っていません';
  }
}
// 最小文字数チェック
function validMin($str, $key)
{
  if (mb_strlen($str) < 6) {
    global $err_msg;
    $err_msg[$key] = '6文字以上で入力してください';
  }
}
// 最大文字数チェック
function validMax($str, $key)
{
  if (mb_strlen($str) > 256) {
    global $err_msg;
    $err_msg[$key] = '256文字以内で入力してください';
  }
}
// 最大文字数チェック５００
function validMaxcm($str, $key)
{
  if (mb_strlen($str) > 500) {
    global $err_msg;
    $err_msg[$key] = '500文字以内で入力してください';
  }
}
// 半角チェック
function validHalf($str, $key)
{
  if (!preg_match("/^[a-zA-Z0-9]+$/", $str)) {
    global $err_msg;
    $err_msg[$key] = '半角英数字のみご利用いただきます';
  }
}

// 固定数チェック
function validLength($str, $key, $len = 8)
{
  if (mb_strlen($str) !== $len) {
    global $err_msg;
    $err_msg[$key] = $len . '文字で入力してください。';
  }
}
// セレクトボックスチェック
function validSelect($str, $key)
{
  if (!preg_match("/^[0-9]+$/", $str)) {
    global $err_msg;
    $err_msg[$key] = '正しくありません。';
  }
}

// エラーメッセージ
function getErrMsg($key)
{
  global $err_msg;
  if (!empty($err_msg[$key])) {
    return $err_msg[$key];
  }
}
// エラーフォーム
function getErr($key)
{
  global $err_msg;
  if (!empty($err_msg[$key])) {
    return 'err';
  }
}

// データーベース
function dbConnect()
{
  // DBへの接続準備
  $dsn = 'mysql:dbname=muscleban;host=localhost;charset=utf8';
  $user = 'root';
  $password = 'root';
  $options = array(
    PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING,
    // デフォルトフェッチモードを連想配列形式に設定
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    // バッファードクエリを使う(一度に結果セットをすべて取得し、サーバー負荷を軽減)
    // SELECTで得た結果に対してもrowCountメソッドを使えるようにする
    PDO::MYSQL_ATTR_USE_BUFFERED_QUERY => true,
  );
  // DBへ接続
  $dbh = new PDO($dsn, $user, $password, $options);
  return $dbh;
}

function queryPost($dbh, $sql, $data)
{
  // クエリー作成
  $stmt = $dbh->prepare($sql);
  // プレスホルダーに値にセットし、SQL文を実行
  if (!$stmt->execute($data)) {
    debug('クエリに失敗しました。');
    debug('失敗したSQL：' . print_r($stmt, true));
    $err_msg['common'] = 'エラーが発生しました。しばらく経ってからやり直してください。';
    return 0;
  }
  debug('クエリ成功！');
  return $stmt;
}

// ログイン認証
function isLogin()
{
  // ログインしている場合
  if (!empty($_SESSION['login_date'])) {
    debug('ログイン済みユーザーです。');

    // 現在日時が最終ログイン日時＋有効期限を超えていた場合
    if (($_SESSION['login_date'] + $_SESSION['login_limit']) < time()) {
      debug('ログイン有効期限オーバーです。');

      session_destroy();
      return false;
    } else {
      debug('ログイン有効期限以内です。');
      return true;
    }
  } else {
    debug('未ログインユーザーです。');
    return false;
  }
}

function getUser($u_id)
{
  debug('ユーザー情報を取得します。');
  // 例外処理
  try {
    $dbh = dbConnect();
    $sql = 'SELECT * FROM users WHERE id = :u_id AND delete_flg = 0';
    $data = array(':u_id' => $u_id);
    $stmt = queryPost($dbh, $sql, $data);

    // クエリ結果のデータを１レコード返却
    if ($stmt) {
      return $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生：' . $e->getMessage());
  }
}

function getrecord($u_id, $p_id)
{
  debug('商品情報を取得します。');
  debug('ユーザーID：' . $u_id);
  debug('商品ID：' . $p_id);

  // 例外処理
  try {
    $dbh = dbConnect();
    $sql = 'SELECT * FROM record WHERE user_id = :u_id AND id = :p_id AND delete_flg = 0';
    $data = array('u_id' => $u_id, ':p_id' => $p_id);
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      return $stmt->fetch(PDO::FETCH_ASSOC);
    } else {
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生：' . $e->getMessage());
  }
}

// メール送信
function sendMail($from, $to, $subject, $comment)
{
  if (!empty($to) && !empty($subject) && !empty($comment)) {
    // 文字化けしないように
    // 現在使っている言語を設定
    mb_language("Japanese");
    mb_internal_encoding("UTF-8");

    // メールを送信
    $result = mb_send_mail($to, $subject, $comment, "From: " . $from);

    // 送信結果を判定
    if ($result) {
      debug('メールを送信しました。');
    } else {
      debug('「エラー発生」メールの送信に失敗しました。');
    }
  }
}

// セッションを１回だけ取得する
function getSessionFlash($key)
{
  if (!empty($_SESSION[$key])) {
    $data = $_SESSION[$key];
    $_SESSION[$key] = "";
    return $data;
  }
}

// 認証キー生成
function makeRandKey($length = 8)
{
  static $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJLKMNOPQRSTUVWXYZ0123456789';
  $str = "";
  for ($i = 0; $i < $length; $i++) {
    $str .= $chars[mt_rand(0, 61)];
  }
  return $str;
}

// サニタイズ
function sanitize($str)
{
  return htmlspecialchars($str, ENT_QUOTES);
}

// フォーム入力保持
function getFormData($str, $flg = false)
{
  if ($flg) {
    $method = $_GET;
  } else {
    $method = $_POST;
  }

  global $dbFormData;
  // ユーザーデータがある場合
  if (!empty($dbFormData)) {
    // フォームのエラーがある場合
    if (!empty($_POST[$str])) {
      // POSTにデータがある場合
      if (isset($method[$str])) {
        return sanitize($method[$str]);
      } else {
        // ない場合はDBの情報を表示
        return sanitize($dbFormData[$str]);
      }
    } else {
      // POSTにデータがあり、DBの情報と違う場合
      if (isset($method[$str]) && $method[$str] !== $dbFormData[$str]) {
        return sanitize($method[$str]);
      } else {
        return sanitize($dbFormData[$str]);
      }
    }
  } else {
    if (isset($method[$str])) {
      return sanitize($method[$str]);
    }
  }
}

// 画像処理
function uploadImg($file, $key)
{
  debug('画像アップロード処理開始');
  debug('FILE情報：' . print_r($file, true));

  if (isset($file['error']) && is_int($file['error'])) {
    try {
      switch ($file['error']) {
        case UPLOAD_ERR_OK:
          break;
        case UPLOAD_ERR_NO_FILE:
          throw new RuntimeException('ファイルが選択されていません');
        case UPLOAD_ERR_INI_SIZE:
          throw new RuntimeException('php.ini定義の最大サイズが超過');
        case UPLOAD_ERR_FORM_SIZE:
          throw new RuntimeException('ファイルサイズが大きすぎます');
        default:
          throw new RuntimeException('その他のエラーが発生しました');
      }

      $type = @exif_imagetype($file['tmp_name']);
      if (!in_array($type, [IMAGETYPE_GIF, IMAGETYPE_JPEG, IMAGETYPE_PNG], true)) {
        throw new RuntimeException('画像形式が未対応です');
      }

      $path = 'uploads/' . sha1_file($file['tmp_name']) . image_type_to_extension($type);
      if (!move_uploaded_file($file['tmp_name'], $path)) {
        throw new RuntimeException('ファイル保存時にエラーが発生しました');
      }
      chmod($path, 0644);

      debug('ファイルは正常にアップロードされました');
      debug('ファイル：' . $path);
      return $path;
    } catch (RuntimeException $e) {
      debug($e->getMessage());
      global $err_msg;
      $err_msg[$key] = $e->getMessage();
    }
  }
}

// 画像表示用関数
function showImg($path)
{
  if (empty($path)) {
    return 'img/sample-img.png';
  } else {
    return $path;
  }
}

// GETパラメータ付加
function appendGetParam($arr_del_key = array())
{
  if (!empty($_GET)) {
    $str = '?';
    foreach ($_GET as $key => $val) {
      if (!in_array($key, $arr_del_key, true)) {
        $str .= $key . '=' . $val . '&';
      }
    }
    $str = mb_substr($str, 0, -1, "UTF-8");
    return $str;
  }
}
function getCategory()
{
  debug('カテゴリー情報を取得します。');
  try {
    $dbh = dbConnect();
    $sql = 'SELECT * FROM category';
    $data = array();
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      return $stmt->fetchAll();
    } else {
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生：' . $e->getMessage());
  }
}

function getMyrecords($u_id)
{
  debug('自分の商品情報を取得します。');
  debug('ユーザーID：' . $u_id);

  // 例外処理
  try {
    $dbh = dbConnect();
    $sql = 'SELECT * FROM record WHERE user_id = :u_id AND delete_flg = 0';
    $data = array('u_id' => $u_id);
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      return $stmt->fetchAll();
    } else {
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生：' . $e->getMessage());
  }
}


function getrecordList($currentMinNum = 1, $category, $sort, $span = 20)
{
  debug('商品情報を取得します。');
  //例外処理
  try {
    $dbh = dbConnect();
    // 件数用のSQL文作成
    $sql = 'SELECT id FROM record';
    if (!empty($category)) $sql .= ' WHERE category_id = ' . $category;
    if (!empty($sort)) {
      switch ($sort) {
        case 1:
          $sql .= ' ORDER BY create_date ASC';
          break;
        case 2:
          $sql .= ' ORDER BY create_date DESC';
          break;
      }
    }
    $data = array();
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);
    //総レコード数
    $rst['total'] = $stmt->rowCount(); 
    //総ページ数
    $rst['total_page'] = ceil($rst['total'] / $span); 
    if (!$stmt) {
      return false;
    }

    // ページング用のSQL文作成
    $sql = 'SELECT * FROM record';
    if (!empty($category)) $sql .= ' WHERE category_id = ' . $category;
    if (!empty($sort)) {
      switch ($sort) {
        case 1:
          $sql .= ' ORDER BY tr_day DESC';
          break;
        case 2:
          $sql .= ' ORDER BY tr_day ASC';
          break;
      }
    }
    $sql .= ' LIMIT ' . $span . ' OFFSET ' . $currentMinNum;
    $data = array();
    debug('SQL：' . $sql);
    // クエリ実行
    $stmt = queryPost($dbh, $sql, $data);

    if ($stmt) {
      // 全レコードを格納
      $rst['data'] = $stmt->fetchAll();
      return $rst;
    } else {
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生:' . $e->getMessage());
  }
}


function pagination($currentPageNum, $totalPageNum, $link = '', $pageColNum = 5)
{
  if ($currentPageNum == $totalPageNum && $totalPageNum >= $pageColNum) {
    $minPageNum = $currentPageNum - 4;
    $maxPageNum = $currentPageNum;
  } elseif ($currentPageNum == ($totalPageNum - 1) && $totalPageNum >= $pageColNum) {
    $minPageNum = $currentPageNum - 3;
    $maxPageNum = $currentPageNum + 1;
  } elseif ($currentPageNum == 2 && $totalPageNum >= $pageColNum) {
    $minPageNum = $currentPageNum - 1;
    $maxPageNum = $currentPageNum + 3;
  } elseif ($currentPageNum == 1 && $totalPageNum >= $pageColNum) {
    $minPageNum = $currentPageNum;
    $maxPageNum = 5;
  } elseif ($totalPageNum < $pageColNum) {
    $minPageNum = 1;
    $maxPageNum = $totalPageNum;
  } else {
    $minPageNum = $currentPageNum - 2;
    $maxPageNum = $currentPageNum + 2;
  }
  echo '<div class="pagination">';
  echo '<ul class="pagination-list">';
  if ($currentPageNum != 1) {
    echo '<li class="list-item"><a href="?p=1 ' . $link . ' ">&lt;</a></li>';
  }
  for ($i = $minPageNum; $i <= $maxPageNum; $i++) {
    echo '<li class="list-item ';
    if ($currentPageNum == $i) {
      echo 'active';
    }
    echo ' "> <a href="?p=  ' . $i . $link . ' ">' . $i . '</a></li>';
  }
  if ($currentPageNum != $maxPageNum && $maxPageNum > 1) {
    echo '<li class="list-item"><a href="?p= ' . $maxPageNum . $link . '">&gt;</a></li>';
  }
  echo '</ul>';
  echo '</div>';
}

function getRecordOne($p_id){
  debug('商品情報を取得します。getrecordone');
  debug('商品ID：'.$p_id);
  // 例外処理
  try{
    $dbh = dbConnect();
    $sql = 'SELECT r.id, r.name, r.tr_day, r.tr_time, r.comment, r.pic1, r.pic2, r.pic3, r.user_id, r.create_date, r.update_date, c.name AS category FROM record AS r LEFT JOIN category AS c ON r.category_id = c.id WHERE r.id = :r_id AND r.delete_flg = 0 AND c.delete_flg = 0';
    $data = array(':r_id' => $p_id);
    $stmt = queryPost($dbh, $sql, $data);
    if($stmt){
      return $stmt->fetch(PDO::FETCH_ASSOC);
    }else{
      return false;
    }
  }catch(Exception $e){
    error_log('エラー発生：' .$e->getMessage());
  }
}

function getMsgAndBoard($id){
  debug('msg情報を取得します。');
  debug('掲示板ID：'.$id);
  // 例外処理
  try{
    $dbh = dbConnect();
    $sql = 'SELECT m.id AS m_id, record_id, board_id, send_date, to_user, from_user, receive_user, send_user, msg, b.create_date FROM message AS m RIGHT JOIN board AS b ON b.id = m.board_id WHERE b.id = :id ORDER BY send_date ASC';
    $data = array(':id' => $id);
    $stmt = queryPost($dbh, $sql, $data);
    if($stmt){
      return $stmt->fetchAll();
    }else{
      return false;
    }
  }catch(Exception $e){
    error_log('エラー発生：' .$e->getMessage());
  }
}

function getMyMsgAndBoard($u_id){
  debug('自分のmsg情報を取得します。');
  try{
    $dbh = dbConnect();
    $sql = 'SELECT * FROM board AS b WHERE b.receive_user = :id OR b.send_user = :id AND b.delete_flg = 0';
    $data = array(':id' => $u_id);
    $stmt =  queryPost($dbh, $sql, $data);
    $rst = $stmt->fetchAll();
    if(!empty($rst)){
      foreach($rst as $key => $val){
        $sql = 'SELECT * FROM message AS m left join users as u on m.to_user = u.id WHERE board_id = :id ORDER BY send_date desc';
        $data = array(':id' => $val['id']);
        $stmt = queryPost($dbh, $sql, $data);
        $rst[$key]['msg'] = $stmt->fetchAll();
      }
    }
    if($stmt){
      return $rst;
    }else{
      return false;
    }
  }catch(Exception $e){
    error_log('エラー発生：'.$e->getMessage());
  }
}
// $sql = 'SELECT * FROM message LEFT JOIN users ON  message.from_user = users.id WHERE board_id = :id AND delete_flg = 0 ORDER BY send_date DESC';
// $sql = 'SELECT * FROM message AS m left join users as u on m.from_user = u.id WHERE board_id = :id AND delete_flg = 0 ORDER BY send_date DESC';

function getDeleteRecord($id){
  debug('DB削除しました。');
  try {
    $dbh = dbConnect();
    $sql = 'DELETE FROM record WHERE id = :id';
    $data = array(':id' => $id);
    $stmt = queryPost($dbh, $sql, $data);
    if ($stmt) {
      return $stmt->fetch(PDO::FETCH_ASSOC);
      $_SESSION['msg_success'] = '削除しました';
      debug('マイページへ遷移します。');
      header("Location:mypage.php");
    }else{
      return false;
    }
  } catch (Exception $e) {
    error_log('エラー発生：' . $e->getMessage());
    $err_msg['common'] = 'エラーが発生しました。';
  }
}
