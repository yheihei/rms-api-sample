# 概要
難解なことで有名なRakuten RMS Web APIのPHPのサンプル集です。色んなAPIを叩いてRMSのデータを取得したり更新できるようになります。
RMS作業自動化ツールなどのヒントになれば幸いです。

# 使い方
## config.phpを設定する

* config_sample.phpをconfig.phpにリネーム
* config.php内にAPIを使用するためのKey、店舗情報などを入力

## あとは下記APIごとのphpファイルをブラウザで開くとAPIのテストができます

# 商品API（ItemAPI）

## insertItem.php
item.insertのAPIを叩いて、ブラウザ上にリクエストと結果を表示  
下記を設定し、RMS店舗に商品を登録するサンプルです。  

* 商品名やURLなどの基本設定
* ディレクトリID、カタログID(JAN)の設定
* R-Cabinetにあげられてる画像を2枚設定
* ポイント変倍設定
* 送料など
* 納期管理番号の設定

## insertVariationItem.php
item.insertのAPIを叩いて、ブラウザ上にリクエストと結果を表示します  
在庫タイプ2：項目選択肢別在庫設定 を設定した際の新規商品登録のサンプルです。  
カラーとサイズを選ばせて、バリエーションごとに個別に在庫を設定しています。
設定項目は下記。

* 商品名やURLなどの基本設定
* ディレクトリID、カタログID(JAN)の設定
* R-Cabinetにあげられてる画像を2枚設定
* ポイント変倍設定
* 送料など
* バリエーションの値設定（カラー×サイズ）
* バリエーションパターンごとに個別に在庫・画像設定

## getItem.php
item.getのAPIを叩いて、ブラウザ上にリクエストと結果を表示  
商品のitemUrlを指定して、その商品の情報を取得してくるサンプルです。  
RMS管理画面から色々情報設定して、その商品を取得すると、xmlがどういう構造になっているかわかりやすくていいです。  
表示されたxmlを下記にぶち込むと、整形してくれるので尚わかりやすい。    
http://tm-webtools.com/Tools/XMLBeauty

## 受注API

coming soon...

## 決済API

coming soon...