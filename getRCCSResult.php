<?php

require_once('config.php');
require_once('util.php');
require_once('class/userAuthModel.php');
require_once('class/uiCancelRequestModel.php');

ini_set('xdebug.var_display_max_children', -1);
ini_set('xdebug.var_display_max_data', -1);
ini_set('xdebug.var_display_max_depth', -1);

// リクエスト結果を見たいリクエストIDを入力
$queryRequestId = $_GET[num];
if(empty($queryRequestId)) {
  $requestIds = array(174355113);
} else {
  $requestIds = array($queryRequestId);
}

/***
 * リクエスト結果を取得 getRCCSResult [同期]
 * */

// 楽天へRMS APIを使って送信
list($request, $httpStatusCode, $response) = getRCCSResult($requestIds);



/***
* getRCCSResult APIのリクエストを行う。
* 非同期のリクエストのrequestIdをセットすることで、非同期リクエストの結果がどうなったか取得できる
* SOAP Clientを使ってパラメータをセットしPOSTしている
* @return リクエストしたxml文字列, httpステータスコード, レスポンスオブジェクト

POST例は下記。本例ではSOAPクライアントを使っているが、自前でやりたい場合は下記のxmlを組み立ててPOSTすること

<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="java:jp.co.rakuten.rms.mall.rccsapi.model.entity" xmlns:ns2="http://orderapi.rms.rakuten.co.jp/rccsapi-services/RCCSAPI" xmlns:ns3="java:language_builtins">
  <SOAP-ENV:Body>
    <ns2:getRCCSResult>
      <ns2:userAuthModel>
        <ns1:authKey>hogekey</ns1:authKey>
        <ns1:shopUrl>hogeshop</ns1:shopUrl>
        <ns1:userName>hogename</ns1:userName>
      </ns2:userAuthModel>
      <ns2:ints>
        <ns3:int>167316941</ns3:int>
      </ns2:ints>
    </ns2:getRCCSResult>
  </SOAP-ENV:Body>
</SOAP-ENV:Envelope>

*/
function getRCCSResult($requestIds) {

  //パラメータセット
  $userAuthModel = new UserAuthModel();
  $userAuthModel->authKey = RMS_SETTLEMENT_AUTH;
  $userAuthModel->shopUrl = RMS_SETTLEMENT_SHOP_URL;
  $userAuthModel->userName  = RMS_SETTLEMENT_USER_NAME;
  $params = array('userAuthModel' => _convertClassObjectToArray($userAuthModel),
                  'ints' => $requestIds);
  
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
    $result = $client->getRCCSResult($params);
    // customVarDump($result);
  } catch (SoapFault $e) {
    // customVarDump($e);
    //SOAP通信の実行に失敗した場合の処理
  }
  // customVarDump($client->__getLastRequest());
  // customVarDump($client->__getLastResponse());
  
  return array($client->__getLastRequest(), extract_response_http_code($client->__getLastResponseHeaders()), $client->__getLastResponse());
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
    <title>getRCCSResult | PaymentAPI</title>
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
          // echo customVarDump($response);
          echo htmlspecialchars(returnFormattedXmlString($response), ENT_QUOTES);
          ?>
      </pre>
    </div>
  </body>
</html>

