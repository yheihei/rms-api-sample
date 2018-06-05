<?php

class Folder
{
  public $folderName; // 50バイト制限
  public $directoryName; // 20バイト制限 デフォルト値 ： [フォルダID]
  public $upperFolderId; //上位階層フォルダID 基本フォルダ0は指定不可
  
  function __construct() {
      
  }
    
}