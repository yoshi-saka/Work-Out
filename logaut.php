<?php
require('function.php');

debug('------------------------------------------');
debug(' ログアウト ');
debug('------------------------------------------');

debugLogStart();

debug('ログアウトします。');
session_destroy();
debug('ログインページへ遷移します。');
header("Location:login.php");
?>