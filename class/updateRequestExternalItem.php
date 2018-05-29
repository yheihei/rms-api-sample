<?php

class UpdateRequestExternalItem
{
  // xmlで自動生成するときに順番通りに要素が並ぶ
  // RMS APIは順番を組み替えると400が返ってくるので注意すること
  public $itemUrl;
  public $inventoryType;
  public $restTypeFlag;
  public $HChoiceName;
  public $VChoiceName;
  public $orderFlag;
  public $nokoriThreshold;
  public $inventoryUpdateMode;
  public $inventory;
  public $inventoryBackFlag;
  public $normalDeliveryDeleteFlag;
  public $normalDeliveryId;
  public $lackDeliveryDeleteFlag;
  public $lackDeliveryId;
  public $orderSalesFlag;
  
  function __construct() {

  }
    
}