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
use think\Log;
use Wechat\WechatReceive;

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
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\Exception
     */
    public static function handler($appid)
    {
        /* 创建接口操作对象 */
        $wechat = WechatService::instance('Receive', $appid);
        if ($wechat->valid() === false) {
            Log::error(($err = "微信被动接口验证失败, {$wechat->errMsg}[{$wechat->errCode}]"));
            return $err;
        }
        /* 分别执行对应类型的操作 */
        switch ($wechat->getRev()->getRevType()) {
            case WechatReceive::MSGTYPE_TEXT:
                $content = $wechat->getRevContent();
                if ($content === 'TESTCOMPONENT_MSG_TYPE_TEXT') {
                    return $wechat->text('TESTCOMPONENT_MSG_TYPE_TEXT_callback')->reply(false, true);
                }
                $key = ltrim($content, "QUERY_AUTH_CODE:");
                WechatService::instance('Service')->getAuthorizationInfo($key);
                return $wechat->text("{$key}_from_api")->reply(false, true);
            case WechatReceive::MSGTYPE_EVENT:
                $event = $wechat->getRevEvent();
                return $wechat->text("{$event['event']}from_callback")->reply(false, true);
            case WechatReceive::MSGTYPE_IMAGE:
            case WechatReceive::MSGTYPE_LOCATION:
            default:
                return 'success';
        }
    }

}
