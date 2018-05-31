<?php

class ItemModel {
  // xmlで自動生成するときに順番通りに要素が並ぶ
  // RMS APIは順番を組み替えると400が返ってくるので注意すること
  public $basketId; //通常注文、オークション注文の場合、指定が必要。
  public $itemId; // 商品情報(値段等)を変更する場合、もしくは商品を削除する場合には指定が必要。
  public $itemName;
  public $itemNumber;
  public $price;
  public $units;
  public $isIncludedPostage;
  public $isIncludedTax;
  public $isIncludedCashOnDeliveryPostage;
  public $restoreInventoryFlag; //個数が変更となる場合に指定が必要。
  // public $normalItemModel;
  // public $saItemModel;
  // public $gbuyItemModel;
  
  
  
  function __construct() {

  }
}