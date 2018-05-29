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
 * 商品情報のセット
 * */
$item = new Item();

// 商品管理番号(商品URL)、商品番号、商品名、販売価格
$item->itemUrl = '7jfisbuy';
// $item->itemNumber = $item->itemUrl;
// $item->itemName = 'テスト商品につき購入不可_' . $item->itemUrl;
$item->itemPrice = rand(100, 200); 

// // 在庫関連設定
$itemInventory = new ItemInventory();
$itemInventory->inventoryType = RMS_ITEM_INVENTORY_TYPE_NORMAL; // 通常在庫設定　在庫数を変えるときはinventoryType指定が必須
// $itemInventory->inventoryQuantityFlag = 1;
// 個別の在庫
$inventory = new Inventory();
$inventory->inventoryCount = rand(1,16);
$inventory->normalDeliveryDateId = 1000; // RMSのデフォルト設定「1～2日以内に発送予定（店舗休業日を除く）」
$inventory->backorderDeliveryDateId = 1000; // RMSのデフォルト設定「1～2日以内に発送予定（店舗休業日を除く）」
// 在庫リストに個別の在庫を格納
$itemInventory->inventories[] = $inventory;
// 商品に在庫情報をセット
$item->itemInventory = $itemInventory;

// (1) ポイント倍率更新の場合
$point = new Point();
$point->pointRate = 2; //変倍率
$pointRateStart = new DateTime('now');
$pointRateStart->modify('+2 hours +30 minutes'); //現在時刻から2時間30分後を変倍の開始に
$pointRateStart->setTimeZone( new DateTimeZone('Asia/Tokyo'));
$pointRateEnd = clone $pointRateStart;
$pointRateEnd->modify('+60 day -1hour'); // 変倍開始から60日後を変倍の終了に
$point->pointRateStart = $pointRateStart->format(DATE_RFC3339); // 変倍開始時期を文字列でセット
$point->pointRateEnd = $pointRateEnd->format(DATE_RFC3339); // 変倍終了時期を文字列でセット
// (2) ポイント倍率削除の場合 空オブジェクトを入れると削除
// $point = new Point();

// 商品にポイント倍率をセット
$item->point = $point;

// // ディレクトリID カタログID(JAN)設定
// $item->genreId = 209124; //本・雑誌・コミック>PC・システム開発>プログラミング>PHP  この値は連関表から取得
// $item->catalogId = 9784797347852; // カタログID(JANコード)
// //$item->catalogIdExemptionReason = RMS_CATALOG_EXCEPTION_REASON_NO_JAN;

// 画像関連設定
// 画像が二つある場合 こちらはR-Cabinetにあげたやつを指定。適宜カスタマイズして
for($i = 0; $i < 2; $i++) {
  $image = new Image();
  $image->imageUrl = RMS_IMAGE_BASE_URL . RMS_SETTLEMENT_SHOP_URL . "/cabinet/images/rrrz_01.jpg";
  $image->imageAlt = "$item->itemName";
  $item->images[] = $image; // 商品に画像をセット
}

// 説明文関連設定
$item->descriptionForPC = '結構html使える' . '_' . date_format(new DateTime('now', new DateTimeZone('Asia/Tokyo')), 'YmdHis');
$item->descriptionForMobile = '一部html使用可能' . '_' . date_format(new DateTime('now', new DateTimeZone('Asia/Tokyo')), 'YmdHis');
$item->descriptionForSmartPhone = '一部html使用可能' . '_' . date_format(new DateTime('now', new DateTimeZone('Asia/Tokyo')), 'YmdHis');
$item->catchCopyForPC = 'PC用キャッチコピー' . '_' . date_format(new DateTime('now', new DateTimeZone('Asia/Tokyo')), 'YmdHis');
$item->catchCopyForMobile = 'モバイル用キャッチコピー' . '_' . date_format(new DateTime('now', new DateTimeZone('Asia/Tokyo')), 'YmdHis');

// 送料など設定
$item->isIncludedPostage = 0; // 送料無料フラグ (0:送料別 1:送料込)
$item->postage = rand(100, 200); // 個別送料
$item->isIncludedCashOnDeliveryPostage = 1; // 1:代引料込 (デフォルト0:代引き料別)


// 楽天へRMS APIを使って登録
list($reqXml, $httpStatusCode, $response) = updateItem($item);



//////////////// 関数群 ////////////////////

/*
* APIのリクエストを行う
* xmlを作って curlでpostしてる
* @param 挿入したい商品情報のクラスオブジェクト
* @return リクエストしたxml文字列, httpステータスコード, レスポンス文字列(xmlで返ってくる)
*/
function updateItem($item) {
  $authkey = base64_encode(RMS_SERVICE_SECRET . ':' . RMS_LICENSE_KEY);
  $header = array(
    "Content-Type: text/xml;charset=UTF-8",
    "Authorization: ESA {$authkey}",
  );

  $url = RMS_API_ITEM_UPDATE;
  $ch = curl_init($url);
  
  $reqXml = _createRequestXml($item);
  
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
  $itemInsertRequestXml = $rootXml->addChild('itemUpdateRequest');
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
    <title>item.update | ItemAPI</title>
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
        <?php var_dump($responseBody['itemUpdateResult']->errorMessages); ?>
      </pre>
      <h2>result.itemUpdateResult</h2>
      <pre>
        <?php var_dump($responseBody['itemUpdateResult']); ?>
      </pre>
    </div>
  </body>
</html>

