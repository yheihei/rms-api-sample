# 概要
難解なことで有名なRakuten RMS Web APIのPHPのサンプル集です。
色んなAPIを叩いてRMSのデータを取得したり更新できるようになります。  

極力特別なパッケージは使用せず、デフォルトのPHPの機能で動作させる様に心がけています。
RMSを使ったツール開発のヒントになれば幸いです。

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
商品にカラーとサイズを設定して、バリエーションごとに個別に在庫を設定しています。
設定項目は下記。

* 商品名やURLなどの基本設定
* ディレクトリID、カタログID(JAN)の設定
* R-Cabinetにあげられてる画像を2枚設定
* ポイント変倍設定
* 送料など
* バリエーションの値設定（カラー×サイズ）
* バリエーションパターンごとに個別に在庫・画像設定

## updateItem.php
item.updateのAPIを叩いて、ブラウザ上にリクエストと結果を表示します  
下記を更新できることを確認しています。

* 商品名やURLなどの基本設定
* ディレクトリID、カタログID(JAN)の設定
* R-Cabinetにあげられてる画像を2枚設定
* ポイント変倍設定
* 送料など
* 納期管理番号の設定

## updateVariationItem.php
item.updateのAPIを叩いて、ブラウザ上にリクエストと結果を表示します  
在庫タイプ2：項目選択肢別在庫設定 を設定した際の新規商品登録のサンプルです。  
商品にカラーとサイズを設定して、バリエーションごとに個別に在庫を更新しています。  
※ 本APIではバリエーション単一で在庫更新はできません。必ず全てのバリエーションに対して在庫を設定して投げる必要があります。  
※ 一つだけバリエーションを設定して更新すると、他のバリエーションが消えます。  
※ バリエーション単一で更新する場合は在庫APIを使う必要があります  

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

# R-CabinetAPI（CabinetAPI）

ドラフト作成。実動作不可。現在楽天に問い合わせ中。

# 受注API(OrderAPI)

＊SOAP Clientを使ってSOAP形式でPOSTをしています。お使いのサーバーでphp_info()などをechoしてSOAP Clientがenableになっているか確認してください。
SOAP Clientが使えない場合、xmlを独自に構築してPOSTする必要があります。xmlの作例例もサンプルコードの中に含んでおりますので、ご確認ください。


## getOrder.php
getOrder APIを用いて、受注商品情報を取得する。  
処理の流れは
1. GetOrderRequestModelに取得したい受注の条件を記載
2. それをAPIコールする関数getOrder()に渡す

## changeStatus.php
changeStatus APIを用いて、受注のステータスを変更する
例えば、下記のようなものを文字列で指定。

```
  ・新規受付
  ・発送前入金待ち
  ・発送待ち
  ・発送後入金待ち
  ・処理済
  ・保留
  ※または、店舗様設定独自ステータス

```

処理の流れは下記。

1. getRequestId APIを用いて、非同期処理のリクエストIDを取得
2. 取得したリクエストIDを用いてchangeStatus APIを叩き、特定の受注番号のステータスを変更

なお、changeStatus APIは非同期処理のため、処理結果を取得するにはリクエストIDを用いて
getResult APIを叩く必要がある。

## updateOrder.php
updateOrder APIを用いて、受注情報を更新する。処理の流れは下記。

1. getRequestId APIを用いて、非同期処理のリクエストIDを取得
2. 取得したリクエストIDを用いてupdateOrder APIを叩き、特定の受注番号の情報を変更
(changeStatus相当の動作も仕様書上はできるようだが、動作確認は未。受注ステータスを変更するだけならchangeStatusを使ってください)

本サンプルでは愚直にクラスを作って一つ一つ入れていきましたが、  
必須項目が50項目以上ありチマチマ入れるのは得策ではありません。  
getOrderで得られるxmlの<orderModel>配下をパースしてarrayにし、  
それを$updateOrderRequestModel->orderModelのarrayに挿入して、  
本ファイルのupdateOrder関数にぶち込むことをオススメします  

## getResult.php
getResultのAPIを叩いて、ブラウザ上にリクエストと結果を表示  
OrderAPI全般の非同期APIを叩く際に使ったリクエストIDを入れて本APIを叩くと、非同期処理結果がどうなったかが返却される。  
例えば、下記のような形で返却される。レスポンスをvar_dumpしている。

```

cclass stdClass#3 (1) {
  public $return =>
  class stdClass#4 (3) {
    public $errorCode =>
    string(7) "N00-000"
    public $message =>
    string(12) "正常終了"
    public $asyncResultModel =>
    array(2) {
      [0] =>
      class stdClass#5 (9) {
        public $complete =>
        string(24) "338459-20180531-00000720"
        public $count =>
        int(1)
        public $errorCode =>
        string(7) "N00-000"
        public $kind =>
        int(1)
        public $message =>
        string(12) "正常終了"
        public $requestId =>
        int(584649535)
        public $startDate =>
        string(25) "2018-05-31T16:45:19+09:00"
        public $status =>
        int(3)
        public $timeStamp =>
        string(25) "2018-05-31T16:45:48+09:00"
      }
      [1] =>
      class stdClass#6 (9) {
        public $count =>
        int(1)
        public $errorCode =>
        string(7) "W00-000"
        public $kind =>
        int(4)
        public $message =>
        string(15) "エラーあり"
        public $requestId =>
        int(584618680)
        public $startDate =>
        string(25) "2018-05-31T16:17:53+09:00"
        public $status =>
        int(3)
        public $timeStamp =>
        string(25) "2018-05-31T16:19:05+09:00"
        public $unitError =>
        class stdClass#7 (3) {
          public $errorCode =>
          string(7) "E04-011"
          public $message =>
          string(48) "更新対象の商品情報が存在しません"
          public $orderKey =>
          string(24) "338459-20180531-00000726"
        }
      }
    }
  }
}

```


# 在庫API（InventoryAPI）

＊SOAP Clientを使ってSOAP形式でPOSTをしています。お使いのサーバーでphp_info()などをechoしてSOAP Clientがenableになっているか確認してください。
SOAP Clientが使えない場合、xmlを独自に構築してPOSTする必要があります。xmlの作例例もサンプルコードの中に含んでおりますので、ご確認ください。


## updateVariationInventory.php
updateInventoryExternalのAPIを叩いて、在庫タイプが項目選択肢別在庫設定の場合の在庫を横軸縦軸単位で変更するAPIサンプル。
例えば、「Sサイズ/赤」の商品だけ在庫数を50にするなどの操作ができる。  
数あるバリエーションの中の一部バリエーション商品の在庫数をいじる場合はこれを使う。  

＊item.updateでもできるが、item.updateだと全てのバリエーションに在庫数をセットして更新しないといけない。  

## getInventoryExternal.php
getInventoryExternalのAPIを叩いて、商品URLを指定して在庫を取得するAPIサンプル。  
商品のitemUrlをリストに詰めてコールすると、在庫タイプ：通常/項目選択肢別の全ての在庫数などの情報を返してくれる。



# 決済API

＊SOAP Clientを使ってSOAP形式でPOSTをしています。お使いのサーバーでphp_info()などをechoしてSOAP Clientがenableになっているか確認してください。
SOAP Clientが使えない場合、xmlを独自に構築してPOSTする必要があります。xmlの作例例もサンプルコードの中に含んでおりますので、ご確認ください。

## authori.php
authoriのAPIを叩いて、ブラウザ上にリクエストと結果を表示  
カードステータスが「1(受注したが決済に関して何もしていない状態？楽天に定義がない)」の受注番号を指定し、カードステータスを「オーソリ済み」にする非同期API。  
処理の結果はすぐには反映されず、authori前にgetRCCSRequestId APIで取得したリクエストIDを元に  
getRCCSResultで取得する必要がある。カードステータスは、受注APIのgetOrderで返却される受注商品のcardStatusでも確認できる。

## authoriCancel.php
authoriCancelのAPIを叩いて、ブラウザ上にリクエストと結果を表示  
カードステータスが「オーソリ済」の受注番号を指定し、カードステータスを「オーソリ取消済み」にする非同期API。  
処理の結果はすぐには反映されず、authoriCancel前にgetRCCSRequestId APIで取得したリクエストIDを元に  
getRCCSResultで取得する必要がある。カードステータスは、受注APIのgetOrderで返却される受注商品のcardStatusでも確認できる。

## sales.php
salesのAPIを叩いて、ブラウザ上にリクエストと結果を表示  
カードステータスが「オーソリ済」の受注番号を指定し、カードステータスを「売上請求済み」にする非同期API。  
処理の結果はすぐには反映されず、sales前にgetRCCSRequestId APIで取得したリクエストIDを元に  
getRCCSResultで取得する必要がある。カードステータスは、受注APIのgetOrderで返却される受注商品のcardStatusでも確認できる。

## salesCancel.php
salesCancelのAPIを叩いて、ブラウザ上にリクエストと結果を表示  
カードステータスが「売上請求済み」の受注番号を指定し、カードステータスを「売上請求取消済み」にする非同期API。  
処理の結果はすぐには反映されず、salesCancel前にgetRCCSRequestId APIで取得したリクエストIDを元に  
getRCCSResultで取得する必要がある。カードステータスは、受注APIのgetOrderで返却される受注商品のcardStatusでも確認できる。

## getRCCSResult.php
getRCCSResultのAPIを叩いて、ブラウザ上にリクエストと結果を表示  
決済API全般の非同期APIを叩く際に使ったリクエストIDを入れて本APIを叩くと、非同期処理結果がどうなったかが返却される。  
例えば、下記のような形で返却される。レスポンスをvar_dumpしている。

```

class stdClass#3 (1) {
  public $result =>
  class stdClass#4 (3) {
    public $errorCode =>
    string(12) "RCCS_N00-000"
    public $message =>
    string(12) "正常終了"
    public $rccsRequestStatus =>
    class stdClass#5 (1) {
      public $UiRCCSRequestStatusModel =>
      class stdClass#6 (9) {
        public $count =>
        int(1)
        public $errorCode =>
        string(12) "RCCS_N00-000"
        public $kind =>
        int(1)
        public $message =>
        string(12) "正常終了"
        public $rccsResults =>
        class stdClass#7 (1) {
          public $UiRCCSResultModel =>
          class stdClass#8 (19) {
            public $approvalNumber =>
            string(7) "0000000"
            public $brandName =>
            string(4) "VISA"
            public $cardImstCount =>
            int(0)
            public $cardNo =>
            string(19) "****-****-****-6941"
            public $cardStatus =>
            int(13)
            public $ccsErrorCode =>
            string(6) "210G55"
            public $ccsErrorCodeDetail =>
            NULL
            public $companyName =>
            string(15) "楽天カード"
            public $errorCode =>
            string(12) "RCCS_E22-822"
            public $eventDate =>
            string(25) "2018-05-31T00:00:00+09:00"
            public $expYM =>
            string(7) "2020/03"
            public $helpItem =>
            string(0) ""
            public $message =>
            string(105) "限度額オーバー：R-Card Plus クレジットサポートセンターへお問合せください。"
            public $orderNumber =>
            string(24) "338459-20180531-00000703"
            public $ownerName =>
            string(12) "TARO RAKUTEN"
            public $payType =>
            int(0)
            public $price =>
            int(208)
            public $regDate =>
            string(25) "2018-05-31T10:10:14+09:00"
            public $transactionId =>
            string(32) "18053110094638800185672603000100"
          }
        }
        public $requestId =>
        int(167316941)
        public $startDate =>
        string(25) "2018-05-31T10:09:43+09:00"
        public $status =>
        int(3)
        public $timeStamp =>
        string(25) "2018-05-31T10:10:14+09:00"
      }
    }
  }
}

```
