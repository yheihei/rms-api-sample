<?php

class CabinetFileSetting
{
  public $fileName; //登録画像名 50バイト以内。その他制約あり。要マニュアル確認
  public $folderId; //登録先フォルダID。 0だと基本フォルダ。その他はcabinet.folders.get APIで調べられる
  public $filePath; //登録file名 20バイト以内。その他制約あり。要マニュアル確認
  public $overWrite; //overWriteがtrueかつfilePathの指定がある場合、filePathをキーとして画像情報を上書きすることができます。
  
  function __construct() {
      
  }
    
}