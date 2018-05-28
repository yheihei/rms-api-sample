<?php

require_once('config.php');
require_once('util.php');
require_once('class/cabinetFileSetting.php');
require_once('class/cabinetUploadFileInfo.php');

ini_set('xdebug.var_display_max_children', -1);
ini_set('xdebug.var_display_max_data', -1);
ini_set('xdebug.var_display_max_depth', -1);

echo "hoge";

/***
 * 画像情報のセット
 * */

// 送信したいファイルの情報設定
$uploadFileInfo = new CabinetUploadFileInfo(__DIR__ . '/image/rrrz_01.jpg'); // 送信したいファイルの絶対パスを指定

// var_dump($uploadFileInfo);

// 画像パスや上書き設定など
$cabinetFileSetting = new CabinetFileSetting();
$cabinetFileSetting->fileName = 'test_' . randomStr(3) . '_' . date_format(new DateTime('now', new DateTimeZone('Asia/Tokyo')), 'YmdHis');
$cabinetFileSetting->folderId = 0; // 0は基本フォルダ
$cabinetFileSetting->filePath = $cabinetFileSetting->fileName . "." . $uploadFileInfo->extension; // 拡張子つける
// NOTE: $uploadFileInfo->extensionがnullの場合対応していない拡張子のファイルなのでエラーを起こした方が良い
$cabinetFileSetting->overWrite = 1; // overWriteがtrueかつfilePathの指定がある場合、filePathをキーとして画像情報を上書きすることができます

// 楽天へRMS APIを使って画像アップロード
list($reqXml, $httpStatusCode, $response) = cabinetFileInsert($cabinetFileSetting, $uploadFileInfo);



//////////////// 関数群 ////////////////////

/*
* APIのリクエストを行う
* xmlを作って curlでpostしてる
* @param 挿入したい商品情報のクラスオブジェクト
* @return リクエストしたxml文字列, httpステータスコード, レスポンス文字列(xmlで返ってくる)
*/
function cabinetFileInsert($cabinetFileSetting, $uploadFileInfo) {
  $authkey = base64_encode(RMS_SERVICE_SECRET . ':' . RMS_LICENSE_KEY);

  $url = RMS_API_CABINET_FILE_INSERT;
  // $ch = curl_init($url);
  
  $reqXml = _createRequestXml($cabinetFileSetting);
  
  // stringからDOM構築
  $dom = DOMDocument::loadXML($reqXml);
  
  // encodingが指定されてないと&#x30C6;&#x30B9;&#x30C8;みたいになるので何か指定する
  // ログ目的なら問答無用でUTF-8とかに統一してもいいと思う
  if (!$dom->encoding) {
      $dom->encoding = 'UTF-8';
  }
  
  // 整形して出力するフラグ
  $dom->formatOutput = true;
  
  // 文字列で取得
  $reqXml = $dom->saveXML();
  
  $file = $uploadFileInfo->filePath;    //アップロードするテキストファイル 
  $files = array(
      'file' => $file
  );
  
  $params = array(
      'xml' => $reqXml
  );
  
  $response = httpPost($url, $params, $files);
  return array($reqXml, null, $response);
  
  // リクエスト+送信するファイルデータの作成
  // $boundary = "------" . md5(mt_rand() . microtime());
  // $data = $boundary . "\r\n" .
  //   'Content-Disposition: form-data; name="xml"' . "\r\n" .
  //   $reqXml . "\r\n" . $boundary . "\r\n"; // リクエストxmlの前後にboundary挿入
  
  // // 送信するファイルデータを付与
  // // $binary = base64_encode(file_get_contents($uploadFileInfo->filePath));
  // $binary = file_get_contents($uploadFileInfo->filePath);
  // // var_dump($uploadFileInfo->filePath);
  // $contentLength = strlen($binary);
  // $data = $data .
  //   'Content-Disposition: form-data; name="file"; filename="' . $cabinetFileSetting->filePath .'"' . "\r\n" .
  //   'Content-Type: ' . $uploadFileInfo->mimeType . "\r\n\r\n" . 
  //   $binary . "\r\n" ."--$boundary--" . "\r\n";
  
  // // var_dump($contentLength);
  
  // $header = array(
  //   "Proxy-Connection: keep-alive",
  //   // "Content-Length: 999999",
  //   "Content-Type: multipart/form-data; boundary={$boundary}",
  //   // "Content-Type: multipart/form-data;",
  //   "Authorization: ESA {$authkey}",
  //   "Accept-Encoding: gzip,deflate",
  //   "Accept-Language: ja,en-US;q=0.8,en;q=0.6"
  // );
  
  // var_dump($header);
  
  // if (function_exists('curl_file_create')) { // php 5.5+
  //   $cFile = curl_file_create($uploadFileInfo->filePath, $uploadFileInfo->mimeType, $cabinetFileSetting->filePath);
  // } else { // 
  //   $cFile = '@' . realpath($uploadFileInfo->filePath);
  // }
  
  // $data = array(
  // 	'xml' => $reqXml,
  // 	'file' => $cFile
  // // 	'file' => "@{$uploadFileInfo->filePath};filename={$cabinetFileSetting->filePath};type={$uploadFileInfo->mimeType}"
  // 	// 書式例
  // 	// 'file' => '@./test.jpg;filename=upload.jpg;type=image/jpeg'
  // );
  
  // var_dump($cFile);
  
  // var_dump($data['file']);
  
  // return array($reqXml, $httpStatusCode, $response);
  
  // var_dump($data);
  
  curl_setopt($ch, CURLOPT_POSTFIELDS,     $data);
  curl_setopt($ch, CURLOPT_POST,           true);
  // curl_setopt($ch, CURLOPT_TIMEOUT,        30);
  // curl_setopt($ch, CURLOPT_SAFE_UPLOAD, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //返り値を 文字列で返します
  curl_setopt($ch, CURLINFO_HEADER_OUT, true);
  $response = curl_exec($ch);
  if(curl_error($ch)){
    $response = curl_error($ch);
    error_log($response);
  }
  
  $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  echo "<pre>";
  var_dump(curl_getinfo($ch,CURLINFO_HEADER_OUT));
  var_dump($data);
  // var_dump($response);
  echo "</pre>";
  error_log($response);
  curl_close($ch);

  return array($reqXml, $httpStatusCode, $response);
}

define('CRLF', "\r\n");
//$urlに、$paramsのパラメータをpost
function httpPost($url, $params, $files = []){

    $isMultipart = (count($files)) ? true : false;
    $authkey = base64_encode(RMS_SERVICE_SECRET . ':' . RMS_LICENSE_KEY);

    if ($isMultipart){

        //ファイルアップロードを伴う場合、multipartで送信

        $boundary = '---------------------------'.'yheihogehogeyhei';

        $contentType = "Content-Type: multipart/form-data; boundary=" . $boundary;

        $data = '';

        foreach($params as $key => $value) {

            $data .= "--$boundary" . "\r\n";

            $data .= 'Content-Disposition: form-data; name=' . '"'. $key . '"' . "\r\n" . "\r\n";

            $data .= $value . "\r\n";

        }

        foreach($files as $key => $file) {
            $data .= "--$boundary" . "\r\n";
            $data .= sprintf('Content-Disposition: form-data; name="%s"; filename="%s"%s', $key, basename($file), "\r\n");
            $data .= 'Content-Type: image/jpeg'. "\r\n";
            $data .= file_get_contents($file) . "\r\n";
        }

        $data .= "--$boundary--" . "\r\n";

    } else {

        //パラメータのみを送信

        $contentType = 'Content-Type: application/x-www-form-urlencoded';

        $data = http_build_query($params);

    }

    $headers = array(
        "Connection: keep-alive",
        "Proxy-Connection: keep-alive",
        $contentType,
        'Content-Length: '.strlen($data),
        "Authorization: ESA {$authkey}",
        // "Accept: */*",
        // "Accept-Encoding: gzip,deflate",
        // "Accept-Language: ja,en-US;q=0.8,en;q=0.6"
    );
    $header = implode("\r\n", $headers);
    var_dump($header);

    $options = array('http' => array(
        'method'  => 'POST',
        'ignore_errors' => true, //trueにすると40x,50x系のエラーでも内容を取得できる。
        'content' => $data,
        'header'  => $header
    ));

    $contents = file_get_contents($url, false, stream_context_create($options));
    
    echo "<pre>";
    foreach($http_response_header as $header){
      echo $header . '<br>';
    }
    echo "</pre>";

    return $contents;

}

/*
* 渡したclassオブジェクトからリクエストのXMLを自動生成する
* 注意. xmlの要素の順番を変えると400でwrong formatエラーが返却されるクソ仕様。
*       cabinet.file.getでxmlの要素の順番を確認しながら行うと無難(API仕様書でも良いが間違ってないという保証はない)
*/
function _createRequestXml($cabinetFile) {

  // リクエストXMLのガワを作る
  $rootXml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><request/>');
  $fileInsertRequestXml = $rootXml->addChild('fileInsertRequest');
  $fileXml = $fileInsertRequestXml->addChild('file');
  
  // 受け取った商品情報オブジェクトをarrayに変換
  $array = _convertClassObjectToArray($cabinetFile);
  
  _arrayToXml($array, $fileXml);  // リクエストのXMLをarray情報から作成する
  
  return $rootXml->asXML(); // リクエストのXMLを返却する
}

/**
 * Convert an array to XML
 * @param array $array
 * @param SimpleXMLElement $xml
 * @param array $parentKeyName (その要素が配列で、子要素を親要素の単数形にして登録したい時指定)
 */
function _arrayToXml($array, &$xml, $parentKeyName=null){
  foreach ($array as $key => $value) {
    if(is_array($value)){
      if(is_int($key)){
          if(!empty($parentKeyName)) {
            // 親要素が存在する時、子要素を親要素の単数形の名前にして登録
            $key = singularByPlural($parentKeyName);
          }
      }
      $label = $xml->addChild($key);
      _arrayToXml($value, $label, $key);
    }
    else if(!is_null($value)){
      // 値がセットされている時だけxml要素に追加
      $xml->addChild($key, $value);
    }
  }
}

/**
 * Convert an classObject to array
 */
function _convertClassObjectToArray($object) {
  $json = json_encode($object);
  return (array)json_decode($json, true);
}


//////////////// 結果をブラウザで表示 ////////////////////

?>

<!DOCTYPE html>
<html>
  <head>
    <title>item.insert | ItemAPI</title>
    <meta charset="UTF-8">
    <style>
      pre,code {
        width:100%;
        overflow: auto;
        white-space: pre-wrap;
        word-wrap: break-word;
      }
    </style>
  </head>
  <body>
    <div style="width:100%;">
      <h1>リクエスト</h1>
      <pre>
        <?php echo htmlspecialchars($reqXml, ENT_QUOTES);; ?>
      </pre>
      <h1>レスポンス結果</h1>
      <h2>HTTP Status code</h2>
      <pre>
        <?php echo $httpStatusCode; ?>
      </pre>
      <h2>生レスポンス</h2>
      <pre>
        <?php 
          error_log(print_r($response, true));
          $xml = htmlspecialchars($response, ENT_QUOTES);
          echo '$response:' . $xml; ?>
      </pre>
      <?php 
        // レスポンスをxmlのオブジェクトにパースする
        if ($httpStatusCode == 200) {
          $responseBody = get_object_vars(simplexml_load_string($response));
        }
      ?>
      <h2>result.status</h2>
      <pre>
        <?php var_dump($responseBody['status']); ?>
      </pre>
      <h2>errorMessages</h2>
      <pre>
        <?php var_dump($responseBody['itemInsertResult']->errorMessages); ?>
      </pre>
      <h2>result.itemInsertResult</h2>
      <pre>
        <?php var_dump($responseBody['itemInsertResult']); ?>
      </pre>
    </div>
  </body>
</html>

