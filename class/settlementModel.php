<?php

class SettlementModel {
  // xmlで自動生成するときに順番通りに要素が並ぶ
  // RMS APIは順番を組み替えると400が返ってくるので注意すること
  public $settlementName;
  public $cardModel; // CardModelオブジェクトが入る カード利用注文の場合には指定が必要。

  
  function __construct() {

  }
}