<?php
session_start();

require_once "twitteroauth/twitteroauth.php";

// Twitterのコンシューマー情報を使用してTwitterOAuthオブジェクトを生成します
$connection = new TwitterOAuth("efuGfr3tXZAIZiB7UohigPx1C", "1dodcvcQYv2lmabfZBNNJR1y3hv0eYkX0pnzbGAQdzKU2obQ1m");

// 認証後のコールバックURLを指定
// ※本来はここに記述するのだが、エラーが出るので[https://apps.twitter.com]側で設定
$request_token   = $connection->getRequestToken();

// リクエストトークンの取得(後で使用するのでセッションに保存)
$_SESSION['request_token']        = $request_token['oauth_token'];
$_SESSION['request_token_secret'] = $request_token['oauth_token_secret'];

// 認証用URLの取得
if ($connection->http_code == 200) {
   // 認証URLを取得する
   $url = $connection->getAuthorizeURL($request_token['oauth_token']);
}else{
   $url = "error.html";
}

// ジャンプ
header('Location: ' . $url) ;
exit();
?>
