<?php

require_once('config.php');
require_once('util.php');

$conditions = array(
  'categorySetManageNumber' => null, //メガショッププランかつカテゴリセットを登録済みの場合は必須
); // クエリを入力

list($httpStatusCode, $response) = getCategories($conditions);

/***
 * shop.categories.get APIを使って、登録しているカテゴリセットの一覧を取得することができます。
 * サンプルレスポンスは下記

<?xml version="1.0" encoding="UTF-8"?>
<result>
  <status>
    <interfaceId>shop.categories.get</interfaceId>
    <systemStatus>OK</systemStatus>
    <message>OK</message>
    <requestId>db323118-70a9-4134-97fb-72f51f152e6e</requestId>
    <requests>
      <categorySetManageNumber/>
    </requests>
  </status>
  <categoriesGetResult>
    <code>N000</code>
    <shopId/>
    <categoryList>
      <category>
        <categoryId>100</categoryId>
        <categoryLevel>1</categoryLevel>
        <name>CD、音楽ソフト</name>
        <status>0</status>
        <categoryWeight>1</categoryWeight>
      </category>
      <category>
        <categoryId>1</categoryId>
        <categoryLevel>1</categoryLevel>
        <name>その他</name>
        <status>0</status>
        <categoryWeight>999999999</categoryWeight>
      </category>
    </categoryList>
  </categoriesGetResult>
</result>

 * */
function getCategories($conditions) {
  $authkey = base64_encode(RMS_SERVICE_SECRET . ':' . RMS_LICENSE_KEY);
  $header = array(
    "Content-Type: text/xml;charset=UTF-8",
    "Authorization: ESA {$authkey}",
  );

  $url = RMS_API_CATEGORIES_GET . "?";
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
    <title>shop.categories.get | CategoryAPI</title>
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

