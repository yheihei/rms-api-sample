<?php

require_once('config.php');
require_once('util.php');
require_once('class/userAuthModel.php');
require_once('class/changeStatusRequestModel.php');
require_once('class/orderStatusModel.php');

ini_set('xdebug.var_display_max_children', -1);
ini_set('xdebug.var_display_max_data', -1);
ini_set('xdebug.var_display_max_depth', -1);

/***
 * OrderAPIの非同期のリクエスト確認
 * $requestIdはシステム内に保存しておくこと。
 * */
// 結果を確認したいリクエストIDを入力(arrayで複数可能)
$requestIds = array(607077163);


// 楽天へRMS APIを使って送信
list($request, $httpStatusCode, $response) = getResult($requestIds);

//////////////// 関数群 ////////////////////

/***
 * 非同期リクエストの結果確認 getResult　[同期]
 * $requestIdはシステム内に保存しておくこと。
 * 
 * POST例は下記。本例ではSOAPクライアントを使っているが、自前でやりたい場合は下記のxmlを組み立ててPOSTすること
 
<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://orderapi.rms.rakuten.co.jp/rms/mall/order/api/ws">
  <SOAP-ENV:Body>
    <ns1:getResult>
      <arg0>
        <authKey>ESA hogekey</authKey>
        <shopUrl>hogeshop</shopUrl>
        <userName>hogename</userName>
      </arg0>
      <arg1>
        <requestId>584649535</requestId>
        <requestId>584618680</requestId>
      </arg1>
    </ns1:getResult>
  </SOAP-ENV:Body>
</SOAP-ENV:Envelope>
  
 * */
function getResult($requestIds) {

  //パラメータセット
  $userAuthModel = new UserAuthModel();
  $userAuthModel->authKey = "ESA " . base64_encode(RMS_SERVICE_SECRET . ':' . RMS_LICENSE_KEY);
  $userAuthModel->shopUrl = RMS_SETTLEMENT_SHOP_URL;
  $userAuthModel->userName  = RMS_SETTLEMENT_USER_NAME;
  $params = array('arg0' => _convertClassObjectToArray($userAuthModel),
    'arg1' => $requestIds);
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
    $result = $client->getResult($params);
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
    <title>getResult | OrderAPI</title>
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

