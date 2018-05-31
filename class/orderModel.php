<?php

class OrderModel {
  // xmlで自動生成するときに順番通りに要素が並ぶ
  // RMS APIは順番を組み替えると400が返ってくるので注意すること
  public $orderNumber;
  public $status; // 受注ステータス
  /* String
  ・新規受付
  ・発送前入金待ち
  ・発送待ち
  ・発送後入金待ち
  ・処理済
  ・保留
  ※または、店舗様設定独自ステータス
  */
  // 以下変更したいプロパティを必要であれば順番に追加する。
  // 仕様書を見るか、getOrder APIで返ってくる値を元に定義しよう
  public $seqId; // getOrderで使われている値をそのままセット
  public $orderDate;
  public $ordererModel; // PersonModelオブジェクトが入る
  public $settlementModel; // SettlementModelオブジェクトが入る
  public $deliveryModel; // DeliveryModelオブジェクトが入る 通常購入、予約商品、定期購入、頒布会、共同購入の場合に指定が必要。
  // public $pointModel;  // PointModelオブジェクトが入る ポイント利用注文の場合に指定が必要
  // public $rBankModel;
  // public $wrappingModel1;
  // public $wrappingModel2;
  public $packageModel; // PackageModelオブジェクトが入るarray
  // public $childOrderModel;
  // public $couponModel; // CouponModelオブジェクトが入るarray クーポン利用注文時に指定
  
  
  function __construct() {

  }
}