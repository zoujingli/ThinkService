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
 *
 * @method \WeChat\Card card($appid) static 卡券管理
 * @method \WeChat\Custom custom($appid) static 客服消息处理
 * @method \WeChat\Limit limit($appid) static 接口调用频次限制
 * @method \WeChat\Media media($appid) static 微信素材管理
 * @method \WeChat\Menu menu($appid) static 微信素材管理
 * @method \WeChat\Oauth oauth($appid) static 微信网页授权
 * @method \WeChat\Pay pay($appid) static 微信支付商户
 * @method \WeChat\Product product($appid) static 商店管理
 * @method \WeChat\Qrcode qrcode($appid) static 二维码管理
 * @method \WeChat\Receive receive($appid) static 公众号推送管理
 * @method \WeChat\Scan scan($appid) static 扫一扫接入管理
 * @method \WeChat\Script script($appid) static 微信前端支持
 * @method \WeChat\Shake shake($appid) static 揺一揺周边
 * @method \WeChat\Tags tags($appid) static 用户标签管理
 * @method \WeChat\Template template($appid) static 模板消息
 * @method \WeChat\User user($appid) static 微信粉丝管理
 * @method \WeChat\Wifi wifi($appid) static 门店WIFI管理
 * @method \WeOpen\Service service() static 第三方服务
 * @method void wechat() static 第三方微信工具
 * @method void config() static 第三方配置工具
 */
class WechatService
{

    /**
     * 实例微信对象
     * @param string $type
     * @param string $appid 授权公众号APPID
     * @return \WeChat\Card|\WeChat\Custom|\WeChat\Media|\WeChat\Menu|\WeChat\Oauth|\WeChat\Pay|\WeChat\Product|\WeChat\Qrcode|\WeChat\Receive|\WeChat\Scan|\WeChat\Script|\WeChat\Shake|\WeChat\Tags|\WeChat\Template|\WeChat\User|\WeChat\Wifi|Service
     * @throws Exception
     * @throws \think\exception\PDOException
     */
    public static function instance($type, $appid = '')
    {
        $config = [
            'cache_path'               => env('runtime_path') . 'wechat',
            'component_appid'          => sysconf('component_appid'),
            'component_token'          => sysconf('component_token'),
            'component_appsecret'      => sysconf('component_appsecret'),
            'component_encodingaeskey' => sysconf('component_encodingaeskey'),
        ];
        // 注册授权公众号 AccessToken 处理
        $config['GetAccessTokenCallback'] = function ($authorizer_appid) use ($config) {
            $where = ['authorizer_appid' => $authorizer_appid];
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
     * 静态初始化对象
     * @param string $name
     * @param array $arguments
     * @return \WeChat\Oauth|\WeChat\Pay|\WeChat\Receive|\WeChat\Script|\WeChat\User|Service
     * @throws Exception
     * @throws \think\exception\PDOException
     */
    public static function __callStatic($name, $arguments)
    {
        return self::instance($name, isset($arguments[0]) ? $arguments[0] : '');
    }

}
