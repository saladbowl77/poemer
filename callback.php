<?php
session_start();

require_once "twitteroauth/twitteroauth.php";

// リクエストト－クンを使用してユーザーのtwitter情報を取得する
$connection = new TwitterOAuth(CONSUMER_KEY,CONSUMER_SECRET,
                               $_SESSION["request_token"],$_SESSION["request_token_secret"]);
// twitterのユーザー情報の取得
$access_token = $connection->getAccessToken($_GET['oauth_verifier']);

  // アクセストークン
  echo 'user_id:'     . $access_token['user_id'] . '<br />';
  echo 'screen_name:' . $access_token['screen_name'] . '<br />';
  echo 'oauth_token:' . $access_token['oauth_token'] . '<br />';
  echo 'oauth_token_secret:' . $access_token['oauth_token_secret'] . '<br />';

  echo "<br />";

  echo "<pre>";
  var_dump($access_token);
  echo "</pre>";

// セッション変数を全て解除
$_SESSION = array();

// セッションクッキーの削除
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-42000, '/');
}

// セッションの破棄
session_destroy();

?>
