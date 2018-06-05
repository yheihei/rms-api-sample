<?php

require_once('config.php');
require_once('util.php');

$conditions = array(
  'fileId' => null,
  'filePath' => 'test_image.jpg',
  'fileName' => null,
  'folderId' => 0,
  'folderPath' => null,
  'offset' => null,
  'limit' => null,
); // 検索条件を入力

list($httpStatusCode, $response) = searchFiles($conditions);

/***
 * cabinet.files.search APIを使って、ファイルの検索ができる。
 * サンプルレスポンスは下記

<?xml version="1.0" encoding="UTF-8"?>
<result>
  <status>
    <interfaceId>cabinet.files.search</interfaceId>
    <systemStatus>OK</systemStatus>
    <message>OK</message>
    <requestId>ab07a25f-b70f-4fef-a847-73d31fedd73a</requestId>
    <requests>
      <folderId>0</folderId>
      <fileName/>
      <offset/>
      <limit/>
    </requests>
  </status>
  <cabinetFilesSearchResult>
    <resultCode>0</resultCode>
    <fileAllCount>1</fileAllCount>
    <fileCount>1</fileCount>
    <files>
      <file>
        <FolderId>0</FolderId>
        <FolderName>基本フォルダ</FolderName>
        <FolderNode>0</FolderNode>
        <FolderPath>/</FolderPath>
        <FileId>87570458</FileId>
        <FileName>test_ds4_20180605121011</FileName>
        <FileUrl>https://image.rakuten.co.jp/_shop_4485/cabinet/test_image.jpg</FileUrl>
        <FilePath>test_image.jpg</FilePath>
        <FileType>1</FileType>
        <FileSize>150.271</FileSize>
        <FileWidth>1600</FileWidth>
        <FileHeight>1200</FileHeight>
        <FileAccessDate>2018-06-05</FileAccessDate>
        <TimeStamp>2018-06-05 12:10:12</TimeStamp>
      </file>
    </files>
  </cabinetFilesSearchResult>
</result>
 * */
function searchFiles($conditions) {
  $responseBody = [];
  $authkey = base64_encode(RMS_SERVICE_SECRET . ':' . RMS_LICENSE_KEY);
  $header = array(
    "Content-Type: text/xml;charset=UTF-8",
    "Authorization: ESA {$authkey}",
  );

  // クエリストリングにそれぞれのパラメーターセット
  $url = RMS_API_CABINET_FILES_SEARCH . "?";
  foreach($conditions as $key => $value) {
    if(!is_null($value)) {
      // 条件が設定されていればクエリに追加
      $url .= "{$key}={$value}" . "&";
    }
  }
  $url = substr($url, 0, -1); // 末尾の「&」を削除
  customVarDump($url);
  
  $ch = curl_init($url);

  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
  curl_setopt($ch, CURLOPT_TIMEOUT,        30);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //返り値を 文字列で返します
  
  $response = curl_exec($ch);
  if(curl_error($ch)){
    $response = curl_error($ch);
  }
  
  $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  
  curl_close($ch);
  return array($httpStatusCode, $response);
}

?>

<!DOCTYPE html>
<html>
  <head>
    <title>cabinet.files.search | CabinetAPI</title>
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
      <h1>レスポンス結果</h1>
      <h2>HTTP Status code</h2>
      <pre>
        <?php echo $httpStatusCode; ?>
      </pre>
      <h2>生レスポンス</h2>
      <pre>
        <?php 
          error_log(print_r($response, true));
          echo htmlspecialchars(returnFormattedXmlString($response) , ENT_QUOTES);  ?>
      </pre>
    </div>
  </body>
</html>

