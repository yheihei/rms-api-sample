<?php

require_once('config.php');
require_once('util.php');

$conditions = array(
  'limit' => 10, // 一回に取得する件数
  'offset' => 1, // 取得するページ番号。2を指定するとlimit+1件目から2*limit件目を取ってくる
);

list($httpStatusCode, $response) = getFolders($conditions);

/*
* APIのリクエストを行う
* xmlを作って curlでgetしてる
* @param $conditions 取得したいフォルダ情報の条件
* @return リクエストしたxml文字列, httpステータスコード, レスポンス文字列(xmlで返ってくる)
*/
function getFolders($conditions) {
  $responseBody = [];
  $authkey = base64_encode(RMS_SERVICE_SECRET . ':' . RMS_LICENSE_KEY);
  $header = array(
    "Content-Type: text/xml;charset=UTF-8",
    "Authorization: ESA {$authkey}",
  );

  // クエリストリングにそれぞれのパラメーターセット
  $url = RMS_API_CABINET_FOLDERS_GET . "?";
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
    <title>cabinet.folders.get | CabinetAPI</title>
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
          $xml = htmlspecialchars($response, ENT_QUOTES);
          echo $xml; ?>
      </pre>
      <?php 
        // レスポンスをxmlのオブジェクトにパースする
        if ($httpStatusCode == 200) {
          $responseBody = get_object_vars(simplexml_load_string($response));
        }
      ?>
      <h2>result.status</h2>
      <code>
        <?php var_dump($responseBody['status']); ?>
      </code>
      <h2>result.cabinetFoldersGetResult</h2>
      <code>
        <?php var_dump($responseBody['cabinetFoldersGetResult']); ?>
      </code>
    </div>
  </body>
</html>

