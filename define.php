<?php

// 楽天API エンドポイント関連
define("RMS_API_ITEM_INSERT", 'https://api.rms.rakuten.co.jp/es/1.0/item/insert');
define("RMS_API_ITEM_UPDATE", 'https://api.rms.rakuten.co.jp/es/1.0/item/update');
define("RMS_API_ITEM_GET", 'https://api.rms.rakuten.co.jp/es/1.0/item/get');
define("RMS_API_CABINET_USAGE_GET", 'https://api.rms.rakuten.co.jp/es/1.0/cabinet/usage/get');
define("RMS_API_CABINET_FOLDERS_GET", 'https://api.rms.rakuten.co.jp/es/1.0/cabinet/folders/get');
define("RMS_API_CABINET_FOLDERS_INSERT", 'https://api.rms.rakuten.co.jp/es/1.0/cabinet/folder/insert');
define("RMS_API_CABINET_FILES_SEARCH", 'https://api.rms.rakuten.co.jp/es/1.0/cabinet/files/search');
define("RMS_API_CABINET_FILE_INSERT", 'https://api.rms.rakuten.co.jp/es/1.0/cabinet/file/insert');
define("RMS_API_CABINET_FILE_UPDATE", 'https://api.rms.rakuten.co.jp/es/1.0/cabinet/file/update');
define("RMS_API_CABINET_FILE_DELETE", 'https://api.rms.rakuten.co.jp/es/1.0/cabinet/file/delete');
define("RMS_API_ORDER_GET", 'https://api.rms.rakuten.co.jp/es/1.0/order/ws');
define("RMS_API_ORDER_SOAP_WSDL", 'https://api.rms.rakuten.co.jp/es/1.0/order/ws?WSDL');
define("RMS_API_INVENTORY_SOAP_ADDRESS", 'https://api.rms.rakuten.co.jp/es/1.0/inventory/ws');
define("RMS_API_INVENTORY_SOAP_WSDL", 'https://inventoryapi.rms.rakuten.co.jp/rms/mall/inventoryapi');
define("RMS_API_PAYMENT_SOAP_WSDL", 'https://orderapi.rms.rakuten.co.jp/rccsapi-services/RCCSApiService?wsdl');
define("RMS_API_CATEGORY_SETS_GET", 'https://api.rms.rakuten.co.jp/es/1.0/categoryapi/shop/categorysets/get');
define("RMS_API_CATEGORIES_GET", 'https://api.rms.rakuten.co.jp/es/1.0/categoryapi/shop/categories/get');
define("RMS_API_CATEGORY_GET", 'https://api.rms.rakuten.co.jp/es/1.0/categoryapi/shop/category/get');
define("RMS_API_CATEGORY_INSERT", 'https://api.rms.rakuten.co.jp/es/1.0/categoryapi/shop/category/insert');
define("RMS_API_CATEGORY_UPDATE", 'https://api.rms.rakuten.co.jp/es/1.0/categoryapi/shop/category/update');
define("RMS_API_CATEGORY_DELETE", 'https://api.rms.rakuten.co.jp/es/1.0/categoryapi/shop/category/delete');
define("RMS_API_RAKUTEN_PAY_GET_ORDER", 'https://api.rms.rakuten.co.jp/es/2.0/order/getOrder/');
define("RMS_API_RAKUTEN_PAY_SEARCH_ORDER", 'https://api.rms.rakuten.co.jp/es/2.0/order/searchOrder/');

// 商品登録(ItemAPI)設定関連
define("RMS_CATALOG_EXCEPTION_REASON_NO_JAN", 5);

define("RMS_IMAGE_BASE_URL", "https://image.rakuten.co.jp/");

// 在庫設定関連
define("RMS_ITEM_INVENTORY_TYPE_NORMAL", 1); // 通常在庫設定
define("RMS_ITEM_INVENTORY_TYPE_VARIATION", 2); // 項目選択肢別在庫設定 

// 受注関連設定
define("RMS_GET_ORDER_DATE_TYPE_ORDER", 1); // 注文日で取得


