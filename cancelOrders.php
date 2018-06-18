<?php

require_once('config.php');
require_once('util.php');
require_once('class/userAuthModel.php');
require_once('class/cancelOrderRequestModel.php');
require_once('class/cancelModel.php');

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
 * 受注のキャンセル
 * $requestIdはシステム内に保存しておくこと。
 * */
$cancelOrderRequestModel = new CancelOrderRequestModel();
$cancelOrderRequestModel->requestId = $requestId;

// キャンセルしたい注文情報を設定
$cancelModel = new CancelModel();
$cancelModel->orderNumber = "338459-20180615-00000818";
$cancelModel->reasonId = 1; // 1	キャンセル	お客様都合
$cancelModel->restoreInventoryFlag = 0; // 0 商品の設定に従う

// 更新したい注文情報をリクエストに設定
$cancelOrderRequestModel->cancelModelList[] = $cancelModel;


// 楽天へRMS APIを使って送信
list($request, $httpStatusCode, $response) = cancelOrder($cancelOrderRequestModel);

//////////////// 関数群 ////////////////////

/***
 * 受注のキャンセル cancelOrder　[非同期]
 * $requestIdはシステム内に保存しておくこと。
 * 非同期のため、処理が正しく行われたかどうかはgetResult APIに$requestIdを入れて確認する必要がある
 * 
 * POST例は下記。本例ではSOAPクライアントを使っているが、自前でやりたい場合は下記のxmlを組み立ててPOSTすること
 
<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://orderapi.rms.rakuten.co.jp/rms/mall/order/api/ws">
  <SOAP-ENV:Body>
    <ns1:cancelOrder>
      <arg0>
        <authKey>ESA hogekey</authKey>
        <shopUrl>hogeshop</shopUrl>
        <userName>hogeuser</userName>
      </arg0>
      <arg1>
        <cancelModelList>
          <orderNumber>338459-20180615-00000822</orderNumber>
          <reasonId>1</reasonId>
          <restoreInventoryFlag>0</restoreInventoryFlag>
        </cancelModelList>
        <requestId>607055705</requestId>
      </arg1>
    </ns1:cancelOrder>
  </SOAP-ENV:Body>
</SOAP-ENV:Envelope>
  
 * */
function cancelOrder($cancelOrderRequestModel) {

  //パラメータセット
  $userAuthModel = new UserAuthModel();
  $userAuthModel->authKey = "ESA " . base64_encode(RMS_SERVICE_SECRET . ':' . RMS_LICENSE_KEY);
  $userAuthModel->shopUrl = RMS_SETTLEMENT_SHOP_URL;
  $userAuthModel->userName  = RMS_SETTLEMENT_USER_NAME;
  $params = array('arg0' => _convertClassObjectToArray($userAuthModel),
    'arg1' => _convertClassObjectToArray($cancelOrderRequestModel));
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
    $result = $client->cancelOrder($params);
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
    <title>cancelOrder | OrderAPI</title>
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
        <?php echo htmlspecialchars(returnFormattedXmlString($request), ENT_QUOTES); ?>
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

