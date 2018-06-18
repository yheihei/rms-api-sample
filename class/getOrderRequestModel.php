<?php

class GetOrderRequestModel
{
  // xmlで自動生成するときに順番通りに要素が並ぶ
  // RMS APIは順番を組み替えると400が返ってくるので注意すること
  public $isOrderNumberOnlyFlg;
  public $orderNumber; // 受注番号指定で取得したい場合
  public $orderSearchModel; // orderSearchModel(受注検索モデル)オブジェクトをセットする
  
  
  function __construct() {

  }
    
}