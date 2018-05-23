<?php

require_once('config.php');
require_once('util.php');
require_once('class/item.php');
require_once('class/image.php');
require_once('class/point.php');

ini_set('xdebug.var_display_max_children', -1);
ini_set('xdebug.var_display_max_data', -1);
ini_set('xdebug.var_display_max_depth', -1);


/***
 * 商品情報のセット
 * */
$item = new Item();

// 商品管理番号(商品URL)、商品番号、商品名、販売価格
$item->itemUrl = 'testrrrz_' . randomStr(3) . '_' . date_format(new DateTime('now', new DateTimeZone('Asia/Tokyo')), 'YmdHis');;
$item->itemNumber = $item->itemUrl;
$item->itemName = 'テスト商品につき購入不可_' . $item->itemUrl;
$item->itemPrice = 100; 

// 在庫関連
$item->itemInventoryType = RMS_ITEM_INVENTORY_TYPE_NORMAL;
$item->inventoryCount = 1; //在庫数
$item->normalDeliveryDateId = 1000; // RMSのデフォルト設定「1～2日以内に発送予定（店舗休業日を除く）」
$item->backorderDeliveryDateId = 1000; // RMSのデフォルト設定「1～2日以内に発送予定（店舗休業日を除く）」

// ポイント倍率設定
$point = new Point();
$point->pointRate = 2; // 変倍率
$point->pointRateStart = new DateTime('now');
$point->pointRateStart->modify('+2 hours +30 minutes'); //現在時刻から2時間30分後を変倍の開始に
$point->pointRateStart->setTimeZone( new DateTimeZone('Asia/Tokyo'));
$point->pointRateEnd = clone $point->pointRateStart;
$point->pointRateEnd->modify('+60 day -1hour'); // 変倍開始から60日後を変倍の終了に
// 時刻を文字列化
$point->pointRateStart = $point->pointRateStart->format(DATE_RFC3339);
$point->pointRateEnd = $point->pointRateEnd->format(DATE_RFC3339);

$item->point = $point;

// ディレクトリID カタログID(JAN)設定
$item->genreId = 209124; //本・雑誌・コミック>PC・システム開発>プログラミング>PHP  この値は連関表から取得
$item->catalogId = 9784797347852; // カタログID(JANコード)
//$item->catalogIdExemptionReason = RMS_CATALOG_EXCEPTION_REASON_NO_JAN;

// 画像関連設定
// 画像が二つある場合 こちらはR-Cabinetにあげたやつを指定。適宜カスタマイズして
for($i = 0; $i < 2; $i++) {
  $image = new Image();
  $image->imageUrl = RMS_IMAGE_BASE_URL . RMS_SETTLEMENT_SHOP_URL . "/cabinet/images/rrrz_01.jpg";
  $image->imageAlt = "$item->itemName";
  $item->images[] = $image;
}

$item->descriptionForPC = '結構html使える';
$item->descriptionForMobile = '一部html使用可能';
$item->descriptionForSmartPhone = '一部html使用可能';
$item->catchCopyForPC = 'PC用キャッチコピー';
$item->catchCopyForMobile = 'モバイル用キャッチコピー';
$item->isIncludedPostage = 0; // 送料無料フラグ (0:送料別 1:送料込)
$item->postage = 108; // 個別送料
$item->isIncludedCashOnDeliveryPostage = 1; // 1:代引料込 (デフォルト0:代引き料別)


/***
 * 楽天へAPIを使って登録
 * */
list($reqXml, $httpStatusCode, $response) = insertItem($item);







/*
* APIのリクエストを行う
* xmlを作って curlでpostしてる
* @return リクエストしたxml文字列, httpステータスコード, レスポンス文字列(xmlで返ってくる)
*/
function insertItem($item) {
  $authkey = base64_encode(RMS_SERVICE_SECRET . ':' . RMS_LICENSE_KEY);
  $header = array(
    "Content-Type: text/xml;charset=UTF-8",
    "Authorization: ESA {$authkey}",
  );

  $url = RMS_API_ITEM_INSERT;
  $ch = curl_init($url);
  
  $reqXml = _createRequestXml($item);
  
  return array($reqXml, $httpStatusCode, $response);
  
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

/**
 * Convert an array to XML
 * @param array $array
 * @param SimpleXMLElement $xml
 */
function arrayToXml($array, &$xml, $parentKeyName){
  var_dump($array);
  foreach ($array as $key => $value) {
    var_dump($parentKeyName);
    if(is_array($value)){
      if(is_int($key)){
          // $key = "e";
          if(!empty($parentKeyName)) {
            $key = substr($parentKeyName, 0, -1); //親のelement名の単数系に
          }
      }
      $label = $xml->addChild($key);
      arrayToXml($value, $label, $key);
    }
    else {
      $xml->addChild($key, $value);
    }
  }
}

/*
* APIのリクエストXMLを作成
* 注意. xmlの要素の順番を変えると400でwrong formatエラーが返却されるクソ仕様。
*       item.getでxmlの要素の順番を確認しながら行うと無難(API仕様書でも良いが間違ってないという保証はない)
*/
function _createRequestXml($item) {
  // 時刻関連を文字列に変換
  // $_pointRateStart = $item->pointRateStart->format(DATE_RFC3339);
  // $_pointRateEnd = $item->pointRateEnd->format(DATE_RFC3339);
  
  $json = json_encode($item);
  $array = (array)json_decode($json, true);

  // $obj = array_flip($obj);
  $xml = new SimpleXMLElement('<request/>');
  // array_walk_recursive($obj, array ($xml, 'addChild'));
  
  arrayToXml($array, $xml);
  
  return $xml->asXML();
  
  $xml  = '<?xml version="1.0" encoding="UTF-8"?>'
      . 
      "
<request>
  <itemInsertRequest>
    <item>
      <itemUrl>$item->itemUrl</itemUrl>
      <itemNumber>$item->itemNumber</itemNumber>
      <itemName>$item->itemName</itemName>
      <itemPrice>$item->itemPrice</itemPrice>
      <genreId>$item->genreId</genreId>
      <catalogId>$item->catalogId</catalogId>";
  
  //画像があれば画像情報をリクエストに挿入
  if(!empty($item->images)) {
    $xml = $xml . "<images>";
    foreach($item->images as $image) {
      $xml = $xml . 
      "
      <image>
        <imageUrl>$image->imageUrl</imageUrl>
        <imageAlt>$image->imageAlt</imageAlt>
      </image>";
    }
    $xml = $xml . "
    </images>";
  }
  
  $xml = $xml . 
    "
      <descriptionForPC>$item->descriptionForPC</descriptionForPC>
      <descriptionForMobile>$item->descriptionForMobile</descriptionForMobile>
      <descriptionForSmartPhone>$item->descriptionForSmartPhone</descriptionForSmartPhone>
      <catchCopyForPC>$item->catchCopyForPC</catchCopyForPC>
      <catchCopyForMobile>$item->catchCopyForMobile</catchCopyForMobile>
      <isIncludedPostage>$item->isIncludedPostage</isIncludedPostage>
      <isIncludedCashOnDeliveryPostage>$item->isIncludedCashOnDeliveryPostage</isIncludedCashOnDeliveryPostage>
      <postage>$item->postage</postage>
      <point>
        <pointRate>$item->pointRate</pointRate>
        <pointRateStart>$_pointRateStart</pointRateStart>
        <pointRateEnd>$_pointRateEnd</pointRateEnd>
      </point>
      <itemInventory>
        <inventoryType>$item->itemInventoryType</inventoryType>
        <inventories>
          <inventory>
            <inventoryCount>$item->inventoryCount</inventoryCount>
            <normalDeliveryDateId>$item->normalDeliveryDateId</normalDeliveryDateId>
            <backorderDeliveryDateId>$item->backorderDeliveryDateId</backorderDeliveryDateId>
          </inventory>
        </inventories>
        <inventoryQuantityFlag>1</inventoryQuantityFlag>
      </itemInventory>
    </item>
  </itemInsertRequest>
</request>";
  
  $xml = simplexml_load_string($xml);
  $json = json_encode($xml);
  $array = json_decode($json,TRUE);
  
  echo "<pre>";
  var_dump($array);
  echo "</pre>";
  
  return $array;
  
  return $xml;
}

?>

<!DOCTYPE html>
<html>
  <head>
    <title>item.insert | ItemAPI</title>
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
          $xml = htmlspecialchars($response, ENT_QUOTES);
          echo $xml; ?>
      </pre>
      <?php 
        // レスポンスをxmlのオブジェクトにパースする
        if ($httpStatusCode == 200) {
          $responseBody = get_object_vars(simplexml_load_string($response));
        }
      ?>
      <h2>result.status</h2>
      <pre>
        <?php var_dump($responseBody['status']); ?>
      </pre>
      <h2>errorMessages</h2>
      <pre>
        <?php var_dump($responseBody['itemInsertResult']->errorMessages); ?>
      </pre>
      <h2>result.itemInsertResult</h2>
      <pre>
        <?php var_dump($responseBody['itemInsertResult']); ?>
      </pre>
    </div>
  </body>
</html>

