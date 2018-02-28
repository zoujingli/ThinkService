<?php

// +----------------------------------------------------------------------
// | ThinkService
// +----------------------------------------------------------------------
// | 版权所有 2014~2017 广州楚才信息科技有限公司 [ http://www.cuci.cc ]
// +----------------------------------------------------------------------
// | 官方网站: http://think.ctolog.com
// +----------------------------------------------------------------------
// | 开源协议 ( https://mit-license.org )
// +----------------------------------------------------------------------
// | github开源项目：https://github.com/zoujingli/ThinkService
// +----------------------------------------------------------------------

namespace app\wechat\controller\api;

use Endroid\QrCode\QrCode;
use service\WechatService;

/**
 * 微信测试
 * Class Test
 * @package app\wechat\controller\api
 */
class Test
{
    /**
     * 微信网页授权测试
     * @param string $appid
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function weboauth($appid)
    {
        $fans = WechatService::oauth($appid, 1);
        dump($fans);
    }

    /**
     * 显示二维码
     * @param string $appid 授权公众号AppId
     * @throws \Endroid\QrCode\Exceptions\ImageFunctionFailedException
     * @throws \Endroid\QrCode\Exceptions\ImageFunctionUnknownException
     * @throws \Endroid\QrCode\Exceptions\ImageTypeInvalidException
     */
    public function qrc($appid)
    {
        $url = url('@wechat/api.test/weboauth/appid/' . $appid, '', true, true);
        $qrCode = new QrCode();
        $qrCode->setText($url)
            ->setSize(300)
            ->setPadding(15)
            ->setLabelFontPath(env('VENDOR_PATH') . 'topthink/think-captcha/assets/zhttfs/1.ttf')
            ->setLabelFontSize(20)
            ->setLabel('微信网页授权测试二维码')
            ->setImageType(QrCode::IMAGE_TYPE_PNG);
        header('Content-Type: ' . $qrCode->getContentType());
        $qrCode->render();
        exit();
    }
}
