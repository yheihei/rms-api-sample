<?php

class CabinetUploadFileInfo
{
  public $filePath;
  public $mimeType;
  // public $pathInfo;  //Array([dirname] => ./hoge,[basename] => hoge.jpg,[extension] => jpg,[filename] => hoge)
  public $extension; // 拡張子
  
  function __construct($filePath) {
      $this->filePath = $filePath;
      $this->mimeType = mime_content_type($this->filePath); // image/jpegみたいなやつ
      $extensionArrayDef = array(
            'gif' => 'image/gif',
            'jpg' => 'image/jpeg',
            'png' => 'image/png',
            'tiff' => 'image/tiff',
            'bmp' => 'image/bmp',
            );
      //MIMEタイプから拡張子を決定
      $this->extension = array_search($this->mimeType, $extensionArrayDef,true);
  }
    
}