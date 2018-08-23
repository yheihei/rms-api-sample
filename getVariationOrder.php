<?php
/**
 * 詳細解説記事はこちら
 * https://virusee.net/rms-api-getorder/
 * 
 * */

require_once('config.php');
require_once('util.php');
require_once('class/getOrderRequestModel.php');
require_once('class/orderSearchModel.php');
require_once('class/userAuthModel.php');
require_once('class/orderVariationItem.php');

ini_set('xdebug.var_display_max_children', -1);
ini_set('xdebug.var_display_max_data', -1);
ini_set('xdebug.var_display_max_depth', -1);

/***
 * 受注情報の検索情報セット
 * */

// 受注情報を設定
$orderRequestModel = new GetOrderRequestModel();
$orderRequestModel->isOrderNumberOnlyFlg = 0; // false:受注情報を取得

$queryOrderNumber = $_GET[num];
if(empty($queryOrderNumber)) {
  // 受注検索モデルを設定
  $orderSearchModel = new OrderSearchModel();
  $orderSearchModel->dateType = RMS_GET_ORDER_DATE_TYPE_ORDER;
  $endDate = new DateTime('now');
  $endDate->setTimeZone( new DateTimeZone('Asia/Tokyo'));
  $endDate->modify('+1 day'); // 現在時刻の次の日を終了時刻に
  $startDate = clone $endDate;
  $startDate->modify('-30 day'); // 30日前を開始に
  $orderSearchModel->startDate = $startDate->format("Y-m-d");
  $orderSearchModel->endDate = $endDate->format("Y-m-d");
  //受注検索モデルを受注情報にセット
  $orderRequestModel->orderSearchModel = $orderSearchModel;
} else {
  // 受注番号指定の場合下記を設定
  $orderNumber = array($queryOrderNumber); // 複数指定可能
  $orderRequestModel->orderNumber = $orderNumber; // 受注番号を受注情報にセット
}

// 楽天へRMS APIを使って送信
list($request, $httpStatusCode, $response) = getOrder($orderRequestModel);

// 受注の詳細を取得
// レスポンスを整形してオブジェクトに
$response = str_ireplace("S:", "", $response);
$response = str_ireplace("ns2:", "", $response);
$xml = simplexml_load_string($response);
$responseObject = $xml->Body->getOrderResponse->return;
$order_models = SimpleXMLElementArrayToArray($responseObject->orderModel);

// 受注した商品を取得
$items = array();
foreach ($order_models as $order_model) {
  $item_models = $order_model['packageModel']['itemModel'];
  if( isset( $item_models[0] ) ) {
      // 一つの受注で複数商品存在する場合
      $items = $item_models;
  } else {
      // 一つの受注で一つの商品のみの場合
      $items[] = $item_models;
  }
}

// バリエーション商品だった場合、情報を格納
$order_variation_items = array();
foreach ($items as $item) {
  if( $item['normalItemModel']['inventoryType'] == 2 ) {
    // 項目選択肢在庫の場合
    $item_code = (string) $item['itemNumber'];
    $selectedChoice = $item['selectedChoice'];
    $order_variation_item = new OrderVatiationItem($item_code, $selectedChoice);
    $order_variation_items[] = $order_variation_item;
  }
}


//////////////// 関数群 ////////////////////

/***
 * 受注情報の取得 getOrder　[同期]
 * @see https://virusee.net/rms-api-getorder/
 * 
 * POST例は下記。本例ではSOAPクライアントを使っているが、自前でやりたい場合は下記のxmlを組み立ててPOSTすること
 
<?xml version="1.0" encoding="UTF-8"?>
<SOAP-ENV:Envelope xmlns:SOAP-ENV="http://schemas.xmlsoap.org/soap/envelope/" xmlns:ns1="http://orderapi.rms.rakuten.co.jp/rms/mall/order/api/ws">
  <SOAP-ENV:Body>
    <ns1:getOrder>
      <arg0>
        <authKey>ESA hogekey</authKey>
        <shopUrl>hogeshop</shopUrl>
        <userName>hogename</userName>
      </arg0>
      <arg1>
        <isOrderNumberOnlyFlg>false</isOrderNumberOnlyFlg>
        <orderSearchModel>
          <dateType>1</dateType>
          <endDate>2018-06-01</endDate>
          <orderType>1</orderType>
          <orderType>2</orderType>
          <orderType>3</orderType>
          <orderType>4</orderType>
          <orderType>5</orderType>
          <orderType>6</orderType>
          <startDate>2018-05-02</startDate>
        </orderSearchModel>
      </arg1>
    </ns1:getOrder>
  </SOAP-ENV:Body>
</SOAP-ENV:Envelope>
  
 * */
function getOrder($orderRequestModel) {

  //パラメータセット
  $userAuthModel = new UserAuthModel();
  $userAuthModel->authKey = "ESA " . base64_encode(RMS_SERVICE_SECRET . ':' . RMS_LICENSE_KEY);
  $userAuthModel->shopUrl = RMS_SETTLEMENT_SHOP_URL;
  $userAuthModel->userName  = RMS_SETTLEMENT_USER_NAME;
  $requestArray = _convertClassObjectToArray($orderRequestModel);
  $requestArray = _deleteEmptyElements($requestArray);
  
  $params = array('arg0' => _convertClassObjectToArray($userAuthModel),
    'arg1' => $requestArray);
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
    $result = $client->getOrder($params);
    // customVarDump($result);
  } catch (SoapFault $e) {
    // customVarDump($e);
    //SOAP通信の実行に失敗した場合の処理
  }
  // customVarDump($client->__getLastRequest());
  // customVarDump($client->__getLastResponse());
  
  // return array($client->__getLastRequest(), extract_response_http_code($client->__getLastResponseHeaders()), $result);
  return array($client->__getLastRequest(), extract_response_http_code($client->__getLastResponseHeaders()), $client->__getLastResponse());
}

// エラーハンドラ関数
function myErrorHandler($errno, $errstr, $errfile, $errline)
{
  
}

/**
 * arrayからnullの要素を除去
 * */
function _deleteEmptyElements($array) {
  foreach($array as $key => $value) {
    if(is_null($value)) {
      unset($array[$key]);
    }
  }
  return $array;
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
    <title>getOrder | OrderAPI 項目選択肢在庫</title>
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
      <h2>項目選択肢在庫の注文情報</h2>
      <pre>
        <?php
          echo print_r($order_variation_items, true);
        ?>
      </pre>
      <h2>生レスポンス</h2>
      <pre>
        <?php 
          echo htmlspecialchars(returnFormattedXmlString($response), ENT_QUOTES);
          ?>
      </pre>
      <h2>受注情報全体のオブジェクト</h2>
      <pre>
        <?php 
          echo print_r($order_models, true);
        ?>
      </pre>
    </div>
  </body>
</html>

