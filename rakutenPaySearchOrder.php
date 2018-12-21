<?php

/**
 * 詳細解説記事はこちら
 * https://virusee.net/rakuten-pay-search-order/
 * */

require_once('config.php');
require_once('util.php');

$orderNumber = 1;
if($_GET[num]) {
  $orderNumber = $_GET[num];
}

$orderNumberList = array($orderNumber);

$dateType = 1; // 期間検索種別

// 期間指定
$endDate = new DateTime('now');
$endDate->setTimeZone( new DateTimeZone('UTC'));
$endDate->modify('+1 day'); // 現在時刻の次の日を終了時刻に
$startDate = clone $endDate;
$startDate->modify('-30 day'); // 30日前を開始に
$startDateTime = $startDate->format("Y-m-d\TH:i:s+0900");
$endDateTime = $endDate->format("Y-m-d\TH:i:s+0900");

// var_dump($startDateTime);
// var_dump($endDateTime);

// 取得したいオーダーステータス
$orderProgressList = [ 100, 200, 300, 400, 500, 600, 700, 800, 900 ];

// 1リクエストで何件要求するか (デフォルト30件しかないので注意)
$paginationRequestModel = [
    'requestRecordsAmount' => 1000,
    'requestPage' => 1,
  ];

list($httpStatusCode, $response, $requestJson, $jsonResponse) = searchOrder($dateType, $startDateTime, $endDateTime, $orderProgressList, $paginationRequestModel);

/***
 * RakutenPayOrderAPI searchOrder APIを使って、楽天ペイ注文の「注文情報の取得」を行うことができます。
 * 詳細解説記事はこちら
 * https://virusee.net/rakuten-pay-search-order/
 * */
function searchOrder($dateType, $startDateTime, $endDateTime, $orderProgressList, $paginationRequestModel) {
  $authkey = base64_encode(RMS_SERVICE_SECRET . ':' . RMS_LICENSE_KEY);
  $header = array(
    'Content-Type: application/json; charset=utf-8',
    "Authorization: ESA {$authkey}",
  );
  
  $requestJson = json_encode([
      'dateType' => $dateType,//期間検索種別
      'startDatetime' => $startDateTime,//検索対象期間先頭日時
      'endDatetime' => $endDateTime,//検索対象エンド点
      'orderProgressList'=> $orderProgressList,//取得したいオーダーステータス
      'PaginationRequestModel' => $paginationRequestModel // 取得したい件数など
  ]);

  $url = RMS_API_RAKUTEN_PAY_SEARCH_ORDER;
  
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
  
  $jsonResponse = $response;
  
  $response = json_decode( $response, true );
  
  curl_close($ch);
  return array($httpStatusCode, $response, $requestJson, $jsonResponse);
}

?>

<!DOCTYPE html>
<html>
  <head>
    <title>searchOrder | RakutenPayOrderAPI</title>
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
        <?php
          echo print_r($requestJson, true);
          ?>
      </pre>
      <h1>レスポンス結果</h1>
      <h2>HTTP Status code</h2>
      <pre>
        <?php echo $httpStatusCode; ?>
      </pre>
      <h2>生レスポンス</h2>
      <pre>
        <?php
          echo print_r($jsonResponse, true) . "\n\n";
          ?>
        <?php
          echo print_r($response, true);
          ?>
      </pre>
    </div>
  </body>
</html>

