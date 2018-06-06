<?php

class CategoryInfo
{
  // xmlで自動生成するときに順番通りに要素が並ぶ
  // RMS APIは順番を組み替えると400が返ってくるので注意すること
  // public $categorySetManageNumber; // ※ メガショッププランの店舗のみ。本サンプルではサポートしない
  public $categoryId; // 設定したいカテゴリーのIDを入れる
  
  function __construct() {
      
  }
    
}