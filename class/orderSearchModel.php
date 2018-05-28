<?php

class OrderSearchModel {
  // xmlで自動生成するときに順番通りに要素が並ぶ
  // RMS APIは順番を組み替えると400が返ってくるので注意すること
  public $dateType; // 1:注文日
  public $startDate;
  public $endDate;
  public $orderType; //販売種別
  
  function __construct() {

  }
}