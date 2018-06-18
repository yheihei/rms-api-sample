<?php

class CancelOrderRequestModel {
  // xmlで自動生成するときに順番通りに要素が並ぶ
  // RMS APIは順番を組み替えると400が返ってくるので注意すること
  public $requestId;
  public $cancelModelList; // CancelModelオブジェクトが入るarray
  
  function __construct() {

  }
}