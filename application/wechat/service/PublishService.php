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
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public static function handler($appid)
    {
        /* 创建接口操作对象 */
        $wechat = WechatService::WeChatReceive($appid);
        /* 分别执行对应类型的操作 */
        switch (strtolower($wechat->getMsgType())) {
            case 'text':
                $receive = $wechat->getReceive();
                if ($receive['Content'] === 'TESTCOMPONENT_MSG_TYPE_TEXT') {
                    return $wechat->text('TESTCOMPONENT_MSG_TYPE_TEXT_callback')->reply([], true);
                }
                $key = str_replace("QUERY_AUTH_CODE:", '', $receive['Content']);
                WechatService::instance('Service')->getQueryAuthorizerInfo($key);
                return $wechat->text("{$key}_from_api")->reply([], true);
            case 'event':
                $receive = $wechat->getReceive();
                return $wechat->text("{$receive['Event']}from_callback")->reply([], true);
            default:
                return 'success';
        }
    }

}
