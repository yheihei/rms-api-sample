<?php

class PackageModel {
  // xmlで自動生成するときに順番通りに要素が並ぶ
  // RMS APIは順番を組み替えると400が返ってくるので注意すること
  public $basketId; //通常注文、オークション注文の場合、指定が必要。
  public $senderModel; // PersonModelオブジェクトが入る
  public $itemModel; // ItemModelオブジェクトがのarrayが入る
  
  function __construct() {

  }
}