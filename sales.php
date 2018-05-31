<?php

require_once('config.php');
require_once('util.php');
require_once('class/userAuthModel.php');
require_once('class/uiSalesRequestModel.php');

ini_set('xdebug.var_display_max_children', -1);
ini_set('xdebug.var_display_max_data', -1);
ini_set('xdebug.var_display_max_depth', -1);

// 売上請求したい受注番号を入力
$orderNumber = "338459-20180530-00000825";

/***
 * リクエストIDを取得 getRCCSRequestId [同期]
 * */

// 楽天へRMS APIを使って送信
list($request, $httpStatusCode, $response) = getRCCSRequestId();

if (strcmp($httpStatusCode, "200") != 0) {
  // statuscode 200じゃない場合、リクエストIDの取得に失敗。処理中止
  return;
}

if(strcmp($response->result->errorCode, "RCCS_N00-000") != 0 ) {
  // 決済APIの結果が正常でない場合、リクエストIDの取得に失敗。処理中止
  return;
}

// リクエストIDの取得完了
$requestId = $response->result->requestId;

/***
 * 売上請求処理 sales [非同期]
 * $requestIdはシステム内に保存しておくこと。
 * 非同期のため、処理が正しく行われたかどうかはgetRCCSResult APIに$requestIdを入れて確認する必要がある
 * */
$uiSalesRequestModel = new UiSalesRequestModel();
$uiSalesRequestModel->orderNumber = $orderNumber;
$uiSalesRequestModels[] = $uiSalesRequestModel;

// 楽天へRMS APIを使って送信
list($request, $httpStatusCode, $response) = sales($requestId, $uiSalesRequestModels);

//////////////// 関数群 ////////////////////

/*
* sales APIのリクエストを行う。オーソリ済の受注を元に売上請求
* SOAP Clientを使ってパラメータをセットしPOSTしている
* @param セットしたい受注の決済のクラスオブジェクト
* @return リクエストしたxml文字列, httpステータスコード, レスポンスオブジェクト

POST例は下記。本例ではSOAPクライアントを使っているが、自前でやりたい場合は下記のxmlを組み立ててPOSTすること

<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="java:jp.co.rakuten.rms.mall.rccsapi.model.entity" xmlns:ns2="http://orderapi.rms.rakuten.co.jp/rccsapi-services/RCCSAPI" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
  <SOAP-ENV:Body>
    <ns2:sales>
      <ns2:userAuthModel>
        <ns1:authKey>hogekey</ns1:authKey>
        <ns1:shopUrl>hogeshop</ns1:shopUrl>
        <ns1:userName>hogeuser</ns1:userName>
      </ns2:userAuthModel>
      <ns2:intVal>167159519</ns2:intVal>
      <ns2:uiSalesRequestModels>
        <ns1:UiSalesRequestModel>
          <ns1:helpItem xsi:nil="true" />
          <ns1:orderNumber>338459-20180530-00000825</ns1:orderNumber>
        </ns1:UiSalesRequestModel>
      </ns2:uiSalesRequestModels>
    </ns2:sales>
  </SOAP-ENV:Body>
</SOAP-ENV:Envelope>

*/
function sales($requestId, $uiSalesRequestModels) {

  //パラメータセット
  $userAuthModel = new UserAuthModel();
  $userAuthModel->authKey = RMS_SETTLEMENT_AUTH;
  $userAuthModel->shopUrl = RMS_SETTLEMENT_SHOP_URL;
  $userAuthModel->userName  = RMS_SETTLEMENT_USER_NAME;
  $params = array('userAuthModel' => _convertClassObjectToArray($userAuthModel),
                  'intVal' => $requestId,
                  'uiSalesRequestModels' => $uiSalesRequestModels);
  
  //エラーを無視するエラーハンドラに切り替える（実行後は元に戻す）
  set_error_handler("myErrorHandler");
  try{
      $client = new SoapClient(RMS_API_PAYMENT_SOAP_WSDL, array('trace' => 1 ));
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
    $result = $client->sales($params);
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
function getRCCSRequestId() {

  //パラメータセット
  $userAuthModel = new UserAuthModel();
  $userAuthModel->authKey = RMS_SETTLEMENT_AUTH;
  $userAuthModel->shopUrl = RMS_SETTLEMENT_SHOP_URL;
  $userAuthModel->userName  = RMS_SETTLEMENT_USER_NAME;
  $params = array('userAuthModel' => _convertClassObjectToArray($userAuthModel));
  
  //エラーを無視するエラーハンドラに切り替える（実行後は元に戻す）
  set_error_handler("myErrorHandler");
  try{
      $client = new SoapClient(RMS_API_PAYMENT_SOAP_WSDL, array('trace' => 1 ));
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
    $result = $client->getRCCSRequestId($params);
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
    <title>sales | PaymentAPI</title>
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

