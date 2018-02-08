<?php

// +----------------------------------------------------------------------
// | ThinkAdmin
// +----------------------------------------------------------------------
// | 版权所有 2014~2017 广州楚才信息科技有限公司 [ http://www.cuci.cc ]
// +----------------------------------------------------------------------
// | 官方网站: http://think.ctolog.com
// +----------------------------------------------------------------------
// | 开源协议 ( https://mit-license.org )
// +----------------------------------------------------------------------
// | github开源项目：https://github.com/zoujingli/ThinkAdmin
// +----------------------------------------------------------------------

namespace app\index\controller;

use WeChat\Pay;
use WeChat\Qrcode;
use WeChat\Receive;

class Demo
{

    /**
     * 创建二维码
     * @throws \WeChat\Exceptions\InvalidResponseException
     * @throws \WeChat\Exceptions\LocalCacheException
     */
    public function qrc()
    {
        $media = new Qrcode(config('wechat.'));
        $result = $media->create('111');
        dump($result);
    }

    /**
     * 支付创建测试
     * @throws \WeChat\Exceptions\InvalidResponseException
     */
    public function pay()
    {
        $pay = new Pay(config('wechat.'));
        // 统一下单接口
        $options = [
            'body'             => '测试商品',
            'out_trade_no'     => time(),
            'total_fee'        => '1',
            'trade_type'       => 'JSAPI',
            'notify_url'       => 'http://a.com/text.html',
            'spbill_create_ip' => app('request')->ip(),
        ];
        dump($pay->createOrder($options));
    }

    /**
     * 微信接口验证
     */
    public function api()
    {
        try {
            $api = new Receive(config('wechat.'));
            p($api->getReceive());
            p("当前OPENID " . var_export($api->getOpenid(), true) . PHP_EOL);
            p("当前请求类型 " . var_export($api->getMsgType(), true) . PHP_EOL);
            $api->text('fasdaf')->reply();
        } catch (\Exception $e) {
            p('----- Exception ------' . PHP_EOL);
            p($e->getFile() . ':' . $e->getLine() . PHP_EOL . '    ' . $e->getMessage());
        }
    }
}