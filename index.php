<?php

//微信扫码支付示例

require_once './phpqrcode/phpqrcode.php';
require_once './WeixinPay/WeixinPay.php';
$weixin = new WeixinPay();
$order_params = [
    'body' => '扫码支付',
    'total_fee' => 1,
    'notify_url' => 'https://test.com/check-sign',
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






