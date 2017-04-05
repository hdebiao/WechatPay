

[TOC]




## [微信支付开发文档](https://pay.weixin.qq.com/wiki/doc/api/index.html)

## 类的使用方法
笔者阅读微信支付SDK里面的example之后，把下单，查询订单，退款，查询退款等常用操作封装在一个类里面。

使用方法如下 : 
* 引入类文件
* 创建一个实例
* 填充参数
* 调用相关的方法
```
<?php
require_once './WeixinPay/WeixinPay.php';
$weixin = new WeixinPay();
$order_params = [
    'body' => '这是一个测试商品',
    'total_fee' => 1,
    'openid' => 'jfahfadhfuadhgahga-test',
    'notify_url' => 'http://test.com/check',
    'trade_type' => 'JSAPI',
    'out_trade_no' => 'test-'.date('YmdHis')
];
$weixin->setOrderParams($order_params);
$jsApiParameters = $weixin->getJsApiParameters();
```


## 微信授权登录
* 由于微信公众号支付需要用到用户的openid,所以有必要了解一下微信的授权登录

* 在网页的"微信网页授权"标签下 [微信公众平台开发文档](https://mp.weixin.qq.com/wiki)
 
获取用户信息的大致步骤如下 :

1. 引导用户打开指定的页面

```
https://open.weixin.qq.com/connect/oauth2/authorize?appid=APPID&redirect_uri=REDIRECT_URI&response_type=code&scope=SCOPE&state=STATE#wechat_redirect
```
注 :  redirect_uri 为跳转链接(需要url_encode处理)，可以在这个页面上获取到code参数
	  当scope= snsapi_base  时，通过获取到的code可以获得微信用户的基本信息。
      当scope= snsapi_userinfo 时 ，通过获取到的code可以获得微信用户的基本信息。

2. 在跳转页面中，通过code参数($_GET['code'])换取access_token 

3. 通过access_token 获取到用户的详细信息


### 获取微信用户的基本信息

* 生成供跳转的链接
```
<?php
require_once './WeixinPay/WeixinPay.php';
$weixin  =  new WeixinPay();

$weixin->setRedirectUrl('http://test.com/user.php');

$url = $weixin->createOauthUrlForCodeOfSnsapiBase();

```

* 拿到code参数后，通过接口获取到用户的基本信息
```
//user.php

<?php
require_once './WeixinPay/WeixinPay.php';

$code =  $_GET['code'];

$weixin  = new WeixinPay();

$weixin->setCode($code);

$userData = $weixin->getUserBase();
```

* 获取到的微信用户的基本信息
```
{
    "access_token": "ksL8nXGggLZY8tOKFFlOUWOa0PICM5-test",
    "expires_in": 7200,
    "refresh_token": "KmpJL_FJK74Q3uxGm444Y_hyEbTKlcP9ZI1cQ6ieOgeux8sMRDW1RKvRhdvxL6doT8YergsCUAZMcVOwzb3wmLx--test",
    "openid": "ydtfysayfgdasytest",
    "scope": "snsapi_base",
    "unionid": "oCqmrfsdfgghhh1hd8u1ST5AYtest"
}
```


### 获取微信用户的详细信息
* 生成链接
```
require_once './WeixinPay/WeixinPay.php';
$weixin = new WeixinPay();
$weixin->setRedirectUrl('http://test.com/userinfo.php');
$url = $weixin->createOauthUrlForCodeOfSnsapiUserInfo();
```
* 用户同意之后，拿到code，通过code获取到用户的详细信息
```
//userinfo.php

<?php
require_once './WeixinPay/WeixinPay.php';
$code =  $_GET['code'];
$weixin  = new WeixinPay();
$weixin->setCode($code);
$userData = $weixin->getUserInfo();
```
* 用户的详细信息
```
{
    "openid": "ydtfysayfgdasytest",
    "nickname": "test",
    "sex": 1,
    "language": "zh_CN",
    "city": "武汉",
    "province": "湖北",
    "country": "中国",
    "headimgurl": "http://wx.qlogo.cn/mmopen/ZR4W6lp2JvoCdw3CKqtFPdiczb9Utpjial621bnD3KI7V5WlJniaLxwLKfHqPvuLBvysaHLRgu5BjQhZ5Hibr4cq79DVOcCIRSQh/0",
    "privilege": [],
    "unionid": "oCqmrfsdfgghhh1hd8u1ST5AYtest"
}
```

### 获取用户的openid
```
<?php
require_once './WeixinPay/WeixinPay.php';
$code =  $_GET['code'];
$weixin  = new WeixinPay();
$weixin->setCode($code);
$openid = $weixin->getUserOpenid();
```

## 微信扫码支付
步骤 ：
* 填充支付信息参数
* 获取可用于扫码支付的链接
* 将获取到的链接转换成二维码
```
<?php
require_once './phpqrcode/phpqrcode.php';
require_once './WeixinPay/WeixinPay.php';
$weixin = new WeixinPay();
$order_params = [
    'body' => '扫码支付',
    'total_fee' => 1,
    'notify_url' => 'http://test.com/check',
    'out_trade_no' => 'test-' . date('YmdHis'),
    'product_id' => 10086
];

$weixin->setOrderParams($order_params);
$result = $weixin->qrCode();
header('Content-Type:image/png');
QRcode::png(
    $result['code_url'],
    false,
    QR_ECLEVEL_L,
    8,
    4
);
```

## 微信公众号支付
步骤 ：
* 获取微信用户的openid
* 填充支付参数
* 获取支付所需的字符串参数

```
<?php
require_once './WeixinPay/WeixinPay.php';
$weixin = new WeixinPay();
$order_params = [
    'body' => '这是一个测试商品',
    'total_fee' => 1,
    'openid' => 'jfahfadhfuadhgahga-test',
    'notify_url' => 'http://test.com/check',
    'trade_type' => 'JSAPI',
    'out_trade_no' => 'test-'.date('YmdHis')
];
$weixin->setOrderParams($order_params);
$jsApiParameters = $weixin->getJsApiParameters();
```


* 支付页面的html代码
```html
<html>
<head>
    <meta http-equiv="content-type" content="text/html;charset=utf-8"/>
    <meta name="viewport" content="width=device-width, initial-scale=1"/>
    <title>微信支付样例-支付</title>
    <script type="text/javascript">
        callpay();
        //调用微信JS api 支付
        function jsApiCall()
        {
            WeixinJSBridge.invoke(
                'getBrandWCPayRequest',
                <?php echo $jsApiParameters; ?>,
                function(res){
                    WeixinJSBridge.log(res.err_msg);
                    alert(res.err_code+res.err_desc+res.err_msg);
                }
            );
        }

        function callpay()
        {
            if (typeof WeixinJSBridge == "undefined"){
                if( document.addEventListener ){
                    document.addEventListener('WeixinJSBridgeReady', jsApiCall, false);
                }else if (document.attachEvent){
                    document.attachEvent('WeixinJSBridgeReady', jsApiCall);
                    document.attachEvent('onWeixinJSBridgeReady', jsApiCall);
                }
            }else{
                jsApiCall();
            }
        }
    </script>

</head>
<body>
<br/>
<font color="#9ACD32"><b>该笔订单支付金额为<span style="color:#f00;font-size:50px">1分</span>钱</b></font><br/><br/>
<div align="center">
    <button style="width:210px; height:50px; border-radius: 15px;background-color:#FE6714; border:0px #FE6714 solid; cursor: pointer;  color:white;  font-size:16px;" type="button" onclick="callpay()" >立即支付</button>
</div>
</body>
</html>
```


## app支付
```
<?php
require_once './WeixinPay/WeixinPay.php';
$weixin = new WeixinPay();
$order_params = [
    'body' => '这是一个测试商品',
    'total_fee' => 1,
    'notify_url' => 'http://test.com/check',
    'trade_type' => 'APP',
    'out_trade_no' => 'test-' . date('YmdHis')
];
$weixin->setOrderParams($order_params);
$data = $weixin->getAppParameters();
```
## 查询订单
步骤 ：
* 填充参数
* 调用查询订单方法

```
<?php
require_once './WeixinPay/WeixinPay.php';
$weixin = new WeixinPay();
$params = [
    'out_trade_no' => 'test'
];
$weixin->setOrderParams($params);
$result = $weixin->orderQuery();
```



## 关闭订单

* 填充参数

```
<?php
require_once './WeixinPay/WeixinPay.php';
$weixin = new WeixinPay();
$params = [
    'out_trade_no' => 'test'
];
$weixin->setOrderParams($params);
$result = $weixin->closeOrder();
```

## 退款
```
<?php
require_once './WeixinPay/WeixinPay.php';
$weixin = new WeixinPay();
$params = [
    'out_trade_no' => 'test'
];
$weixin->setOrderParams($params);
$result = $weixin->refund();
```

## 退款查询

```
<?php
require_once './WeixinPay/WeixinPay.php';
$weixin = new WeixinPay();
$params = [
    'out_trade_no' => 'test'
];
$weixin->setOrderParams($params);
$result = $weixin->refundQuery();
```



## 验证回调结果

* 代码
```
<?php
require_once './WeixinPay/WeixinPay.php';
$weixin = new WeixinPay();
$result = $weixin->checkSign();
if ($result['status'] === 'SUCCESS') {
    //签名验证成功
    //获取微信通知的数据(包含有订单号,金额等订单信息)
    $data = $result['data'];
    echo $result['response'];
} else {
    //签名验证失败
    echo $result['response'];
}

```


* 验签结果正确的时候
```
<xml>
  <return_code><![CDATA[SUCCESS]]></return_code>
  <return_msg><![CDATA[OK]]></return_msg>
</xml>
```

* 验签结果错误的时候返回
```
<xml>
  <return_code><![CDATA[FAIL]]></return_code>
  <return_msg><![CDATA[OK]]></return_msg>
</xml>
```



* 在lib/WxPay.Api.php 后面增加的代码（用于验证支付回调结果）
```
    public function checkSing()
    {
        //获取通知的数据
        $xml = $GLOBALS['HTTP_RAW_POST_DATA'];
        $weixin = new WxPayDataBase();
        $result = $weixin->FromXml($xml);
        $sign = $result['sign'];
        $correctResponse = ['return_code' => 'SUCCESS', 'return_msg' => 'OK'];
        $errorResponse = ['return_code' => 'FAIL', 'return_msg' => 'OK'];
        //对比签名数据
        if ($sign === $weixin->MakeSign()) {
            //返回正确的结果
            return ['status' => 'SUCCESS', 'response' => $this->arrayToXml($correctResponse),'data' => $result];
        } else {
            return ['status' => 'FAIL', 'response' => $this->arrayToXml($errorResponse)];
        }
    }


    /**
     * 数组转换成xml
     * @param $data
     * @return string
     */
    public function arrayToXml($data)
    {
        $xml = "<xml>";
        foreach ($data as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else {
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
            }
        }
        $xml .= "</xml>";
        return $xml;
    }
```
