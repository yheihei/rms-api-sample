<?php

require_once('config.php');
require_once('util.php');
require_once('class/getRequestExternalModel.php');
require_once('class/externalUserAuthModel.php');

ini_set('xdebug.var_display_max_children', -1);
ini_set('xdebug.var_display_max_data', -1);
ini_set('xdebug.var_display_max_depth', -1);

/***
 * 在庫情報の検索情報セット
 * 商品のitemUrlをリストに詰めてコールすると、在庫タイプ：通常/項目選択肢別の全ての在庫数などの情報を返してくれる
 * 
 * */

// 受注情報を設定
$getRequestExternalModel = new GetRequestExternalModel();
$getRequestExternalModel->itemUrl = array('7jfisbuy'); // 取得したい商品のitemUrlをリストで入れる

// 楽天へRMS APIを使って送信
list($request, $httpStatusCode, $response) = getInventoryExternal($getRequestExternalModel);



//////////////// 関数群 ////////////////////

/***
 * 在庫情報取得 getInventoryExternal　[同期]
 * itemUrlを指定して在庫情報を取得する機能です。
 * 
 * @return リクエストしたxml文字列, httpステータスコード, レスポンスオブジェクト
 * 
 * POST例は下記。本例ではSOAPクライアントを使っているが、自前でやりたい場合は下記のxmlを組み立ててPOSTすること
 
<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="java:jp.co.rakuten.rms.mall.inventoryapi.v1.model.entity" xmlns:ns2="https://inventoryapi.rms.rakuten.co.jp/rms/mall/inventoryapi" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:ns3="java:language_builtins.lang">
  <SOAP-ENV:Body>
    <ns2:getInventoryExternal>
      <ns2:externalUserAuthModel>
        <ns1:authKey>ESA hogekey</ns1:authKey>
        <ns1:userName>hogename</ns1:userName>
        <ns1:shopUrl>hogeshop</ns1:shopUrl>
      </ns2:externalUserAuthModel>
      <ns2:getRequestExternalModel>
        <ns1:inventorySearchRange xsi:nil="true"/>
        <ns1:itemUrl>
          <ns3:string>7jfisbuy</ns3:string>
        </ns1:itemUrl>
      </ns2:getRequestExternalModel>
    </ns2:getInventoryExternal>
  </SOAP-ENV:Body>
</SOAP-ENV:Envelope>
  
 * */
function getInventoryExternal($getRequestExternalModel) {

  //パラメータセット
  $userAuthModel = new ExternalUserAuthModel();
  $userAuthModel->authKey = "ESA " . base64_encode(RMS_SERVICE_SECRET . ':' . RMS_LICENSE_KEY);
  $userAuthModel->shopUrl = RMS_SETTLEMENT_SHOP_URL;
  $userAuthModel->userName  = RMS_SETTLEMENT_USER_NAME;
  $params = array('externalUserAuthModel' => _convertClassObjectToArray($userAuthModel),
    'getRequestExternalModel' => _convertClassObjectToArray($getRequestExternalModel));
  // customVarDump($params);
  
  //エラーを無視するエラーハンドラに切り替える（実行後は元に戻す）
  set_error_handler("myErrorHandler");
  try{
    // WSDLファイルを読み込む 楽天サーバーの見えるところに置かれてないので、自サーバーの適当なところに置いて読む
    $client = new SoapClient(__DIR__ ."/wsdl/inventoryapi.wsdl", array('trace' => 1 ));
    //エラーハンドラ回復
    restore_error_handler();
  } catch (Exception $e) {
    //エラーハンドラ回復
    restore_error_handler();
    echo $e->getMessage();
    return array(null, "400", $e->getMessage()); // 適切に対処した方が良い
    //WSDLファイルの読み込みに失敗した場合の処理
  }
  
  try{
    //SOAP通信実行
    $result = $client->getInventoryExternal($params);
    // customVarDump($result);
  } catch (SoapFault $e) {
    // customVarDump($e);
    echo $e->getMessage();
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
    <title>getInventoryExternal | OrderAPI</title>
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

