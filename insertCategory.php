<?php

require_once('config.php');
require_once('util.php');
require_once('class/categoryInsertRequest.php');
require_once('class/category.php');

ini_set('xdebug.var_display_max_children', -1);
ini_set('xdebug.var_display_max_data', -1);
ini_set('xdebug.var_display_max_depth', -1);


/***
 * カテゴリー情報のセット
 * */
$categoryInsertRequest = new CategoryInsertRequest();
$categoryInsertRequest->categoryId = 0; //どのカテゴリー配下に置くか。0はルート
// $categoryInsertRequest->categoryId = 100;

// カテゴリーの名前や優先度をセット
$category = new Category();
$category->name = $_GET[num]; // クエリストリングでカテゴリー名指定
if(empty($category->name)) {
  $category->name = 'test_' . randomStr(3) . '_' . date_format(new DateTime('now', new DateTimeZone('Asia/Tokyo')), 'YmdHis');
}
$category->status = 0;
// $category->categoryWeight = 1;
$categoryInsertRequest->category = $category;

// 楽天へRMS APIを使って登録
list($reqXml, $httpStatusCode, $response) = insertCategory($categoryInsertRequest);



//////////////// 関数群 ////////////////////

/*
* shop.category.insert APIのリクエストを行う
* xmlを作って curlでpostしてる
* @param 挿入したいカテゴリー情報のクラスオブジェクト
* @return リクエストしたxml文字列, httpステータスコード, レスポンス文字列(xmlで返ってくる)
*/
function insertCategory($categoryInsertRequest) {
  $authkey = base64_encode(RMS_SERVICE_SECRET . ':' . RMS_LICENSE_KEY);
  $header = array(
    "Content-Type: text/xml;charset=UTF-8",
    "Authorization: ESA {$authkey}",
  );

  $url = RMS_API_CATEGORY_INSERT;
  $ch = curl_init($url);
  
  $reqXml = _createRequestXml($categoryInsertRequest);
  
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
*/
function _createRequestXml($categoryInsertRequest) {

  // リクエストXMLのガワを作る
  $rootXml = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><request/>');
  $categoryInsertRequestXml = $rootXml->addChild('categoryInsertRequest');
  
  // 受け取ったオブジェクトをarrayに変換
  $array = _convertClassObjectToArray($categoryInsertRequest);
  
  _arrayToXml($array, $categoryInsertRequestXml);  // リクエストのXMLをarray情報から作成する
  
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
    <title>shop.category.insert | CategoryAPI</title>
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
        <?php echo htmlspecialchars(returnFormattedXmlString($reqXml), ENT_QUOTES); ?>
      </pre>
      <h1>レスポンス結果</h1>
      <h2>HTTP Status code</h2>
      <pre>
        <?php echo $httpStatusCode; ?>
      </pre>
      <h2>生レスポンス</h2>
      <pre>
        <?php 
          $xml = htmlspecialchars(returnFormattedXmlString($response), ENT_QUOTES);
          echo $xml; ?>
      </pre>
    </div>
  </body>
</html>

