<!DOCTYPE HTML>
<html lang="ja">
<head>
<meta charset="utf-8">
<title>Twitter REST API OAuth認証 画像付きツイート投稿 [POST media/upload.json statuses/update.json] | WEPICKS!</title>
</head>
<body>

<h1>Twitter REST API OAuth認証 画像付きツイート投稿 [POST media/upload.json statuses/update.json]</h1>
<!-- 説明ページurl -->
<h2><a href="/twitter-restapi-tmhoauth/">→説明はこちら</a></h2>

<?php
#########################################
### 画像とツイートの投稿

//アップロードファイル確認
if(!empty($_FILES)){

 $twObj = NULL;
 $sUpdImgBase64 = '';
 $aUpdImgParams = array();
 $iImgCode = 0;
 $aResUpload = array();
 $iMediaId = 0;
 $aUptParams = array();
 $aResUpdate = array();

 //tmhOAuth.phpインクルード
 require('./tmhOAuth.php');

 //サーバー上のtempファイル名を格納
 $sImgTmpName = $_FILES['picture']['tmp_name'];

 //Access Tokenの設定 apps.twitter.com でご確認下さい。
 //Consumer keyの値を格納
 $sConsumerKey = "efuGfr3tXZAIZiB7UohigPx1C";
 //Consumer secretの値を格納
 $sConsumerSecret = "1dodcvcQYv2lmabfZBNNJR1y3hv0eYkX0pnzbGAQdzKU2obQ1m";
 //Access Tokenの値を格納
 $sAccessToken = "1266247450131435520-vvHQmUmN4EnAjc8FGhfa0cmSBqiHGL";
 //Access Token Secretの値を格納
 $sAccessTokenSecret = "FyT21KIGIMo9nW5nJZValqCU3DuWHHeP0uJt3QsBL6fF0";

 //OAuthオブジェクトを生成する
 $twObj = new tmhOauth(
 array(
 "consumer_key" => $sConsumerKey,
 "consumer_secret" => $sConsumerSecret,
 "token" => $sAccessToken,
 "secret" => $sAccessTokenSecret,
 "curl_ssl_verifypeer" => false,
 )
 );

 //PHPでアップロードした画像をbase64エンコードします。
 $sUpdImgBase64 = base64_encode(file_get_contents($sImgTmpName));

 //パラメータの作成
 $aUpdImgParams = array('media_data' =>  $sUpdImgBase64);

 //base64エンコードした画像をtwitterに送信
 $iImgCode = $twObj->request( 'POST', "https://upload.twitter.com/1.1/media/upload.json", $aUpdImgParams, true, true);

 // media/upload.json の結果をjson文字列で受け取り配列に格納
 $aResUpload = json_decode($twObj->response["response"], true);

 //メディアIDの取得
 $iMediaId = $aResUpload['media_id'];

 //メディアIDとツイート文字列のパラメータを作成
 $aUptParams = array(
 'media_ids' =>  $iMediaId,//取得したmedia_id
 'status' =>  $_POST['tweet']//ツイート内容
 );

 //メディアIDとツイート文字列をTwitterに送信
 $iImgCode = $twObj->request( 'POST', "https://api.twitter.com/1.1/statuses/update.json", $aUptParams);

 // statuses/update.json の結果をjson文字列で受け取り配列に格納
 $aResUpdate = json_decode($twObj->response["response"], true);

 //配列を展開
 if(isset($aResUpdate['errors']) && $aResUpdate['errors'] != ''){
 ?>
 <h1>投稿に失敗しました。</h1>
 エラー内容：<br/>
 <pre>
 <?php var_dump($aResUpdate); ?>
 </pre>
 <?php
 }else{
 //id と media url を表示
 echo '<h1>IDとmedia urlの表示</h1>';
 echo '投稿ID：'.$aResUpdate['id']."<br/>\n";
 echo 'media ID：'.$aResUpdate['entities']['media'][0]['id']."<br/>\n";
 echo 'media url：'.$aResUpdate['entities']['media'][0]['media_url']."<br/>\n";
 echo '<img src="'.$aResUpdate['entities']['media'][0]['media_url'].'" />'."<br/>\n";
 echo 'screen_name：<a href="https://twitter.com/'.$aResUpdate['user']['screen_name'].'" target="_blank">'.$aResUpdate['user']['screen_name']."</a><br/>\n";

 //すべての内容
 echo '<h1>$aResUpdate 内容をvar_dumpで確認</h1>';
 echo '<pre>';
 var_dump($aResUpdate);
 echo '</pre>';
 }
}
?>

<?php
#########################################
### 投稿フォーム
?>

<h1>投稿フォーム</h1>
<form action="update_tmh.php" method="POST" enctype="multipart/form-data">
画像選択：<input type="file" name="picture"><br>
ツイート：<textarea name="tweet"></textarea><br>
<input type="submit" value="送信" />
</form>

</body>
</html>
