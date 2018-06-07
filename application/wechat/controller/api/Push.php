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

use app\wechat\service\BuildService;
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
     * @param string $appid
     * @return string
     * @throws \think\Exception
     * @throws \WeChat\Exceptions\InvalidDecryptException
     * @throws \WeChat\Exceptions\InvalidResponseException
     * @throws \WeChat\Exceptions\LocalCacheException
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
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
     * @throws \think\Exception
     * @throws \WeChat\Exceptions\InvalidResponseException
     * @throws \WeChat\Exceptions\LocalCacheException
     * @throws \think\exception\PDOException
     */
    public function ticket()
    {
        $server = WechatService::service();
        if (!($data = $server->getComonentTicket())) {
            return "Ticket event handling failed.";
        }
        # 授权成功通知
        if (!empty($data['AuthorizerAppid']) && $data['InfoType'] === 'authorized') {
            $where = ['authorizer_appid' => $data['AuthorizerAppid']];
            Db::name('WechatConfig')->where($where)->update(['is_deleted' => '0']);
        }
        # 接收取消授权服务事件
        if (!empty($data['AuthorizerAppid']) && $data['InfoType'] === 'unauthorized') {
            $where = ['authorizer_appid' => $data['AuthorizerAppid']];
            Db::name('WechatConfig')->where($where)->update(['is_deleted' => '1']);
        }
        # 授权更新通知
        if (!empty($data['AuthorizerAppid']) && $data['InfoType'] === 'updateauthorized') {
            $_GET['auth_code'] = $data['PreAuthCode'];
            $this->applyAuth($server);
        }
        return 'success';
    }

    /**
     * 网页授权
     * @throws \think\Exception
     * @throws \WeChat\Exceptions\InvalidResponseException
     * @throws \WeChat\Exceptions\LocalCacheException
     */
    public function oauth()
    {
        list($mode, $appid, $enurl, $sessid) = [
            $this->request->get('mode'),
            $this->request->get('state'),
            $this->request->get('enurl'),
            $this->request->get('sessid'),
        ];
        $service = WechatService::service();
        $result = $service->getOauthAccessToken($appid);
        if (empty($result['openid'])) {
            throw new Exception('网页授权失败, 无法进一步操作！');
        }
        cache("{$appid}_{$sessid}_openid", $result['openid']);
        if (!empty($mode)) {
            $wechat = new Oauth($service->getConfig($appid));
            $fans = $wechat->getUserInfo($result['access_token'], $result['openid']);
            if (empty($fans)) {
                throw new Exception('网页授权信息获取失败, 无法进一步操作！');
            }
            cache("{$appid}_{$sessid}_fans", $fans);
        }
        redirect(decode($enurl), [], 301)->send();
    }

    /**
     * 跳转到微信服务授权页面
     * @param string $redirect
     * @return string
     * @throws \think\Exception
     * @throws \WeChat\Exceptions\InvalidResponseException
     * @throws \WeChat\Exceptions\LocalCacheException
     * @throws \think\exception\PDOException
     */
    public function auth($redirect = '')
    {
        $fromRedirect = decode($redirect);
        if (empty($redirect) || empty($fromRedirect)) {
            return '请传入回跳Redirect参数 ( 请使用ENCODE加密 )';
        }
        # 预授权码不为空，则表示可以进行授权处理
        $service = WechatService::service();
        if (($auth_code = $this->request->get('auth_code'))) {
            return $this->applyAuth($service, $fromRedirect);
        }
        # 生成微信授权链接，使用刷新跳转到授权网页
        $url = url("@wechat/api.push/auth/{$redirect}", false, true, true);
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
     * @param string|null $redirect 授权成功回跳地址
     * @return string
     * @throws \WeChat\Exceptions\InvalidResponseException
     * @throws \WeChat\Exceptions\LocalCacheException
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    private function applyAuth($service, $redirect = null)
    {
        // 通过授权code换取公众号信息
        $result = $service->getQueryAuthorizerInfo();
        if (empty($result['authorizer_appid'])) {
            return "接收微信第三方平台授权失败! ";
        }
        // 重新通过接口查询公众号参数
        $author = $service->getAuthorizerInfo($result['authorizer_appid']);
        if (!($info = array_merge($result, $author))) {
            return '获取授权数据失败, 请稍候再试!';
        }
        $info = BuildService::filter($info);
        $info['status'] = '1';
        $info['is_deleted'] = '0';
        $info['expires_in'] = time() + 7000;
        $info['create_at'] = date('Y-m-d H:i:s');
        // 微信接口APPKEY处理与更新
        $config = Db::name('WechatConfig')->where('authorizer_appid', $result['authorizer_appid'])->find();
        $info['appkey'] = empty($config['appkey']) ? md5(uniqid('', true)) : $config['appkey'];
        DataService::save('WechatConfig', $info, 'authorizer_appid');
        if (!empty($redirect)) { // 带上appid与appkey跳转到应用
            $split = stripos($redirect, '?') > 0 ? '&' : '?';
            $realurl = preg_replace(['/appid=\w+/i', '/appkey=\w+/i', '/(\?\&)$/i'], ['', '', ''], $redirect);
            return redirect("{$realurl}{$split}appid={$info['authorizer_appid']}&appkey={$info['appkey']}");
        }
    }

}
