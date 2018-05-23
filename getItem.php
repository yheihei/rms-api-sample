<?php

require_once('config.php');
require_once('util.php');
require_once('class/item.php');

$item = new Item();
// $item->itemUrl = "7jfisbuy"; // ここを適宜変えると取ってくる商品を変えられる
$item->itemUrl = "rrrz_05";

list($httpStatusCode, $response) = getItem($item);

function getItem($item) {
  $responseBody = [];
  $authkey = base64_encode(RMS_SERVICE_SECRET . ':' . RMS_LICENSE_KEY);
  $header = array(
    "Content-Type: text/xml;charset=UTF-8",
    "Authorization: ESA {$authkey}",
  );

  // クエリストリングに取得したい商品のitemUrlを入れる
  $url = RMS_API_ITEM_GET . "?itemUrl=$item->itemUrl";
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
    <title>item.get | ItemAPI</title>
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
      <h2>errorMessages</h2>
      <code>
        <?php var_dump($responseBody['itemGetResult']->errorMessages); ?>
      </code>
      <h2>result.itemGetResult</h2>
      <code>
        <?php var_dump($responseBody['itemGetResult']); ?>
      </code>
    </div>
  </body>
</html>

