<?php
class ItemInventory
{
  // xmlで自動生成するときに順番通りに要素が並ぶ
  // RMS APIは順番を組み替えると400が返ってくるので注意すること
  public $inventoryType; // 在庫タイプ
  public $inventories; // Inventoryクラスのオブジェクトをarrayで追加していく
  public $inventoryQuantityFlag;
  
  function __construct() {
      
  }
    
}