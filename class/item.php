<?php

class Item
{
  // xmlで自動生成するときに順番通りに要素が並ぶ
  // RMS APIは順番を組み替えると400が返ってくるので注意すること
  public $itemUrl;
  public $itemNumber;
  public $itemName;
  public $itemPrice;
  public $genreId; //ディレクトリID
  public $catalogId; //カタログID(JAN)
  public $catalogIdExemptionReason;
  public $images; // 画像のリスト Imageクラスのオブジェクトをarrayで追加していく
  public $descriptionForPC;
  public $descriptionForMobile;
  public $descriptionForSmartPhone;
  public $catchCopyForPC;
  public $catchCopyForMobile;
  public $isIncludedPostage;
  public $isIncludedCashOnDeliveryPostage;
  public $postage;
  public $point; // Pointクラスのオブジェクトが入る
  public $itemInventory; // ItemInventoryクラスのオブジェクトが入る
  
  function __construct() {
      
  }
    
}