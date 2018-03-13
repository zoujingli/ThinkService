<?php

// +----------------------------------------------------------------------
// | ThinkService
// +----------------------------------------------------------------------
// | 版权所有 2014~2017 广州楚才信息科技有限公司 [ http://www.cuci.cc ]
// +----------------------------------------------------------------------
// | 官方网站: http://think.ctolog.com
// +----------------------------------------------------------------------
// | 开源协议 ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | github开源项目：https://github.com/zoujingli/ThinkService
// +----------------------------------------------------------------------

namespace service;

use think\Db;
use think\Exception;
use WeOpen\Service;

/**
 * 微信数据服务
 * Class WechatService
 * @package service
 * @author Anyon <zoujingli@qq.com>
 * @date 2017/03/22 15:32
 */
class WechatService
{
    /**
     * 实例微信对象
     * @param string $type
     * @param string $appid 授权公众号APPID
     * @return \WeChat\Oauth|\WeChat\Pay|\WeChat\User|\WeOpen\Service|\WeChat\Receive|\WeChat\Script
     * @throws Exception
     */
    public static function instance($type, $appid = '')
    {
        // 获取第三方平台配置
        $config = self::getConfig();
        // 注册授权公众号 AccessToken 处理
        $config['GetAccessTokenCallback'] = function ($authorizer_appid) use ($config) {
            $where = ['authorizer_appid' => $authorizer_appid, 'stauts' => '1'];
            if (!($refresh_token = Db::name('WechatConfig')->where($where)->value('authorizer_refresh_token'))) {
                throw new Exception('The WeChat information is not configured.', '404');
            }
            $open = new Service($config);
            $result = $open->refreshAccessToken($authorizer_appid, $refresh_token);
            if (empty($result['authorizer_access_token']) || empty($result['authorizer_refresh_token'])) {
                throw new Exception($result['errmsg'], '0');
            }
            Db::name('WechatConfig')->where($where)->update([
                'authorizer_access_token'  => $result['authorizer_access_token'],
                'authorizer_refresh_token' => $result['authorizer_refresh_token'],
            ]);
            return $result['authorizer_access_token'];
        };
        $open = new Service($config);
        if (strtolower($type) === 'service') {
            return $open;
        }
        return $open->instance($type, $appid);
    }

    /**
     * 获取公众号配置
     * @return array
     * @throws Exception
     */
    public static function getConfig()
    {
        # 读取开放平台配置
        $config = [
            'cache_path'               => env('RUNTIME_PATH') . 'wechat',
            'component_appid'          => sysconf('component_appid'),
            'component_token'          => sysconf('component_token'),
            'component_appsecret'      => sysconf('component_appsecret'),
            'component_encodingaeskey' => sysconf('component_encodingaeskey'),
        ];
        return $config;
    }

}
