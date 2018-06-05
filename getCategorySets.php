<?php

require_once('config.php');
require_once('util.php');


list($httpStatusCode, $response) = getCategorySets();

/***
 * shop.categorysets.get APIを使って、登録しているカテゴリセットの一覧を取得することができます。
 * サンプルレスポンスは下記

<?xml version="1.0" encoding="UTF-8"?>
<result>
	<status>
		<interfaceId>shop.categorysets.get</interfaceId>
		<systemStatus>OK</systemStatus>
		<message>OK</message>
		<requestId>714a4983-555f-42d9-aeea-89dae89f2f55</requestId>
	</status>
	<categorysetsGetResult>
		<code>N000</code>
		<shopId/>
		<categorySetList>
			<categorySet>
				<categorySetManageNumber>0</categorySetManageNumber>
				<categorySetName>ブランド品</categorySetName>
				<categorySetStatus>0</categorySetStatus>
				<categorySetOrder>1</categorySetOrder>
			</categorySet>
			<categorySet>
				<categorySetManageNumber>1</categorySetManageNumber>
				<categorySetName>お菓子</categorySetName>
				<categorySetStatus>0</categorySetStatus>
				<categorySetOrder>2</categorySetOrder>
			</categorySet>
			…
		</categorySetList>
	</categorysetsGetResult>
</result>
 * */
function getCategorySets() {
  $authkey = base64_encode(RMS_SERVICE_SECRET . ':' . RMS_LICENSE_KEY);
  $header = array(
    "Content-Type: text/xml;charset=UTF-8",
    "Authorization: ESA {$authkey}",
  );

  $url = RMS_API_CATEGORY_SETS_GET;
  customVarDump($url);
  
  $ch = curl_init($url);

  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
  curl_setopt($ch, CURLOPT_TIMEOUT,        30);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); //返り値を 文字列で返します
  
  $response = curl_exec($ch);
  if(curl_error($ch)){
    $response = curl_error($ch);
  }
  
  $httpStatusCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
  
  curl_close($ch);
  return array($httpStatusCode, $response);
}

?>

<!DOCTYPE html>
<html>
  <head>
    <title>shop.categorysets.get | CategoryAPI</title>
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
      <h1>レスポンス結果</h1>
      <h2>HTTP Status code</h2>
      <pre>
        <?php echo $httpStatusCode; ?>
      </pre>
      <h2>生レスポンス</h2>
      <pre>
        <?php 
          error_log(print_r($response, true));
          echo htmlspecialchars(returnFormattedXmlString($response) , ENT_QUOTES);  ?>
      </pre>
    </div>
  </body>
</html>

