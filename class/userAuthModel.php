<?php

class UserAuthModel {
  
  public $userName;
  public $shopUrl;
  public $authKey; // base64_encode(RMS_SERVICE_SECRET . ':' . RMS_LICENSE_KEY);
  
  function __construct() {

  }
}