<?php
class ItemInventory
{
  // xmlで自動生成するときに順番通りに要素が並ぶ
  // RMS APIは順番を組み替えると400が返ってくるので注意すること
  public $inventoryType; // 在庫タイプ
  public $inventories; // Inventoryクラスのオブジェクトをarrayで追加していく
  public $verticalName; // 項目選択肢別在庫用縦軸選択肢項目名
  public $horizontalName; // 項目選択肢別在庫用横軸選択肢項目名
  public $inventoryQuantityFlag; // 在庫数表示 在庫タイプが「1：通常在庫設定」の時のみ設定できる
  public $inventoryDisplayFlag; // 項目選択肢別在庫用残り表示閾値 在庫タイプが「2：項目選択肢別在庫設定 」の時のみ設定できる -1で表示  0で表示しない 1以上で△表示
  function __construct() {
      
  }
    
}