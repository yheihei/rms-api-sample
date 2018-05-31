<?php

class OrderStatusModel {
  // xmlで自動生成するときに順番通りに要素が並ぶ
  // RMS APIは順番を組み替えると400が返ってくるので注意すること
  public $orderNumber; // Stringオブジェクトが入るarray
  public $statusName; // 入る文字列は下記
  /*
  ・新規受付
  ・発送前入金待ち
  ・発送待ち
  ・発送後入金待ち
  ・処理済
  ・保留
  ※または、店舗様設定独自ステータス
  */
  
  function __construct() {

  }
}