<?php

require_once('config.php');
require_once('util.php');


list($httpStatusCode, $response) = getUsage();

/***
 * cabinet.usage.get APIを使って、R-Cabinetの残容量などが確認できる。
 * サンプルレスポンスは下記

<?xml version="1.0" encoding="UTF-8"?>
<result>
  <status>
    <interfaceId>cabinet.usage.get</interfaceId>
    <systemStatus>OK</systemStatus>
    <message>OK</message>
    <requestId>d199dc5e-bbbb-4aa4-a678-d456f9643234</requestId>
  </status>
  <cabinetUsageGetResult>
    <resultCode>0</resultCode>
    <MaxSpace>5000</MaxSpace>
    <FolderMax>500</FolderMax>
    <FileMax>2000</FileMax>
    <UseSpace>853.623</UseSpace>
    <AvailSpace>5119146.377</AvailSpace>
    <UseFolderCount>4</UseFolderCount>
    <AvailFolderCount>496</AvailFolderCount>
  </cabinetUsageGetResult>
</result>

 * 
 * 
 * */
function getUsage() {
  $responseBody = [];
  $authkey = base64_encode(RMS_SERVICE_SECRET . ':' . RMS_LICENSE_KEY);
  $header = array(
    "Content-Type: text/xml;charset=UTF-8",
    "Authorization: ESA {$authkey}",
  );

  // クエリストリングにそれぞれのパラメーターセット
  $url = RMS_API_CABINET_USAGE_GET;
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
    <title>cabinet.usage.get | CabinetAPI</title>
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

