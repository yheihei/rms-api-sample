<?php

class Item
{
  public $itemUrl = '';
  public $itemNumber = '';
  public $itemName = '';
  public $itemPrice = 0;
  public $itemInventoryType = -1; // 在庫タイプ
  public $normalDeliveryDateId; // 在庫あり時納期管理番号
  public $backorderDeliveryDateId; // 在庫切れ時納期管理番号
  // public $pointRate;
  // public $pointRateStart;
  // public $pointRateEnd;
  public $point;
  public $genreId; //ディレクトリID
  public $catalogId; //カタログID(JAN)
  public $catalogIdExemptionReason;
  public $catchCopyForPC = '';
  public $catchCopyForMobile = '';
  public $isIncludedPostage = 0;
  public $postage = -1;
  public $isIncludedCashOnDeliveryPostage = 0;
  public $inventoryCount = 0; //在庫数
  public $images = array(); // 画像のリスト Imageクラスのオブジェクトが入る
  public $descriptionForMobile = '';
  public $descriptionForSmartPhone = '';
  public $descriptionForPC = '';
  
  function __construct() {
      
  }
    
}