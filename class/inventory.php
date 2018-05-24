<?php
class Inventory
{
  // xmlで自動生成するときに順番通りに要素が並ぶ
  // RMS APIは順番を組み替えると400が返ってくるので注意すること
  public $inventoryCount; //在庫数
  public $normalDeliveryDateId; // 在庫あり時納期管理番号
  public $backorderDeliveryDateId; // 在庫切れ時納期管理番号
  
  function __construct() {
      
  }
    
}