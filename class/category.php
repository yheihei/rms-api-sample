<?php

class Category {
  // xmlで自動生成するときに順番通りに要素が並ぶ
  // RMS APIは順番を組み替えると400が返ってくるので注意すること
  public $name; // 60バイト制限
  public $status; // 0:表示、1：非表示
  public $categoryWeight; // 優先度 有効範囲：1-999999999
  // public $categoryContent; // サポートしない
  // public $categoryLayout; // サポートしない
  // public $childCategories; // サポートしない
  
  function __construct() {

  }
}