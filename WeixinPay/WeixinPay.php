<?php

include_once __DIR__ . '/lib/WxPay.Api.php';
include_once __DIR__ . '/lib/WxPay.Notify.php';

class WeixinPay
{


    public $redirectUrl = ''; //跳转链接
    public $code;

    public $values = array();


    //设定参数
    public function setOrderParams($params)
    {
        if (array_key_exists('body', $params)) {
            $this->values['body'] = $params['body'];
        }
        if (array_key_exists('attach', $params)) {
            $this->values['attach'] = $params['attach'];
        }
        if (array_key_exists('out_trade_no', $params)) {
            $this->values['out_trade_no'] = $params['out_trade_no'];
        }
        if (array_key_exists('total_fee', $params)) {
            $this->values['total_fee'] = $params['total_fee'];
        }
        if (array_key_exists('goods_tag', $params)) {
            $this->values['goods_tag'] = $params['goods_tag'];
        }
        if (array_key_exists('notify_url', $params)) {
            $this->values['notify_url'] = $params['notify_url'];
        }
        if (array_key_exists('trade_type', $params)) {
            $this->values['trade_type'] = $params['trade_type'];
        }
        if (array_key_exists('openid', $params)) {
            $this->values['openid'] = $params['openid'];
        }

        if (array_key_exists('bill_date', $params)) {
            $this->values['bill_date'] = $params['bill_date'];
        }

        if (array_key_exists('bill_type', $params)) {
            $this->values['bill_type'] = $params['bill_type'];
        }

        if (array_key_exists('bill_tar_type', $params)) {
            $this->values['bill_tar_type'] = $params['bill_tar_type'];
        }
        if (array_key_exists('product_id', $params)) {
            $this->values['product_id'] = $params['product_id'];
        }
    }

    public function setRedirectUrl($url)
    {
        $this->redirectUrl = $url;
    }


    public function getRedirectUrl()
    {
        return $this->redirectUrl;
    }

    public function setCode($code)
    {
        $this->code = $code;
    }

    public function getCode()
    {
        return $this->code;
    }

    private function getAppId()
    {
        return \WxPayConfig::APPID;
    }


    private function getAppSecret()
    {
        return \WxPayConfig::APPSECRET;
    }


    //公众号支付
    public function getJsApiParameters()
    {

        $UnifiedOrderResult = $this->createWechatOrder();

        $jsApi = new \WxPayJsApiPay();
        $jsApi->SetAppid($UnifiedOrderResult['appid']);
        $timeStamp = time();
        $jsApi->SetTimeStamp("$timeStamp");
        $jsApi->SetNonceStr(\WxPayApi::getNonceStr());
        $jsApi->SetPackage("prepay_id=" . $UnifiedOrderResult['prepay_id']);
        $jsApi->SetSignType('MD5');
        $jsApi->SetPaySign($jsApi->MakeSign());
        $parameters = json_encode($jsApi->GetValues());
        return $parameters;
    }


    //扫码支付 　采用模式二
    public function qrCode()
    {
        $this->setOrderParams(['trade_type' => 'NATIVE']);
        $UnifiedOrderResult = $this->createWechatOrder();
        error_log(json_encode($UnifiedOrderResult));
        $result = ['error' => 'error'];
        if (is_array($UnifiedOrderResult) && array_key_exists('return_code',
                $UnifiedOrderResult) && $UnifiedOrderResult['return_code'] === 'SUCCESS'
        ) {
            $result = ['code_url' => $UnifiedOrderResult['code_url']];
        }
        return $result;
    }


    //APP支付
    public function getAppParameters()
    {
        $UnifiedOrderResult = $this->createWechatOrder();

        if (is_array($UnifiedOrderResult) && array_key_exists('return_code',
                $UnifiedOrderResult) && $UnifiedOrderResult['return_code'] === 'SUCCESS'
        ) {
            $jsApi = new \WxPayJsApiPay();
            $jsApi->SetAppid($UnifiedOrderResult['appid']);
            $timeStamp = time();
            $NonceStr = \WxPayApi::getNonceStr();
            $jsApi->SetTimeStamp("$timeStamp");
            $jsApi->SetNonceStr($NonceStr);
            $jsApi->SetPackage("prepay_id=" . $UnifiedOrderResult['prepay_id']);
            $jsApi->SetSignType('MD5');
            $jsApi->SetPaySign($jsApi->MakeSign());
            $sing = $jsApi->GetPaySign();
            //这是要返回给app　的参数
            $result = [
                'appid' => $UnifiedOrderResult['appid'],
                'partnerid' => $UnifiedOrderResult['mch_id'],
                'prepayid' => $UnifiedOrderResult['prepay_id'],
                'noncestr' => $NonceStr,
                'timestamp' => $timeStamp,
                'sign' => $sing
            ];

        } else {
            $result = ['error' => 'error'];
        }

        return $result;
    }


    //订单查询
    public function orderQuery()
    {
        $input = new \WxPayOrderQuery();
        $input->SetOut_trade_no($this->values['out_trade_no']);
        return \WxPayApi::orderQuery($input);
    }

    //退款
    public function refund()
    {
        $input = new \WxPayRefund();
        $input->SetOut_trade_no($this->values['out_trade_no']);
        return \WxPayApi::refund($input);
    }


    //退款查询
    public function refundQuery()
    {
        $input = new \WxPayRefundQuery();
        $input->SetOut_trade_no($this->values['out_trade_no']);
        return \WxPayApi::refundQuery($input);
    }

    //关闭订单
    public function closeOrder()
    {
        $input = new \WxPayCloseOrder();
        $input->SetOut_trade_no($this->values['out_trade_no']);
        return \WxPayApi::closeOrder($input);
    }


    //下载微信对账单
    public function downloadBill()
    {
        $input = new \WxPayDownloadBill();
        $input->SetBill_date($this->values['bill_date']);
        $input->SetBill_type($this->values['bill_type']);
        return \WxPayApi::downloadBill($input);
    }


    //至少有一个统一下单接口
    public function createWechatOrder()
    {
        $input = new \WxPayUnifiedOrder();
        if (array_key_exists('body', $this->values)) {
            $input->SetBody($this->values['body']);
        }
        if (array_key_exists('attach', $this->values)) {
            $input->SetAttach($this->values['attach']);
        }
        if (array_key_exists('out_trade_no', $this->values)) {
            $input->SetOut_trade_no($this->values['out_trade_no']);
        }
        if (array_key_exists('total_fee', $this->values)) {
            $input->SetTotal_fee($this->values['total_fee']);
        }
        if (array_key_exists('goods_tag', $this->values)) {
            $input->SetGoods_tag($this->values['goods_tag']);
        }
        if (array_key_exists('notify_url', $this->values)) {
            $input->SetNotify_url($this->values['notify_url']);
        }
        if (array_key_exists('trade_type', $this->values)) {
            $input->SetTrade_type($this->values['trade_type']);
        }
        if (array_key_exists('openid', $this->values)) {
            $input->SetOpenid($this->values['openid']);
        }
        if (array_key_exists('product_id', $this->values)) {
            $input->SetProduct_id($this->values['product_id']);
        }
        $order = \WxPayApi::unifiedOrder($input);
        return $order;
    }


    //验证回调结果 
    public function checkSign()
    {
        $api = new \WxPayApi();

        return $api->checkSing();
    }


    //获取微信普通授权登录链接
    public function createOauthUrlForCodeOfSnsapiBase()
    {
        $urlObj["appid"] = $this->getAppId();
        $urlObj["redirect_uri"] = urlencode($this->redirectUrl);
        $urlObj["response_type"] = "code";
        $urlObj["scope"] = "snsapi_base";
        $urlObj["state"] = "STATE" . "#wechat_redirect";
        $bizString = $this->ToUrlParams($urlObj);
        return "https://open.weixin.qq.com/connect/oauth2/authorize?" . $bizString;
    }

    //获取微信高级授权登录链接
    public function createOauthUrlForCodeOfSnsapiUserInfo()
    {
        $urlObj["appid"] = $this->getAppId();
        $urlObj["redirect_uri"] = urlencode($this->redirectUrl);
        $urlObj["response_type"] = "code";
        $urlObj["scope"] = "snsapi_userinfo";
        $urlObj["state"] = "STATE" . "#wechat_redirect";
        $bizString = $this->ToUrlParams($urlObj);
        return "https://open.weixin.qq.com/connect/oauth2/authorize?" . $bizString;
    }


    /**
     *    作用：生成可以获得access_token的url
     */
    public function createOauthUrlForOpenid()
    {
        $urlObj["appid"] = $this->getAppId();
        $urlObj["secret"] = $this->getAppSecret();
        $urlObj["code"] = $this->code;
        $urlObj["grant_type"] = "authorization_code";
        $bizString = $this->ToUrlParams($urlObj);
        return "https://api.weixin.qq.com/sns/oauth2/access_token?" . $bizString;
    }


    //获取用户的基本信息　openid union_id
    public function getUserBase()
    {
        $url = $this->createOauthUrlForOpenid();

        //初始化curl
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //运行curl，结果以jason形式返回
        $res = curl_exec($ch);
        curl_close($ch);
        return json_decode($res, true);

    }

    //获取用户的详细信息  通过　access_token 和 openId
    public function getUserInfo()
    {
        $url = $this->createOauthUrlForOpenid();

        //初始化curl
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //运行curl，结果以jason形式返回
        $res = curl_exec($ch);
        $data = json_decode($res, true);
        curl_close($ch);

        $url = 'https://api.weixin.qq.com/sns/userinfo?access_token=' . $data['access_token'] . '&openid=' . $data['openid'] . '&lang=zh_CN';
        //初始化curl
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $res = curl_exec($ch);
        curl_close($ch);

        return json_decode($res, true);
    }


    //获取用户的openid
    public function getUserOpenid()
    {
        $url = $this->createOauthUrlForOpenid();

        //初始化curl
        $ch = curl_init();
        //设置超时
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        //运行curl，结果以jason形式返回
        $res = curl_exec($ch);
        $data = json_decode($res, true);
        curl_close($ch);
        $openid = $data['openid'];
        return $openid;
    }


    /**
     *
     * 拼接签名字符串
     * @param array $urlObj
     *
     * @return 返回已经拼接好的字符串
     */
    private function ToUrlParams($urlObj)
    {
        $buff = "";
        foreach ($urlObj as $k => $v) {
            if ($k != "sign") {
                $buff .= $k . "=" . $v . "&";
            }
        }

        $buff = trim($buff, "&");
        return $buff;
    }

}