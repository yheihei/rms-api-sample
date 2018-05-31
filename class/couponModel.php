<?php

class CouponModel {
  // xmlで自動生成するときに順番通りに要素が並ぶ
  // RMS APIは順番を組み替えると400が返ってくるので注意すること
  public $couponCode;
  public $itemId;
  public $couponUnit;
  public $couponCapital;
  public $couponPrice;
  
  function __construct() {

  }
}