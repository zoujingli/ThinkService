<?php

// +----------------------------------------------------------------------
// | Think.Service
// +----------------------------------------------------------------------
// | 版权所有 2014~2017 广州楚才信息科技有限公司 [ http://www.cuci.cc ]
// +----------------------------------------------------------------------
// | 官方网站: http://think.ctolog.com
// +----------------------------------------------------------------------
// | 开源协议 ( https://mit-license.org )
// +----------------------------------------------------------------------
// | github开源项目：https://github.com/zoujingli/Think.Service
// +----------------------------------------------------------------------

namespace app\wechat\service;

use service\WechatService;

/**
 * 第三方平台测试上线
 *
 * @author Anyon <zoujingli@qq.com>
 * @date 2016/10/27 14:14
 */
class PublishService
{

    /**
     * 当前微信APPID
     * @var string
     */
    protected static $appid;

    /**
     * 事件初始化
     * @param string $appid
     * @return string
     * @throws \WeChat\Exceptions\InvalidDecryptException
     * @throws \WeChat\Exceptions\InvalidResponseException
     * @throws \WeChat\Exceptions\LocalCacheException
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public static function handler($appid)
    {
        /* 创建接口操作对象 */
        $wechat = WechatService::instance('Receive', $appid);
        /* 分别执行对应类型的操作 */
        $receive = $wechat->getReceive();
        p($receive);
        switch (strtolower($wechat->getMsgType())) {
            case 'text':
                if ($receive['Content'] === 'TESTCOMPONENT_MSG_TYPE_TEXT') {
                    return $wechat->text('TESTCOMPONENT_MSG_TYPE_TEXT_callback')->reply([], true);
                }
                $key = ltrim($receive['Content'], "QUERY_AUTH_CODE:");
                WechatService::instance('Service')->getAuthorizerInfo($key);
                return $wechat->text("{$key}_from_api")->reply(false, true);
            case 'event':
                return $wechat->text("{$receive['EventKey']}from_callback")->reply([], true);
            default:
                return 'success';
        }
    }

}
