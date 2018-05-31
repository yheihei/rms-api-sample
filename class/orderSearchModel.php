<?php

class OrderSearchModel {
  // xmlで自動生成するときに順番通りに要素が並ぶ
  // RMS APIは順番を組み替えると400が返ってくるので注意すること
  public $dateType; // 1:注文日
  public $startDate;
  public $endDate;
  public $orderType = array(1,2,3,4,5,6); //販売種別全て　
  /*
  1	通常購入	　
  2	オークション	　
  3	共同購入	　
  4	定期購入	　
  5	頒布会	　
  6	予約商品
  */
  
  function __construct() {

  }
}