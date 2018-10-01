<?php

require_once('config.php');
require_once('util.php');

// 既存注文を取得して発送情報の追加・更新を行う
// 出荷した荷物の送り状などの情報を更新する

$orderNumber = '123456-20180101-00111801';
if($_GET[num]) {
  $orderNumber = $_GET[num];
}

// 指定の注文を取得する
$orderNumberList = array($orderNumber);
list($httpStatusCode, $getOrderResponse) = getOrder($orderNumberList);

$response = $getOrderResponse;

$basketId = 0;
if ( count($getOrderResponse['OrderModelList']) ) {
  // 必須項目のbasketIdを注文情報から取得する
  // 簡略化のため配送先は一つのみの場合を想定する
  // 配送先が複数ある場合PackageModelListが複数になってくるため注意する事
  // 配送先複数選択を許容するか否かは店舗側で設定できる。設定場所は下記。
  // RMS管理画面　＞　拡張サービス一覧　＞　1オプション機能利用申込・解約　＞　注文時送付先設定
  $orderModel = $getOrderResponse['OrderModelList'][0];
  $PackageModelList = $orderModel['PackageModelList'][0];
  $basketId = $PackageModelList['basketId'];
  
  // 完了報告を行うための、発送明細ID shippingDetailIdを注文情報から取得する
  // まだ注文情報をRMSに報告していない場合、ここはemptyとなるため無視する
  $shippingDetailId = 0;
  if ( isset($PackageModelList['ShippingModelList']) && !empty($PackageModelList['ShippingModelList']) ) {
    $shippingDetailId = $PackageModelList['ShippingModelList'][0]['shippingDetailId'];
  }
  
  // 送付先モデルを作る
  $BasketidModel = array();
  // 送付先IDを指定
  $BasketidModel['basketId'] = $basketId;
  
  // 発送モデルを作る
  $ShippingModel = array();
  
  /***
   * 発送明細ID
   * 未指定時は追加。指定時は更新・削除
   * */
  if($shippingDetailId) {
    $ShippingModel['shippingDetailId'] = $shippingDetailId;
  }
  
  /**
   * 配送会社を選択
      1000: その他
      1001: ヤマト運輸
      1002: 佐川急便
      1003: 日本郵便
      1004: 西濃運輸
      1005: 西部運輸
      1006: 福山通運
      1007: 名鉄運輸
      1008: トナミ運輸
      1009: 第一貨物
      1010: 新潟運輸
      1011: 中越運送
      1012: 岡山県貨物運送
      1013: 久留米運送
      1014: 山陽自動車運送
      1015: 日本トラック
      1016: エコ配
      1017: EMS
      1018: DHL
      1019: FedEx
      1020: UPS
      1021: 日本通運
      1022: TNT
      1023: OCS
      1024: USPS
      1025: SFエクスプレス
      1026: Aramex
      1027: SGHグローバル・ジャパン
   * */
  $ShippingModel['deliveryCompany'] = 1001;
  
  /***
   * お荷物伝票番号
   * 以下の入力チェックが適用されます。
   * ・機種依存文字などの不正文字以外
   * ・全角、半角にかかわらず120文字以下
   * */
  $ShippingModel['shippingNumber'] = '123456789';
  
  /***
   * 発送日
   * YYYY-MM-DD
   * */
  $nowDateTime = new DateTime('now', new DateTimeZone('Asia/Tokyo'));
  $ShippingModel['shippingDate'] = $nowDateTime->format('Y-m-d');
  
  /***
   * 発送情報削除フラグ
   * 0: 発送情報を削除しない
   * 1: 発送情報を削除する
   * */
  // $ShippingModel['shippingDeleteFlag'] = 1;
  
  // 発送モデルリストを作る
  $ShippingModelList = array();
  $ShippingModelList[] = $ShippingModel;
  
  // 送付先モデルに発送モデルリストを入れる
  $BasketidModel['ShippingModelList'] = $ShippingModelList;
  
  // 送付先モデルリストを作る
  $BasketidModelList = array();
  $BasketidModelList[] = $BasketidModel;
  
  list($httpStatusCode, $response, $requestJson) = 
    updateOrderShipping(
      $orderNumber, 
      $BasketidModelList
      );
}

/***
 * RakutenPayOrderAPI updateOrderShipping APIを使って、楽天ペイ注文の「送付者情報と注文商品情報の更新」を行うことができます。
 * @param string $orderNumber
 * @param array $BasketidModelList
 * 
 * @return array
 * 
 * */
function updateOrderShipping(
    $orderNumber, 
    $BasketidModelList
    ) {
  $authkey = base64_encode(RMS_SERVICE_SECRET . ':' . RMS_LICENSE_KEY);
  $header = array(
    'Content-Type: application/json; charset=utf-8',
    "Authorization: ESA {$authkey}",
  );
  
  $requestJson = json_encode([
      'orderNumber' => $orderNumber,
      'BasketidModelList' => $BasketidModelList,
  ], JSON_UNESCAPED_UNICODE); // 日本語文字列を含む場合ユニコードにするとエラーとなるため、ユニコードにしないオプション

  $url = RMS_API_RAKUTEN_PAY_UPDATE_ORDER_SHIPPING;
  
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
    <title>updateOrderShipping | RakutenPayOrderAPI</title>
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

