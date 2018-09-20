<?php

require_once('config.php');
require_once('util.php');

$orderNumber = '123456-20180101-00111801';
if($_GET[num]) {
  $orderNumber = $_GET[num];
}

$deliveryName = '宅配便';

if($_GET[deliveryName]) {
  $deliveryName = $_GET[deliveryName];
}

list($httpStatusCode, $response) = updateOrderDelivery($orderNumber, $deliveryName);

/***
 * RakutenPayOrderAPI updateOrderDelivery APIを使って、楽天ペイ注文の「配送方法の更新」を行うことができます。
 * サンプルレスポンスは下記


 * */
function updateOrderDelivery($orderNumber, $deliveryName) {
  $authkey = base64_encode(RMS_SERVICE_SECRET . ':' . RMS_LICENSE_KEY);
  $header = array(
    'Content-Type: application/json; charset=utf-8',
    "Authorization: ESA {$authkey}",
  );
  
  $requestJson = json_encode([
      'orderNumber' => $orderNumber,
      'deliveryName' => $deliveryName,
  ], JSON_UNESCAPED_UNICODE); // 日本語文字列を含む場合ユニコードにするとエラーとなるため、ユニコードにしないオプション

  $url = RMS_API_RAKUTEN_PAY_UPDATE_ORDER_DELIVERY;
  
  $ch = curl_init($url);

  curl_setopt($ch, CURLOPT_POSTFIELDS,     $requestJson);
  curl_setopt($ch, CURLOPT_POST,           true);
  curl_setopt($ch, CURLOPT_TIMEOUT,        30);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //返り値を 文字列で返します
  
  $response = curl_exec($ch);
  if(curl_error($ch)){
    $response = curl_error($ch);
  }
  
  $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  
  $response = json_decode( $response, true );
  
  curl_close($ch);
  return array($httpStatusCode, $response);
}

?>

<!DOCTYPE html>
<html>
  <head>
    <title>updateOrderDelivery | RakutenPayOrderAPI</title>
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
          echo print_r($response, true);
          ?>
      </pre>
    </div>
  </body>
</html>

