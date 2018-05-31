<?php

class PersonModel {
  // xmlで自動生成するときに順番通りに要素が並ぶ
  // RMS APIは順番を組み替えると400が返ってくるので注意すること
  public $zipCode1;
  public $zipCode2;
  public $prefecture;
  public $city;
  public $subAddress;
  public $familyName;
  public $phoneNumber1;
  public $phoneNumber2;
  public $phoneNumber3;
  public $emailAddress;
  
  
  function __construct() {

  }
}