<?php

class CategoryInsertRequest {
  // xmlで自動生成するときに順番通りに要素が並ぶ
  // RMS APIは順番を組み替えると400が返ってくるので注意すること
  public $categorySetManageNumber; //※ TODOメガショッププランを契約している場合は、必須項目となります
  public $categoryId; // どのカテゴリーの配下に置くか。ルートの場合、0か指定しない
  public $category; // Categoryクラスオブジェクトが入る
  
  function __construct() {

  }
}