<?php

class OrderVatiationItem
{
  public $itemNumber;
  public $itemId;
  public $horizontalName; // 項目選択肢別在庫用横軸選択肢項目名
  public $horizontalValue; // 横軸に入っている値
  public $verticalName; // 項目選択肢別在庫用縦軸選択肢項目名
  public $verticalValue; // 縦軸に入っている値
  public $selectedChoice;
  function __construct($itemCode, $selectedChoice) {
    $this->itemNumber = $itemCode;
    $this->selectedChoice = $selectedChoice;
    /***
     * 
     * selecetedChoiceは下記のように取れる。1行目が横軸、2行目が縦軸の値である
     * [selectedChoice] => 媒体:Kindle\n
        色/サイズ:白/M
     * 
     * この情報から横軸名と縦軸名を取得する。
     * 
     * */
    $choices = str_replace("\n", ",", $this->selectedChoice); // \nを,に変更
    $choices = explode(",",$choices);
    
    // var_dump($choices);
    /**
     * この時点で$choicesはこうなっている
     * array(2) { [0] => string(13) "媒体:Kindle" [1] => string(19) "色/サイズ:白/M" }
     * */
    $this->horizontalName = explode(":", $choices[0], 2)[0]; // 横軸の"媒体"部分を取得
    $this->horizontalValue = explode(":", $choices[0], 2)[1]; // 横軸の"Kindle"部分を取得
    $this->itemNumber = str_replace($this->horizontalValue, "", $this->itemNumber); // 横軸の値をitemNumberから削除
    
    if( isset($choices[1]) ) {
      // 縦軸も存在する場合
      $this->verticalName = explode(":", $choices[1], 2)[0]; // 縦軸の"色/サイズ" 部分を取得
      $this->verticalValue = explode(":", $choices[1], 2)[1]; // 縦軸の"白/M" 部分を取得
      $this->itemNumber = str_replace($this->verticalValue, "", $this->itemNumber); // 縦軸の値をitemNumberから削除
    }
    
    $this->itemId = explode("-", $this->itemNumber)[1];
      
  }
    
}