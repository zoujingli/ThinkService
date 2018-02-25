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

namespace app\wechat\controller\api;

use app\wechat\service\PublishService;
use app\wechat\service\ReceiveService;
use service\DataService;
use service\WechatService;
use think\Controller;
use think\Db;
use think\Exception;
use WeChat\Oauth;

/**
 * 微信推送事件处理
 *
 * @author Anyon <zoujingli@qq.com>
 * @date 2016/10/18 12:38
 */
class Push extends Controller
{

    /**
     * 微信API推送事件处理
     * @param string $appid 授权公众号Appid
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\Exception
     */
    public function notify($appid)
    {
        /* 全网发布接口测试 */
        if ($appid === 'wx570bc396a51b8ff8') {
            return PublishService::handler($appid);
        }
        /* 接口类正常服务 */
        return ReceiveService::handler($appid);
    }

    /**
     * 一、处理服务推送Ticket
     * 二、处理取消公众号授权
     * @return string
     * @throws \WeChat\Exceptions\InvalidResponseException
     * @throws \WeChat\Exceptions\LocalCacheException
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function ticket()
    {
        $server = WechatService::instance('Service');
        if (!($data = $server->getComonentTicket())) {
            return "Ticket event handling failed.";
        }
        # 接收取消授权服务事件 
        if ($data['InfoType'] === 'unauthorized' && !empty($data['AppId'])) {
            $where = ['authorizer_appid' => $data['AuthorizerAppid']];
            Db::name('WechatConfig')->where($where)->update(['status' => '0']);
        }
        return 'success';
    }

    /**
     * 网页授权
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function oauth()
    {
        list($appid, $authMode, $redirectCode) = [
            $this->request->get('state'),
            $this->request->get('auth_mode'),
            $this->request->get('redirect_code'),
        ];
        $service = WechatService::instance('service');
        $result = $service->getOauthAccessToken($appid);
        if (empty($result['openid'])) {
            throw new Exception('网页授权失败, 无法进一步操作！');
        }
        session("{$appid}_openid", $result['openid'], 7000);
        if (!empty($authMode)) {
            $wechat = new Oauth($service->getConfig($result['appid']));
            $fans = $wechat->getUserInfo($result['access_token'], $result['openid']);
            if (empty($fans)) {
                throw new Exception('网页授权信息获取失败, 无法进一步操作！');
            }
            session("{$appid}_fansinfo", $fans, 3600);
        }
        dump($result);
        exit(decode($redirectCode));
        $this->redirect(decode($redirectCode));
    }

    /**
     * 跳转到微信服务授权页面
     * @param string $redirect
     * @return string
     * @throws \WeChat\Exceptions\InvalidResponseException
     * @throws \WeChat\Exceptions\LocalCacheException
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function auth($redirect = '')
    {
        $fromRedirect = decode($redirect);
        if (empty($redirect) || empty($fromRedirect)) {
            return '请传入回跳Redirect参数 ( 请使用ENCODE加密 )';
        }
        # 预授权码不为空，则表示可以进行授权处理
        $service = WechatService::instance('Service');
        if (($auth_code = $this->request->get('auth_code'))) {
            return $this->applyAuth($service, $fromRedirect);
        }
        # 生成微信授权链接，使用刷新跳转到授权网页
        $url = url("@wechat/api.push/auth/redirect/{$redirect}", false, true, true);
        if (($redirect = $service->getAuthRedirect($url))) {
            ob_clean();
            header("Refresh:0;url={$redirect}");
            return "<script>window.location.href='{$redirect}';</script><a href='{$redirect}'>跳转中...</a>";
        }
        # 生成微信授权链接失败
        return "<h2>Failed to create authorization. Please return to try again.</h2>";
    }

    /**
     * 公众号授权绑定数据处理
     * @param \WeOpen\Service $service
     * @param string $redirect 授权成功回跳地址
     * @return string
     * @throws \WeChat\Exceptions\InvalidResponseException
     * @throws \WeChat\Exceptions\LocalCacheException
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    private function applyAuth($service, $redirect)
    {
        // 通过授权code换取公众号信息
        $result = $service->getQueryAuthorizerInfo();
        if (empty($result['authorizer_appid'])) {
            return "接收微信第三方平台授权失败! ";
        }
        // 重新通过接口查询公众号参数
        if (!($info = array_merge($result, $service->getAuthorizerInfo($result['authorizer_appid'])))) {
            return '获取授权数据失败, 请稍候再试!';
        }
        $info['func_info'] = join(',', array_map(function ($tmp) {
            return $tmp['funcscope_category']['id'];
        }, $info['func_info']));
        $info['verify_type_info'] = join(',', $info['verify_type_info']);
        $info['service_type_info'] = join(',', $info['service_type_info']);
        $info['business_info'] = json_encode($info['business_info'], JSON_UNESCAPED_UNICODE);
        $info['status'] = '1';
        $info['expires_in'] = time() + 7000;
        $info['create_by'] = session('user.id');
        // 微信类型:  0 代表订阅号, 2 代表服务号
        $info['service_type'] = intval($info['service_type_info']) === 2 ? 2 : 0;
        // 微信认证: -1 代表未认证, 0 代表微信认证
        $info['verify_type'] = intval($info['verify_type_info']) !== 0 ? -1 : 0;
        unset($info['signature']);
        DataService::save('WechatConfig', $info, 'authorizer_appid');
        // 带上appid与appkey跳转到应用
        $split = stripos($redirect, '?') > 0 ? '&' : '?';
        $realurl = preg_replace(['/appid=\w+/i', '/(\?\&)$/i'], '', $redirect);
        return redirect("{$realurl}{$split}appid={$info['authorizer_appid']}");
    }
}
