<?php

/*
詳細解説記事はこちら  
https://virusee.net/rms-api-updateinventoryexternal/
*/

require_once('config.php');
require_once('util.php');
require_once('class/updateRequestExternalModel.php');
require_once('class/updateRequestExternalItem.php');
require_once('class/externalUserAuthModel.php');

ini_set('xdebug.var_display_max_children', -1);
ini_set('xdebug.var_display_max_data', -1);
ini_set('xdebug.var_display_max_depth', -1);

/***
 * 在庫情報セット
 * */

// 在庫情報を設定
$updateRequestExternalModel = new UpdateRequestExternalModel();

// 更新したい在庫の情報を設定 「Sサイズのred色の在庫を任意の在庫数に変える」
$updateRequestExternalItem = new UpdateRequestExternalItem();
$updateRequestExternalItem->itemUrl = '7jfisbuy';
$updateRequestExternalItem->inventoryType = 3; // 項目選択肢別在庫設定
$updateRequestExternalItem->restTypeFlag = 0;
$updateRequestExternalItem->HChoiceName = 'S'; // 更新したい在庫の横軸
$updateRequestExternalItem->VChoiceName = 'red'; // 更新したい在庫の縦軸
$updateRequestExternalItem->orderFlag = 0;
$updateRequestExternalItem->nokoriThreshold = -1;
$updateRequestExternalItem->inventoryUpdateMode = 1;
$updateRequestExternalItem->inventoryBackFlag = 1;
$updateRequestExternalItem->inventory = 50; // 在庫数
$updateRequestExternalItem->normalDeliveryDeleteFlag = 0;
$updateRequestExternalItem->normalDeliveryId = 1000;
$updateRequestExternalItem->lackDeliveryDeleteFlag = 0;
$updateRequestExternalItem->lackDeliveryId = 1000;
$updateRequestExternalItem->orderSalesFlag = 1;

// 在庫情報に更新したい在庫情報をセット
$updateRequestExternalModel->updateRequestExternalItem[] = $updateRequestExternalItem;

// customVarDump($updateRequestExternalModel);

// 楽天へRMS APIを使って送信
list($request, $httpStatusCode, $response) = updateInventoryExternal($updateRequestExternalModel);



//////////////// 関数群 ////////////////////

/***
 * 在庫情報更新 updateInventoryExternal　[同期]
 * 商品単位または項目選択肢単位で、在庫情報（在庫数、納期情報など）の更新を行います。
 * 最大400件を同時に更新できます。
 * 
 * @return リクエストしたxml文字列, httpステータスコード, レスポンスオブジェクト
 * 
 * POST例は下記。本例ではSOAPクライアントを使っているが、自前でやりたい場合は下記のxmlを組み立ててPOSTすること
 
<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="java:jp.co.rakuten.rms.mall.inventoryapi.v1.model.entity" xmlns:ns2="https://inventoryapi.rms.rakuten.co.jp/rms/mall/inventoryapi">
  <SOAP-ENV:Body>
    <ns2:updateInventoryExternal>
      <ns2:externalUserAuthModel>
        <ns1:authKey>ESA hogekey</ns1:authKey>
        <ns1:userName>hogename</ns1:userName>
        <ns1:shopUrl>hogeshop</ns1:shopUrl>
      </ns2:externalUserAuthModel>
      <ns2:updateRequestExternalModel>
        <ns1:updateRequestExternalItem>
          <ns1:UpdateRequestExternalItem>
            <ns1:HChoiceName>S</ns1:HChoiceName>
            <ns1:VChoiceName>red</ns1:VChoiceName>
            <ns1:inventory>50</ns1:inventory>
            <ns1:inventoryBackFlag>1</ns1:inventoryBackFlag>
            <ns1:inventoryType>3</ns1:inventoryType>
            <ns1:inventoryUpdateMode>1</ns1:inventoryUpdateMode>
            <ns1:itemUrl>7jfisbuy</ns1:itemUrl>
            <ns1:lackDeliveryDeleteFlag>false</ns1:lackDeliveryDeleteFlag>
            <ns1:lackDeliveryId>1000</ns1:lackDeliveryId>
            <ns1:nokoriThreshold>-1</ns1:nokoriThreshold>
            <ns1:normalDeliveryDeleteFlag>false</ns1:normalDeliveryDeleteFlag>
            <ns1:normalDeliveryId>1000</ns1:normalDeliveryId>
            <ns1:orderFlag>0</ns1:orderFlag>
            <ns1:orderSalesFlag>1</ns1:orderSalesFlag>
            <ns1:restTypeFlag>0</ns1:restTypeFlag>
          </ns1:UpdateRequestExternalItem>
        </ns1:updateRequestExternalItem>
      </ns2:updateRequestExternalModel>
    </ns2:updateInventoryExternal>
  </SOAP-ENV:Body>
</SOAP-ENV:Envelope>
  
 * */
function updateInventoryExternal($updateRequestExternalModel) {

  //パラメータセット
  $userAuthModel = new ExternalUserAuthModel();
  $userAuthModel->authKey = "ESA " . base64_encode(RMS_SERVICE_SECRET . ':' . RMS_LICENSE_KEY);
  $userAuthModel->shopUrl = RMS_SETTLEMENT_SHOP_URL;
  $userAuthModel->userName  = RMS_SETTLEMENT_USER_NAME;
  $params = array('externalUserAuthModel' => _convertClassObjectToArray($userAuthModel),
    'updateRequestExternalModel' => _convertClassObjectToArray($updateRequestExternalModel));
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
    $result = $client->updateInventoryExternal($params);
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
    <title>updateInventoryExternal | OrderAPI</title>
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
