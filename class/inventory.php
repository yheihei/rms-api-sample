<?php
class Inventory
{
  // xmlで自動生成するときに順番通りに要素が並ぶ
  // RMS APIは順番を組み替えると400が返ってくるので注意すること
  public $inventoryCount; //在庫数
  public $childNoVertical; // 項目選択肢別在庫設定 時のバリエーションの縦軸
  public $childNoHorizontal; // 項目選択肢別在庫設定 時のバリエーションの横軸
  public $optionNameVertical;
  public $optionNameHorizontal;
  public $normalDeliveryDateId; // 在庫あり時納期管理番号
  public $backorderDeliveryDateId; // 在庫切れ時納期管理番号
  public $isRestoreInventoryFlag; // 在庫戻し設定 キャンセル時に在庫をもどすか
  public $images; // SKU画像情報リスト InventoryImageクラスのオブジェクトをarrayで追加していく
  
  function __construct() {
      
  }
    
}