<?php

require_once('config.php');
require_once('util.php');
require_once('class/userAuthModel.php');
require_once('class/uiRCCSResultSearchModel.php');

ini_set('xdebug.var_display_max_children', -1);
ini_set('xdebug.var_display_max_data', -1);
ini_set('xdebug.var_display_max_depth', -1);

// リクエスト結果を見たい条件を作成
$uiRCCSResultSearchModel = new UiRCCSResultSearchModel();
$uiRCCSResultSearchModel->cardStatus = -1; // 特定のカードステータスを検索条件に指定しない場合は「-1：指定なし」

$endDate = new DateTime('now');
$endDate->setTimeZone( new DateTimeZone('Asia/Tokyo'));
$endDate->modify('+1 day'); // 現在時刻の次の日を終了時刻に
$startDate = clone $endDate;
$startDate->modify('-3 day'); // 開始日をいつにするか
$uiRCCSResultSearchModel->fromDate = $startDate->format("Y-m-d");
$uiRCCSResultSearchModel->toDate = $endDate->format("Y-m-d");

/***
 * リクエスト結果を取得 getRCCSResultAll [同期]
 * */

// 楽天へRMS APIを使って送信
list($request, $httpStatusCode, $response) = getRCCSResultAll($uiRCCSResultSearchModel);



/***
* getRCCSResultAll APIのリクエストを行う。
* カード決済結果検索条件モデルに条件を指定し、カード決済処理がどうなったかを確認できる。
* SOAP Clientを使ってパラメータをセットしPOSTしている
* @return リクエストしたxml文字列, httpステータスコード, レスポンスオブジェクト

POST例は下記。本例ではSOAPクライアントを使っているが、自前でやりたい場合は下記のxmlを組み立ててPOSTすること

<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="java:jp.co.rakuten.rms.mall.rccsapi.model.entity" xmlns:ns2="http://orderapi.rms.rakuten.co.jp/rccsapi-services/RCCSAPI" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
  <SOAP-ENV:Body>
    <ns2:getRCCSResultAll>
      <ns2:userAuthModel>
        <ns1:authKey>hogeauth</ns1:authKey>
        <ns1:shopUrl>hogeshop</ns1:shopUrl>
        <ns1:userName>hogeuser</ns1:userName>
      </ns2:userAuthModel>
      <ns2:uiRCCSResultSearchModel>
        <ns1:brandName xsi:nil="true"/>
        <ns1:cardStatus>-1</ns1:cardStatus>
        <ns1:fromDate>2018-07-29</ns1:fromDate>
        <ns1:helpItem xsi:nil="true"/>
        <ns1:orderNumber xsi:nil="true"/>
        <ns1:ownerName xsi:nil="true"/>
        <ns1:payType/>
        <ns1:toDate>2018-08-28</ns1:toDate>
      </ns2:uiRCCSResultSearchModel>
    </ns2:getRCCSResultAll>
  </SOAP-ENV:Body>
</SOAP-ENV:Envelope>


*/
function getRCCSResultAll($uiRCCSResultSearchModel) {

  //パラメータセット
  $userAuthModel = new UserAuthModel();
  $userAuthModel->authKey = RMS_SETTLEMENT_AUTH;
  $userAuthModel->shopUrl = RMS_SETTLEMENT_SHOP_URL;
  $userAuthModel->userName  = RMS_SETTLEMENT_USER_NAME;
  $params = array('userAuthModel' => _convertClassObjectToArray($userAuthModel),
                  'uiRCCSResultSearchModel' => _convertClassObjectToArray($uiRCCSResultSearchModel));
  
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
    $result = $client->getRCCSResultAll($params);
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
    <title>getRCCSResultAll | PaymentAPI</title>
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

