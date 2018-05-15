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
use WeOpen\MiniApp;

/**
 * 微信数据服务
 * Class WechatService
 * @package service
 * @author Anyon <zoujingli@qq.com>
 * @date 2017/03/22 15:32
 *
 * @method \WeChat\Card card() static 卡券管理
 * @method \WeChat\Custom custom() static 客服消息处理
 * @method \WeChat\Limit limit() static 接口调用频次限制
 * @method \WeChat\Media media() static 微信素材管理
 * @method \WeChat\Menu menu() static 微信素材管理
 * @method \WeChat\Oauth oauth() static 微信网页授权
 * @method \WeChat\Pay pay() static 微信支付商户
 * @method \WeChat\Product product() static 商店管理
 * @method \WeChat\Qrcode qrcode() static 二维码管理
 * @method \WeChat\Receive receive() static 公众号推送管理
 * @method \WeChat\Scan scan() static 扫一扫接入管理
 * @method \WeChat\Script script() static 微信前端支持
 * @method \WeChat\Shake shake() static 揺一揺周边
 * @method \WeChat\Tags tags() static 用户标签管理
 * @method \WeChat\Template template() static 模板消息
 * @method \WeChat\User user() static 微信粉丝管理
 * @method \WeChat\Wifi wifi() static 门店WIFI管理
 * --- 小程序SDK加载 开始 ---
 * @method \WeMini\Account miniAccount() static 小程序账号管理
 * @method \WeMini\Basic miniBasic() static 小程序基础信息设置
 * @method \WeMini\Code miniCode() static 小程序代码管理
 * @method \WeMini\Domain miniDomain() static 小程序域名管理
 * @method \WeMini\Tester minitester() static 小程序成员管理
 * @method \WeMini\User miniUser() static 小程序帐号管理
 * @method \WeMini\Crypt miniCrypt() static 小程序数据加密处理管理
 * @method \WeMini\Plugs miniPlugs() static 小程序插件管理
 * @method \WeMini\Poi miniPoi() static 小程地址管理
 * @method \WeMini\Qrcode miniQrcode() static 小程二维码管理
 * @method \WeMini\Template miniTemplate() static 小程序模板消息
 * @method \WeMini\Total miniTotal() static 小程序数据接口
 * --- 小程序SDK加载 结束 ---
 * @method void wechat() static 第三方微信工具
 * @method void config() static 第三方配置工具
 * @method \WeOpen\Login login() static 第三方微信登录
 * @method \WeOpen\Service service() static 第三方服务
 */
class WechatService
{

    /**
     * 接口类型模式
     * @var string
     */
    private static $type = 'WeChat';

    /**
     * 切换接口为服务号模式
     */
    public static function setWeChatState()
    {
        self::$type = 'WeChat';
    }

    /**
     * 切换接口为小程序模式
     */
    public static function setWeMiniState()
    {
        self::$type = 'WeMini';
    }

    /**
     * 实例微信对象
     * @param string $name
     * @param string $appid 授权公众号APPID
     * @param string $type 将要获取SDK类型
     * @return \WeChat\Card|\WeChat\Custom|\WeChat\Media|\WeChat\Menu|\WeChat\Oauth|\WeChat\Pay|\WeChat\Product|\WeChat\Qrcode|\WeChat\Receive|\WeChat\Scan|\WeChat\Script|\WeChat\Shake|\WeChat\Tags|\WeChat\Template|\WeChat\User|\WeChat\Wifi|MiniApp
     * @throws Exception
     * @throws \think\exception\PDOException
     */
    public static function instance($name, $appid = '', $type = null)
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
            $open = new MiniApp($config);
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
        $app = new MiniApp($config);
        if (in_array(strtolower($name), ['service', 'miniapp'])) {
            return $app;
        }
        if (!in_array($type, ['WeChat', 'WeMini'])) {
            $type = self::$type;
        }
        return $app->instance($name, $appid, $type);
    }

    /**
     * 静态初始化对象
     * @param string $name
     * @param array $arguments
     * @return \WeChat\Oauth|\WeChat\Pay|\WeChat\Receive|\WeChat\Script|\WeChat\User|\WeChat\Service
     * @throws Exception
     * @throws \think\exception\PDOException
     */
    public static function __callStatic($name, $arguments)
    {
        if (substr($name, 0, 4) === 'mini') {
            self::setWeMiniState();
            $name = substr($name, 4);
        } else {
            self::setWeChatState();
        }
        return self::instance($name, isset($arguments[0]) ? $arguments[0] : '', self::$type);
    }

}
