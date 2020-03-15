<header>
    <h1><a href="index.php">Work out</a></h1>
    <nav class="nav">
      <ul>
        <?php if (empty($_SESSION['user_id'])) { ?>
          <li><a href="sign.php">ユーザー登録</a></li>
          <li><a href="login.php">ログイン</a></li>
        <?php } else { ?>
          <li><a href="mypage.php">マイページ</a></li>
          <li><a href="logaut.php">ログアウト</a></li>
        <?php } ?>
      </ul>
    </nav>
  </header>