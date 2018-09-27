<?php

require_once('config.php');
require_once('util.php');

$orderNumber = '123456-20180101-00111801';
if($_GET[num]) {
  $orderNumber = $_GET[num];
}

/***
 * 在庫連動区分
0: 商品設定に合わせる
1: 在庫連動する
2: 在庫連動しない
 * 
 * 
 * */
$inventoryRestoreType = 0;
 
/***
 * キャンセル理由
(お客様都合による)
1: キャンセル
2: 受取後の返品
3: 長期不在による受取拒否
4: 未入金
5: 代引決済の受取拒否
6: その他

(店舗都合による)
8: 欠品
10: その他
13: 発送遅延
14: 顧客・配送対応注意表示
15: 返品（破損・品間違い）
 * */
$changeReasonDetailApply = 8;


list($httpStatusCode, $response) = cancelOrder($orderNumber, $inventoryRestoreType, $changeReasonDetailApply);

/***
 * RakutenPayOrderAPI cancelOrder APIを使って、楽天ペイ注文のキャンセルを行うことができます。
 * 
 * @param string $orderNumber
 * @param int $inventoryRestoreType
 * @param int $changeReasonDetailApply
 * 
 * @return array
キャンセルできるのは次のステータスのみ
・100: 注文確認待ち
・200: 楽天処理中
・300: 発送待ち
 * */
function cancelOrder($orderNumber, $inventoryRestoreType, $changeReasonDetailApply) {
  $authkey = base64_encode(RMS_SERVICE_SECRET . ':' . RMS_LICENSE_KEY);
  $header = array(
    'Content-Type: application/json; charset=utf-8',
    "Authorization: ESA {$authkey}",
  );
  
  $requestJson = json_encode([
      'orderNumber' => $orderNumber,
      'inventoryRestoreType' => $inventoryRestoreType,
      'changeReasonDetailApply' => $changeReasonDetailApply,
  ]);

  $url = RMS_API_RAKUTEN_PAY_CANCEL_ORDER;
  
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
    <title>getPayment | RakutenPayOrderAPI</title>
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

