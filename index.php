<?php 
// 共通変数・関数ファイルを読み込み
require('function.php');
debug('-----------------------------------------------------------');
debug('  トップページ  ');
debug('-----------------------------------------------------------');
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

debug('画面表示処理終了 ----------------------------------------------');
?>

<?php 
$siteTitle = "HOME";
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
  input[type="submit"]{
  width: 100%;
}

</style>

<body>
<!-- メニュー -->
<?php require('header.php'); ?>

<!-- メインコンテンツ -->
<div class="content">
<div class="colum">
<!-- サイドバー -->
<section class="sidebar-right">
<form method="get">
<h1 class="title">カテゴリー</h1>
<div class="selectbox">
<span class="icn_select"></span>
<select name="c_id" id="">
<option value="0" <?php if(getFormData('c_id', true) == 0){
  echo 'selected';
  } ?>>選択してください</option>
  <?php foreach($dbCategoryData as $key => $val){ ?>
  <option value="<?php echo $val['id'] ?>" <?php if(getFormData('c_id', true) == $val['id']){
    echo 'selected';
  } ?>>
  <?php echo $val['name']; ?>
  </option>
<?php } ?>
</select>
</div>
<h1 class="title">表示順</h1>
<div class="selectbox">
<span class="icn_select"></span>
<select name="sort">
<option value="0" <?php if(getFormData('sort', true) == 0){
  echo 'selected';
} ?>>選択してください</option>
<option value="1" <?php if(getFormData('sort', true) == 1){
  echo 'selected';
} ?>>新しい日付</option>
<option value="2" <?php if(getFormData('sort', true) == 2){
  echo 'selected';
} ?>>前の日付</option>
</select>
</div>
<input type="submit" value="検索">
</form>
</section>

<!-- メイン -->
<section class="section page-2colum">


<div class="search-title">
<div class="search-left">
<span class="total-num"><?php echo sanitize($dbRecordData['total']); ?></span>
件のレコードが見つかりました。
</div>
<div class="search-right">
<span class="num"><?php echo(!empty($dbRecordData['data'])) ? $currentMinNum+1 : 0; ?></span>-<span class="num"><?php echo $currentMinNum+count($dbRecordData['data']); ?></span>件 / <span class="num"><?php echo sanitize($dbRecordData['total']); ?></span>件中
</div>
</div>
<div class="panel-list">
<?php foreach($dbRecordData['data'] as $key => $val): ?>
<a href="recordDetail.php<?php echo (!empty(appendGetParam())) ? appendGetParam(). '&p_id='.$val['id'] : '?p_id=' .$val['id']; ?>" class="panel">
<div class="panel-head">
<img src="<?php echo sanitize($val['pic1']); ?>" alt="<?php echo sanitize($val['name']); ?>">
</div>
<div class="panel-body">
<p class="panel-title"><?php echo sanitize($val['name']); ?></p>
</div>
</a>
<?php endforeach; ?>



</div>
<?php pagination($currentPageNum, $dbRecordData['total_page']); ?>
</section>
</div>
</div>

<!-- フッター -->
<?php require('footer.php'); ?>