<?php
/*****

	画像付きツイートを投稿する

	使い方:
		[設定項目]に必要な情報を指定して実行して下さい。

	解説:
		SYNCER
		https://syncer.jp/Web/API/Twitter/Snippet/1/

	質問掲示板:
		SYNCER FORUM
		https://forum.syncer.jp/t/twitter-rest-api/58

*****/


/***** 設定項目 *****/
$api_key = "efuGfr3tXZAIZiB7UohigPx1C" ;	// APIキー
$api_secret = "1dodcvcQYv2lmabfZBNNJR1y3hv0eYkX0pnzbGAQdzKU2obQ1m" ;	// APIシークレット
$access_token = "1266247450131435520-vvHQmUmN4EnAjc8FGhfa0cmSBqiHGL" ;	// アクセストークン
$access_token_secret = "FyT21KIGIMo9nW5nJZValqCU3DuWHHeP0uJt3QsBL6fF0" ;	// アクセストークンシークレット
$media_files = [ "./image.png"] ;	// 画像ファイルのパス (4つまで指定可)
$status = 'APIを利用してツイートを投稿しました。この投稿は削除予定です。' ;	// ツイート本文


/***** プログラムの実行 *****/
// メディアファイルのアップロード
$media_ids = [] ;

foreach ( $media_files as $media_file ) {
	list( $response_body, $response_header ) = syncer_twitter_rest_api_request ( $api_key, $api_secret, $access_token, $access_token_secret, "https://upload.twitter.com/1.1/media/upload.json", "POST", [
		"media" => file_get_contents( $media_file ),
	] ) ;

	if ( $response_body ) {
		$arr = json_decode( $response_body, true ) ;
	}

	if ( !isset( $arr["media_id_string"] ) || !$arr["media_id_string"] ) {
		echo "画像のアップロードに失敗しました。(" . $media_file . ")" ;
		exit ;
	} else {
		$media_ids[] = $arr["media_id_string"] ;
	}
}

// ツイートの投稿
list( $response_body, $response_header ) = syncer_twitter_rest_api_request ( $api_key, $api_secret, $access_token, $access_token_secret, "https://api.twitter.com/1.1/statuses/update.json", "POST", [
	"status" => $status ,
	"media_ids" => implode( ",", $media_ids ) ,
] ) ;

$arr = json_decode( $response_body, true ) ;

if ( isset($arr["id_str"]) && $arr["id_str"] ) {
	echo '<p>ツイートの投稿に成功しました。(<a href="https://twitter.com/u/status/' . $arr["id_str"] . '" target="_blank">リンク</a>)</p>' ;
	echo '<blockquote class="twitter-tweet" data-lang="ja"><p lang="ja" dir="ltr"><a href="https://twitter.com/u/status/' . $arr["id_str"] . '"></a></blockquote>
<script type="text/javascript" async src="https://platform.twitter.com/widgets.js"></script>' ;
	exit ;
}


/*** Twitter Rest APIの汎用関数 ***/
// 作成者: SYNCER
// 作成日時: 2017-01-29
// 更新情報:
// 	2017-01-29: 作成しました。
// 使用条件:
// 	・再配布禁止
// 	・転載禁止
// お問い合わせ: https://twitter.com/arayutw
/*** ***/
function syncer_twitter_rest_api_request ( $api_key="", $api_secret="", $access_token="", $access_token_secret="", $request_url="", $request_method="", $params_a=[] ) {
	$request_headers = [] ;
	$request_body = "" ;

	$params_b = array(
		'oauth_token' => $access_token ,
		'oauth_consumer_key' => $api_key ,
		'oauth_signature_method' => 'HMAC-SHA1' ,
		'oauth_timestamp' => time() ,
		'oauth_nonce' => microtime() ,
		'oauth_version' => '1.0' ,
	) ;

	switch ( $request_method ) {
		case "POST" :
			switch( $request_url ) {
				case( 'https://api.twitter.com/1.1/account/update_profile_background_image.json' ) :
				case( 'https://api.twitter.com/1.1/account/update_profile_image.json' ) :
					$media_param = 'image' ;
				break ;

				case( 'https://api.twitter.com/1.1/account/update_profile_banner.json' ) :
					$media_param = 'banner' ;
				break ;

				case( 'https://upload.twitter.com/1.1/media/upload.json' ) :
					$media_param = ( isset($params_a['media']) && !empty($params_a['media']) ) ? 'media' : 'media_data' ;
				break ;
			}

			// multipart POST
			if ( isset($media_param) && isset($params_a[ $media_param ]) ) {
				$media_data = ( $params_a[ $media_param ] ) ? $params_a[ $media_param ] : "" ;

				if( isset( $params_a[ $media_param ] ) ) unset( $params_a[ $media_param ] ) ;

				$boundary = 's-y-n-c-e-r---------------' . md5( mt_rand() ) ;

				$request_body .= '--' . $boundary . "\r\n" ;
				$request_body .= 'Content-Disposition: form-data; name="' . $media_param . '"; ' ;
				$request_body .= "\r\n" ;
				$request_body .= "\r\n" . $media_data . "\r\n" ;

				foreach( $params_a as $key => $value ) {
					$request_body .= '--' . $boundary . "\r\n" ;
					$request_body .= 'Content-Disposition: form-data; name="' . $key . '"' . "\r\n\r\n" ;
					$request_body .= $value . "\r\n" ;
				}

				$request_body .= '--' . $boundary . '--' . "\r\n\r\n" ;

				$request_headers[] = "Content-Type: multipart/form-data; boundary=" . $boundary ;

				$params_c = $params_b ;

			// POST
			} else {
				switch ( $request_url ) {
					case "https://api.twitter.com/1.1/collections/entries/curate.json" :
						$params_c = $params_b ;
						$request_body = $params_a ;
					break ;

					default :
						$params_c = array_merge( $params_a , $params_b ) ;

						if ( $params_a ) {
							$request_body = http_build_query( $params_a ) ;
						}
					break ;
				}
			}
		break ;

		// GET
		case "GET" :
			$params_c = array_merge( $params_a , $params_b ) ;
		break ;
	}

	ksort( $params_c ) ;

	$signature_key = rawurlencode( $api_secret ) . '&' . rawurlencode( $access_token_secret ) ;

	$request_params = http_build_query( $params_c, '', '&' ) ;
	$request_params = str_replace( array( '+', '%7E' ) , array( '%20', '~' ) , $request_params ) ;
	$request_params = rawurlencode( $request_params ) ;
	$encoded_request_method = rawurlencode( $request_method ) ;
	$encoded_request_url = rawurlencode( $request_url ) ;
	$signature_data = $encoded_request_method . '&' . $encoded_request_url . '&' . $request_params ;
	$hash = hash_hmac( 'sha1' , $signature_data , $signature_key , TRUE ) ;
	$signature = base64_encode( $hash ) ;
	$params_c['oauth_signature'] = $signature ;
	$header_params = http_build_query( $params_c , '' , ',' ) ;

	$context = array(
		'http' => array(
			'method' => $request_method ,
			'header' => array(
				'Authorization: OAuth ' . $header_params ,
			) ,
			'content' => $request_body ,
		) ,
	) ;

	if ( $request_headers ) {
		$context['http']['header'] = array_merge( $context['http']['header'], $request_headers ) ;
	}

	if( $request_method == "GET" ) {
		$request_url .= '?' . http_build_query( $params_a ) ;
	}

	$curl = curl_init() ;
	curl_setopt( $curl, CURLOPT_URL , $request_url ) ;
	curl_setopt( $curl, CURLOPT_HEADER, true ) ;
	curl_setopt( $curl, CURLOPT_CUSTOMREQUEST , $context['http']['method'] ) ;
	curl_setopt( $curl, CURLOPT_SSL_VERIFYPEER , false ) ;
	curl_setopt( $curl, CURLOPT_RETURNTRANSFER , true ) ;
	curl_setopt( $curl, CURLOPT_HTTPHEADER , $context['http']['header'] ) ;
	if ( isset($context['http']['content']) ) {
		curl_setopt( $curl, CURLOPT_POSTFIELDS , $context['http']['content'] ) ;
	}
	curl_setopt( $curl, CURLOPT_TIMEOUT, 5 ) ;
	$res1 = curl_exec( $curl ) ;
	$res2 = curl_getinfo( $curl ) ;
	curl_close( $curl ) ;

	$response_body = substr( $res1, $res2['header_size'] ) ;
	$response_header = substr( $res1, 0, $res2['header_size'] ) ;

	return [ $response_body, $response_header ] ;
}
