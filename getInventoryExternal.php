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
 * */

// 受注情報を設定
$getRequestExternalModel = new GetRequestExternalModel();
$getRequestExternalModel->itemUrl = '7jfisbuy';

// 楽天へRMS APIを使って送信
list($reqXml, $httpStatusCode, $response) = getInventoryExternal($getRequestExternalModel);



//////////////// 関数群 ////////////////////

/*
* APIのリクエストを行う
* xmlを作って curlでpostしてる
* @param 取得したい受注情報のクラスオブジェクト
* @return リクエストしたxml文字列, httpステータスコード, レスポンス文字列(xmlで返ってくる)
*/
function getInventoryExternal($getRequestExternalModel) {

  $url = RMS_API_INVENTORY_SOAP_ADDRESS;
  $ch = curl_init($url);
  
  $userAuthModel = new ExternalUserAuthModel();
  $userAuthModel->authKey = "ESA " . base64_encode(RMS_SERVICE_SECRET . ':' . RMS_LICENSE_KEY);
  $userAuthModel->shopUrl = RMS_SETTLEMENT_SHOP_URL;
  $userAuthModel->userName  = RMS_SETTLEMENT_USER_NAME;
  
  $reqXml = _createRequestSOAPXml(array("externalUserAuthModel" => $userAuthModel, "getRequestExternalModel" => $getRequestExternalModel));
  
  // return array($reqXml, $httpStatusCode, $response);
  curl_setopt($ch, CURLOPT_POSTFIELDS,     $reqXml);
  curl_setopt($ch, CURLOPT_POST,           true);
  curl_setopt($ch, CURLOPT_TIMEOUT,        30);
  
  $header = array(
    "Content-Type: text/xml;charset=UTF-8",
  );
  curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //返り値を 文字列で返します
  $response = curl_exec($ch);
  if(curl_error($ch)){
    $response = curl_error($ch);
  }
  
  $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  
  curl_close($ch);
  return array($reqXml, $httpStatusCode, $response);
}

/*
* 渡したclassオブジェクトのリストからリクエストのSOAP XMLを自動生成する
* 注意. xmlの要素の順番を変えると400でwrong formatエラーが返却されるクソ仕様。
*/
function _createRequestSOAPXml($objects) {
  
  $paramXmls = array();
  foreach($objects as $index => $object) {
    // オブジェクトをxmlに変換
    $paramXmls[] = _createPartXml($object, $index);
  }

  $reqXml = "<?xml version='1.0' encoding='UTF-8'?>
  <SOAP-ENV:Envelope xmlns:SOAP-ENV='http://schemas.xmlsoap.org/soap/envelope/'>
  <SOAP-ENV:Body>
  <ns1:getInventoryExternal xmlns:ns1='https://inventoryapi.rms.rakuten.co.jp/rms/mall/inventoryapi'>";
  
  foreach($paramXmls as $paramXml) {
    // 各パラメータxmlを挿入
    $reqXml = $reqXml . $paramXml;
  }
  
  $reqXml = $reqXml . "</ns1:getInventoryExternal>
  </SOAP-ENV:Body>
  </SOAP-ENV:Envelope>";
  
  return $reqXml; // リクエストのXMLを返却する
}

/*
* 渡したclassオブジェクトからXMLを自動生成する
* 注意. xmlの要素の順番を変えると400でwrong formatエラーが返却されるクソ仕様。
*/
function _createPartXml($object, $arrayNumber) {

  // リクエストXMLのガワを作る
  $rootXml = new SimpleXMLElement("<{$arrayNumber}/>");
  
  // 受け取ったオブジェクトをarrayに変換
  $array = _convertClassObjectToArray($object);
  
  _arrayToXml($array, $rootXml);  // リクエストのXMLをarray情報から作成する
  
  $returnPartXml = str_replace('<?xml version="1.0"?>', '', $rootXml->asXML());
  return $returnPartXml; // XMLを返却する
}

/**
 * Convert an array to XML
 * @param array $array
 * @param SimpleXMLElement $xml
 * @param array $parentKeyName (その要素が配列で、子要素を親要素の単数形にして登録したい時指定)
 */
function _arrayToXml($array, &$xml, $parentKeyName=null){
  foreach ($array as $key => $value) {
    if(is_array($value)){
      if(is_int($key)){
          if(!empty($parentKeyName)) {
            // 親要素が存在する時、子要素を親要素の単数形の名前にして登録
            $key = singularByPlural($parentKeyName);
          }
      }
      $label = $xml->addChild($key);
      _arrayToXml($value, $label, $key);
    }
    else if(!is_null($value)){
      // 値がセットされている時だけxml要素に追加
      $xml->addChild($key, $value);
    }
  }
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
    <title>getInventory | InventoryAPI</title>
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
        <?php echo htmlspecialchars($reqXml, ENT_QUOTES);; ?>
      </pre>
      <h1>レスポンス結果</h1>
      <h2>HTTP Status code</h2>
      <pre>
        <?php echo $httpStatusCode; ?>
      </pre>
      <h2>生レスポンス</h2>
      <pre>
        <?php 
          echo htmlspecialchars($response, ENT_QUOTES);
          ?>
      </pre>
      <?php 
        // レスポンスをxmlのオブジェクトにパースする
        if ($httpStatusCode == 200) {
          $clean_xml = str_ireplace(['S:', 'ns2:'], '', $response);
          // echo htmlspecialchars($clean_xml, ENT_QUOTES);
          ?>
          <pre>
          <?php 
            // echo $clean_xml;
          ?>
          </pre>
      <?php
          $responseBody = get_object_vars(simplexml_load_string($clean_xml));
        }
      ?>
      <h2>GetOrderResponseModel.message</h2>
      <pre>
        <?php var_dump($responseBody['Body']->getOrderResponse->return->errorCode); ?>
        <?php var_dump($responseBody['Body']->getOrderResponse->return->message); ?>
      </pre>
      <h2>GetOrderResponseModel</h2>
      <pre>
        <?php var_dump($responseBody['Body']->getOrderResponse->return->orderModel); ?>
      </pre>
    </div>
  </body>
</html>

