<?php

require_once('config.php');
require_once('util.php');
require_once('class/userAuthModel.php');
require_once('class/updateOrderRequestModel.php');
require_once('class/orderModel.php');
require_once('class/personModel.php');
require_once('class/settlementModel.php');
require_once('class/cardModel.php');
require_once('class/deliveryModel.php');
require_once('class/packageModel.php');
require_once('class/itemModel.php');

ini_set('xdebug.var_display_max_children', -1);
ini_set('xdebug.var_display_max_data', -1);
ini_set('xdebug.var_display_max_depth', -1);

/***
 * リクエストIDを取得 getRequestId [同期]
 * */

// 楽天へRMS APIを使って送信
list($request, $httpStatusCode, $response) = getOrderRequestId();
// customVarDump($request);
// customVarDump($httpStatusCode);
// customVarDump($response);

if (strcmp($httpStatusCode, "200") != 0) {
  // statuscode 200じゃない場合、リクエストIDの取得に失敗。処理中止
  return;
}

if(strcmp($response->return->errorCode, "N00-000") != 0 ) {
  // 決済APIの結果が正常でない場合、リクエストIDの取得に失敗。処理中止
  return;
}

//リクエストIDの取得完了
$requestId = $response->return->requestId;

/***
 * 受注情報の変更 updateOrder　[非同期]
 * $requestIdはシステム内に保存しておくこと。
 * 非同期のため、処理が正しく行われたかどうかはgetResult APIに$requestIdを入れて確認する必要がある
 * 
 * @note
 * 愚直にクラスを作って一つ一つ入れていきましたが、
 * 必須項目が50項目以上ありチマチマ入れるのは得策ではありません。
 * getOrderで得られるxmlの<orderModel>配下をパースしてarrayにし、
 * それを$updateOrderRequestModel->orderModelのarrayに挿入して、
 * 本ファイルのupdateOrder関数にぶち込むことをオススメします
 * 
 * POST例は下記。本例ではSOAPクライアントを使っているが、自前でやりたい場合は下記のxmlを組み立ててPOSTすること
 
 <?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://orderapi.rms.rakuten.co.jp/rms/mall/order/api/ws">
  <SOAP-ENV:Body>
    <ns1:updateOrder>
      <arg0>
        <authKey>ESA hogekey</authKey>
        <shopUrl>hogeshop</shopUrl>
        <userName>hogename</userName>
      </arg0>
      <arg1>
        <orderModel>
          <deliveryModel>
            <deliveryName>宅配便</deliveryName>
          </deliveryModel>
          <orderDate>2018-05-31T13:47:04+09:00</orderDate>
          <orderNumber>338459-20180531-00000726</orderNumber>
          <ordererModel>
            <city>世田谷区 玉川一丁目14番1号</city>
            <emailAddress>hogemail</emailAddress>
            <familyName>楽天</familyName>
            <phoneNumber1>03</phoneNumber1>
            <phoneNumber2>1234</phoneNumber2>
            <phoneNumber3>5678</phoneNumber3>
            <prefecture>東京都</prefecture>
            <subAddress>※※ この注文はテスト注文です。誠に申し訳ございませんがキャンセル処理をお願いします。</subAddress>
            <zipCode1>158</zipCode1>
            <zipCode2>0094</zipCode2>
          </ordererModel>
          <packageModel>
            <basketId>1647839462</basketId>
            <itemModel>
              <basketId>1646425796</basketId>
              <isIncludedCashOnDeliveryPostage>false</isIncludedCashOnDeliveryPostage>
              <isIncludedPostage>false</isIncludedPostage>
              <isIncludedTax>true</isIncludedTax>
              <itemName>テスト商品につき購入不可_testrrrz_0ia_20180524100531</itemName>
              <itemNumber>testrrrz_0ia_20180524100531</itemNumber>
              <price>100</price>
              <units>1</units>
            </itemModel>
            <senderModel>
              <city>世田谷区 玉川一丁目14番1号</city>
              <emailAddress>hogemail</emailAddress>
              <familyName>楽天</familyName>
              <phoneNumber1>03</phoneNumber1>
              <phoneNumber2>1234</phoneNumber2>
              <phoneNumber3>5678</phoneNumber3>
              <prefecture>東京都</prefecture>
              <subAddress>※※ この注文はテスト注文です。誠に申し訳ございませんがキャンセル処理をお願いします。</subAddress>
              <zipCode1>158</zipCode1>
              <zipCode2>0094</zipCode2>
            </senderModel>
          </packageModel>
          <seqId>0</seqId>
          <settlementModel>
            <cardModel>
              <brandName>VISA</brandName>
              <cardNo>XXXX-XXXX-XXXX-6941</cardNo>
              <expYM>2020/03</expYM>
              <ownerName>TARO RAKUTEN</ownerName>
              <payType>0</payType>
            </cardModel>
            <settlementName>クレジットカード</settlementName>
          </settlementModel>
          <status>発送前入金待ち</status>
        </orderModel>
        <requestId>584618680</requestId>
      </arg1>
    </ns1:updateOrder>
  </SOAP-ENV:Body>
</SOAP-ENV:Envelope>
  
 * */
$updateOrderRequestModel = new UpdateOrderRequestModel();
$updateOrderRequestModel->requestId = $requestId;

// 更新したい注文情報を設定
$orderModel = new OrderModel();
$orderModel->orderNumber = "338459-20180531-00000726";
$orderModel->status = "発送前入金待ち";
$orderModel->seqId = 0; // getOrderで取得してきた値を入れる
$orderModel->orderDate = "2018-05-31T13:47:04+09:00"; // getOrderで取得してきた値を入れる

// 注文者情報モデルを設定(更新如何に関わらず必須項目)
$ordererModel = new PersonModel();
$ordererModel->zipCode1 = "158";
$ordererModel->zipCode2 = "0094";
$ordererModel->prefecture = "東京都";
$ordererModel->city = "世田谷区 玉川一丁目14番1号";
$ordererModel->subAddress = "※※ この注文はテスト注文です。誠に申し訳ございませんがキャンセル処理をお願いします。";
$ordererModel->familyName = "楽天";
$ordererModel->phoneNumber1 = "03";
$ordererModel->phoneNumber2 = "1234";
$ordererModel->phoneNumber3 = "5678";
$ordererModel->emailAddress = RMS_TEST_MAIL_ADDRESS;
$orderModel->ordererModel = $ordererModel;

// 配送方法モデルを設定(更新如何に関わらず通常購入、予約商品、定期購入、頒布会、共同購入の場合に指定が必要)
$deliveryModel = new DeliveryModel();
$deliveryModel->deliveryName = "宅配便";
$orderModel->deliveryModel = $deliveryModel;

//支払方法モデルを設定(更新如何に関わらず必須項目)
$settlementModel = new SettlementModel();
$settlementModel->settlementName = "クレジットカード";
// クレジットカードモデルを設定(カード利用注文の場合には指定が必要。)
$cardModel = new CardModel();
$cardModel->brandName = "VISA";
$cardModel->cardNo = "XXXX-XXXX-XXXX-6941";
$cardModel->ownerName = "TARO RAKUTEN";
$cardModel->expYM = "2020/03";
$cardModel->payType = 0;
$settlementModel->cardModel = $cardModel;
$orderModel->settlementModel = $settlementModel;

// 送付先モデルを設定(更新如何に関わらず必須項目)
$packageModel = new PackageModel();
$packageModel->basketId = 1647839462; // 通常注文、オークション注文の場合、指定が必要。
// 送付者情報モデルを設定(更新如何に関わらず必須項目)
$senderModel = new PersonModel();
$senderModel->zipCode1 = "158";
$senderModel->zipCode2 = "0094";
$senderModel->prefecture = "東京都";
$senderModel->city = "世田谷区 玉川一丁目14番1号";
$senderModel->subAddress = "※※ この注文はテスト注文です。誠に申し訳ございませんがキャンセル処理をお願いします。";
$senderModel->familyName = "楽天";
$senderModel->phoneNumber1 = "03";
$senderModel->phoneNumber2 = "1234";
$senderModel->phoneNumber3 = "5678";
$senderModel->emailAddress = RMS_TEST_MAIL_ADDRESS;
$packageModel->senderModel = $senderModel;
// 商品モデルを設定(更新如何に関わらず必須項目)
$itemModel = new ItemModel();
$itemModel->basketId = 1646425796; // 通常注文、オークション注文の場合、指定が必要。
// $itemModel->itemId = 10000044; // 商品情報(値段等)を変更する場合、もしくは商品を削除する場合には指定が必要。
$itemModel->itemName = "テスト商品につき購入不可_testrrrz_0ia_20180524100531";
$itemModel->itemNumber = "testrrrz_0ia_20180524100531";
$itemModel->price = 100;
$itemModel->units = 1;
$itemModel->isIncludedPostage = 0;
$itemModel->isIncludedTax = 1;
$itemModel->isIncludedCashOnDeliveryPostage = 0;
// $itemModel->restoreInventoryFlag = hoge; // 個数が変更となる場合に指定が必要
$packageModel->itemModel[] = $itemModel;
$orderModel->packageModel = $packageModel;



// 更新したい注文情報をリクエストに設定
$updateOrderRequestModel->orderModel[] = $orderModel;


// 楽天へRMS APIを使って送信
list($request, $httpStatusCode, $response) = updateOrder($updateOrderRequestModel);

//////////////// 関数群 ////////////////////

/***
* 非同期APIを使用するためのrequestIdを取得するAPIのリクエストを行う
* Soap clientを使ってPOSTしている
* @return リクエストしたxml文字列, httpステータスコード, レスポンスオブジェクト

POST例は下記。本例ではSOAPクライアントを使っているが、自前でやりたい場合は下記のxmlを組み立ててPOSTすること

<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="java:jp.co.rakuten.rms.mall.rccsapi.model.entity" xmlns:ns2="http://orderapi.rms.rakuten.co.jp/rccsapi-services/RCCSAPI">
  <SOAP-ENV:Body>
    <ns2:getRCCSRequestId>
      <ns2:userAuthModel>
        <ns1:authKey>hogehoge</ns1:authKey>
        <ns1:shopUrl>hogehogeshop</ns1:shopUrl>
        <ns1:userName>hogehogeuser</ns1:userName>
      </ns2:userAuthModel>
    </ns2:getRCCSRequestId>
  </SOAP-ENV:Body>
</SOAP-ENV:Envelope>
*/
function updateOrder($updateOrderRequestModel) {

  //パラメータセット
  $userAuthModel = new UserAuthModel();
  $userAuthModel->authKey = "ESA " . base64_encode(RMS_SERVICE_SECRET . ':' . RMS_LICENSE_KEY);
  $userAuthModel->shopUrl = RMS_SETTLEMENT_SHOP_URL;
  $userAuthModel->userName  = RMS_SETTLEMENT_USER_NAME;
  $params = array('arg0' => _convertClassObjectToArray($userAuthModel),
    'arg1' => _convertClassObjectToArray($updateOrderRequestModel));
  // customVarDump($params);
  
  //エラーを無視するエラーハンドラに切り替える（実行後は元に戻す）
  set_error_handler("myErrorHandler");
  try{
      $client = new SoapClient(RMS_API_ORDER_SOAP_WSDL, array('trace' => 1 ));
      //エラーハンドラ回復
      restore_error_handler();
  } catch (Exception $e) {
      //エラーハンドラ回復
      restore_error_handler();
      return array(null, "400", $e->getMessage()); // 適切に対処した方が良い
      //WSDLファイルの読み込みに失敗した場合の処理
  }
  
  try{
    //SOAP通信実行
    $result = $client->updateOrder($params);
    // customVarDump($result);
  } catch (SoapFault $e) {
    // customVarDump($e);
    //SOAP通信の実行に失敗した場合の処理
  }
  // customVarDump($client->__getLastRequest());
  // customVarDump($client->__getLastResponse());
  
  return array($client->__getLastRequest(), extract_response_http_code($client->__getLastResponseHeaders()), $result);
}

/***
* 非同期APIを使用するためのrequestIdを取得するAPIのリクエストを行う
* Soap clientを使ってPOSTしている
* @return リクエストしたxml文字列, httpステータスコード, レスポンスオブジェクト

POST例は下記。本例ではSOAPクライアントを使っているが、自前でやりたい場合は下記のxmlを組み立ててPOSTすること

<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://orderapi.rms.rakuten.co.jp/rms/mall/order/api/ws">
  <SOAP-ENV:Body>
    <ns1:getRequestId>
      <arg0>
        <authKey>ESA hogekey</authKey>
        <shopUrl>hogeshop</shopUrl>
        <userName>hogename</userName>
      </arg0>
    </ns1:getRequestId>
  </SOAP-ENV:Body>
</SOAP-ENV:Envelope>
*/
function getOrderRequestId() {

  //パラメータセット
  $userAuthModel = new UserAuthModel();
  $userAuthModel->authKey = "ESA " . base64_encode(RMS_SERVICE_SECRET . ':' . RMS_LICENSE_KEY);
  $userAuthModel->shopUrl = RMS_SETTLEMENT_SHOP_URL;
  $userAuthModel->userName  = RMS_SETTLEMENT_USER_NAME;
  $params = array('arg0' => _convertClassObjectToArray($userAuthModel));
  // customVarDump($params);
  
  //エラーを無視するエラーハンドラに切り替える（実行後は元に戻す）
  set_error_handler("myErrorHandler");
  try{
      $client = new SoapClient(RMS_API_ORDER_SOAP_WSDL, array('trace' => 1 ));
      //エラーハンドラ回復
      restore_error_handler();
  } catch (Exception $e) {
      //エラーハンドラ回復
      restore_error_handler();
      return array(null, "400", $e->getMessage()); // 適切に対処した方が良い
      //WSDLファイルの読み込みに失敗した場合の処理
  }
  
  try{
    //SOAP通信実行
    $result = $client->getRequestId($params);
    // customVarDump($result);
  } catch (SoapFault $e) {
    // customVarDump($e);
    //SOAP通信の実行に失敗した場合の処理
  }
  // customVarDump($client->__getLastRequest());
  // customVarDump($client->__getLastResponse());
  
  return array($client->__getLastRequest(), extract_response_http_code($client->__getLastResponseHeaders()), $result);
}

// エラーハンドラ関数
function myErrorHandler($errno, $errstr, $errfile, $errline)
{
  
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
    <title>updateOrder | OrderAPI</title>
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
        <?php echo htmlspecialchars(returnFormattedXmlString($request), ENT_QUOTES);; ?>
      </pre>
      <h1>レスポンス結果</h1>
      <h2>HTTP Status code</h2>
      <pre>
        <?php customVarDump($httpStatusCode); ?>
      </pre>
      <h2>生レスポンス</h2>
      <pre>
        <?php 
          echo customVarDump($response);
          ?>
      </pre>
    </div>
  </body>
</html>

