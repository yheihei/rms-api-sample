<?php

require_once('config.php');
require_once('util.php');

// 既存注文を取得して1円減額するサンプル

$orderNumber = '123456-20180101-00111801';
if($_GET[num]) {
  $orderNumber = $_GET[num];
}

// 指定の注文を取得する
$orderNumberList = array($orderNumber);
list($httpStatusCode, $getOrderResponse) = getOrder($orderNumberList);

$response = $getOrderResponse;

if ( count($getOrderResponse['OrderModelList']) ) {
  // 注文が取れた場合、1円減額して更新
  $reductionReason = 13;
  /***
   * 以下のいずれか
1:お客様都合による：キャンセル
2:お客様都合による：受取後の返品
3:お客様都合による：長期不在による受取拒否
4:お客様都合による：未入金
5:お客様都合による：代引決済の受取拒否
6:お客様都合による：その他
8:店舗都合による：欠品
10:店舗都合による：その他
13:店舗都合による：発送遅延
14:店舗都合による：顧客・配送対応注意表示
15:店舗都合による：返品（破損・品間違い）

・減額する場合は必須
   * */
  $taxRecalcFlag = 1;
  /***
   * 以下のいずれか
0: 消費税再計算を行わない
1: 消費税再計算を行う
   * */
  $orderModel = $getOrderResponse['OrderModelList'][0];
  $WrappingModel1 = $orderModel['WrappingModel1'];
  $WrappingModel2 = $orderModel['WrappingModel2'];
  $CouponModelList = $orderModel['CouponModelList'];
  $PackageModelList = $orderModel['PackageModelList'];
  foreach( $PackageModelList as &$packageModel ) {
    foreach( $packageModel['ItemModelList'] as &$itemModel ) {
      // 商品価格を1円減額
      $itemModel['price'] = (int) $itemModel['price'] - 1;
    }
    unset($itemModel);
  }
  unset($packageModel);
  
  list($httpStatusCode, $response, $requestJson) = 
    updateOrderSender(
      $orderNumber, 
      $reductionReason, 
      $taxRecalcFlag,
      $WrappingModel1,
      $WrappingModel2,
      $PackageModelList,
      $CouponModelList
      );
}

/***
 * RakutenPayOrderAPI updateOrderSender APIを使って、楽天ペイ注文の「送付者情報と注文商品情報の更新」を行うことができます。
 * 対象項目は以下となります。
・商品名
・商品番号
・項目・選択肢
・単価
・税（込・別）
・送料（無料・別）
・ラッピング情報更新、削除（包装紙・リボン、ラッピング名、単価、税（込・別））
・送付先 - 名前 
・送付先 - フリガナ
・送付先 - 住所
・送付先 - 電話番号
・送付先 - のし
・送付先 - 数量
・送付先 - 在庫連動（商品設定に合わせる・在庫連動する・在庫連動しない）
・送付先 - 外税
・送付先 - 送料
 * 
 * 
 * @param string $orderNumber
 * @param int $reductionReason
 * @param int $taxRecalcFlag,
 * @param WrappingModel $WrappingModel1
 * @param WrappingModel $WrappingModel2
 * @param PackageModel[] $PackageModelList
 * @param CouponModel[] $CouponModelList
 * 
 * @return array
 * 
 * */
function updateOrderSender(
    $orderNumber, 
    $reductionReason, 
    $taxRecalcFlag,
    $WrappingModel1,
    $WrappingModel2,
    $PackageModelList,
    $CouponModelList
    ) {
  $authkey = base64_encode(RMS_SERVICE_SECRET . ':' . RMS_LICENSE_KEY);
  $header = array(
    'Content-Type: application/json; charset=utf-8',
    "Authorization: ESA {$authkey}",
  );
  
  $requestJson = json_encode([
      'orderNumber' => $orderNumber,
      'reductionReason' => $reductionReason,
      'taxRecalcFlag' => $taxRecalcFlag,
      'WrappingModel1' => $WrappingModel1,
      'WrappingModel2' => $WrappingModel2,
      'PackageModelList' => $PackageModelList,
      'CouponModelList' => $CouponModelList,
  ], JSON_UNESCAPED_UNICODE); // 日本語文字列を含む場合ユニコードにするとエラーとなるため、ユニコードにしないオプション

  $url = RMS_API_RAKUTEN_PAY_UPDATE_ORDER_SENDER;
  
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
  return array($httpStatusCode, $response, $requestJson);
}

// 注文取得
function getOrder($orderNumberList) {
  $authkey = base64_encode(RMS_SERVICE_SECRET . ':' . RMS_LICENSE_KEY);
  $header = array(
    'Content-Type: application/json; charset=utf-8',
    "Authorization: ESA {$authkey}",
  );
  
  $requestJson = json_encode([
      'orderNumberList' => $orderNumberList
  ]);

  $url = RMS_API_RAKUTEN_PAY_GET_ORDER;
  
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
    <title>updateOrderSender | RakutenPayOrderAPI</title>
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
      <h1>変更リクエスト</h1>
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
          echo print_r($response, true);
          ?>
      </pre>
    </div>
  </body>
</html>

