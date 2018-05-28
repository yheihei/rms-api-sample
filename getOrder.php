<?php

require_once('config.php');
require_once('util.php');
require_once('class/item.php');
require_once('class/image.php');
require_once('class/point.php');
require_once('class/itemInventory.php');
require_once('class/inventory.php');

ini_set('xdebug.var_display_max_children', -1);
ini_set('xdebug.var_display_max_data', -1);
ini_set('xdebug.var_display_max_depth', -1);

/***
 * 受注情報の検索情報セット
 * */



// 楽天へRMS APIを使って登録
list($reqXml, $httpStatusCode, $response) = getOrder($orderRequestModel);



//////////////// 関数群 ////////////////////

/*
* APIのリクエストを行う
* xmlを作って curlでpostしてる
* @param 取得したい受注情報のクラスオブジェクト
* @return リクエストしたxml文字列, httpステータスコード, レスポンス文字列(xmlで返ってくる)
*/
function getOrder($item) {
  // $authkey = base64_encode(RMS_SERVICE_SECRET . ':' . RMS_LICENSE_KEY);
  // $header = array(
  //   "Content-Type: text/xml;charset=UTF-8",
  //   "Authorization: ESA {$authkey}",
  // );

  $url = RMS_API_ORDER_GET;
  $ch = curl_init($url);
  
  // $reqXml = _createRequestXml($item);
  
  $authkey = "ESA " . base64_encode(RMS_SERVICE_SECRET . ':' . RMS_LICENSE_KEY);
  $shop_url = RMS_SETTLEMENT_SHOP_URL;
  $user_name = RMS_SETTLEMENT_USER_NAME;
  $reqXml = "<?xml version='1.0' encoding='UTF-8'?>
  <SOAP-ENV:Envelope xmlns:SOAP-ENV='http://schemas.xmlsoap.org/soap/envelope/' xmlns:ns1='http://orderapi.rms.rakuten.co.jp/rms/mall/order/api/ws'>
  <SOAP-ENV:Body>
  <ns1:getOrder>
  <arg0>
    <authKey>{$authkey}</authKey>
    <shopUrl>{$shop_url}</shopUrl>
    <userName>{$user_name}</userName>
  </arg0>
  <arg1>
    <isOrderNumberOnlyFlg>false</isOrderNumberOnlyFlg>
    <orderSearchModel>
      <dateType>1</dateType>
      <startDate>2018-05-24</startDate>
      <endDate>2018-05-30</endDate>
      <orderType>1</orderType>
    </orderSearchModel>
  </arg1>
  </ns1:getOrder>
  </SOAP-ENV:Body>
  </SOAP-ENV:Envelope>";
  $header = array(
    "Content-Type: text/xml;charset=UTF-8",
  );
  
  // return array($reqXml, $httpStatusCode, $response);
  
  curl_setopt($ch, CURLOPT_POSTFIELDS,     $reqXml);
  curl_setopt($ch, CURLOPT_POST,           true);
  curl_setopt($ch, CURLOPT_TIMEOUT,        30);
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
* 渡したclassオブジェクトからリクエストのXMLを自動生成する
* 注意. xmlの要素の順番を変えると400でwrong formatエラーが返却されるクソ仕様。
*       item.getでxmlの要素の順番を確認しながら行うと無難(API仕様書でも良いが間違ってないという保証はない)
*/
function _createRequestXml($item) {

  // リクエストXMLのガワを作る
  $rootXml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><request/>');
  $itemInsertRequestXml = $rootXml->addChild('itemInsertRequest');
  $itemXml = $itemInsertRequestXml->addChild('item');
  
  // 受け取った商品情報オブジェクトをarrayに変換
  $array = _convertClassObjectToArray($item);
  
  _arrayToXml($array, $itemXml);  // リクエストのXMLをarray情報から作成する
  
  return $rootXml->asXML(); // リクエストのXMLを返却する
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
    <title>getOrder | OrderAPI</title>
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

