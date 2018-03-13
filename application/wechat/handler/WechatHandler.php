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

namespace app\wechat\handler;

use service\FileService;
use service\WechatService;

/**
 * 微信网页授权接口
 * Class WechatHandler
 * @package app\wechat\handler
 * @author Anyon <zoujingli@qq.com>
 */
class WechatHandler extends BasicHandler
{

    /**
     * 微信网页授权
     * @param string $sessid 当前会话id(可用session_id()获取)
     * @param string $self_url 当前会话URL地址(需包含域名的完整URL地址)
     * @param int $fullMode 网页授权模式(0静默模式,1高级授权)
     * @return array|bool
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\Exception
     */
    public function oauth($sessid, $self_url, $fullMode = 0)
    {
        $this->checkInit();
        $fans = cache("{$this->appid}_{$sessid}_fans");
        $openid = cache("{$this->appid}_{$sessid}_openid");
        if (!empty($openid) && (empty($fullMode) || !empty($fans))) {
            return ['openid' => $openid, 'fans' => $fans, 'url' => ''];
        }
        $service = WechatService::instance('service');
        $mode = empty($fullMode) ? 'snsapi_base' : 'snsapi_userinfo';
        $url = url('@wechat/api.push/oauth', '', true, 'wm.cuci.cc');
        $params = ['mode' => $fullMode, 'sessid' => $sessid, 'enurl' => encode($self_url)];
        $authurl = $service->getOauthRedirect($this->appid, $url . '?' . http_build_query($params), $mode);
        return ['openid' => $openid, 'fans' => $fans, 'url' => $authurl];
    }

    /**
     * 上传Base64文件到服务器
     * @param string $base64 文件内容
     * @param string $filename 文件名称
     * @return array|null
     */
    public function upFile($base64, $filename)
    {
        return FileService::local(basename($filename), base64_decode($base64));
    }

    /**
     * 清理上传的文件
     * @param string $filename 文件名称
     * @return bool
     */
    public function rmFile($filename)
    {
        return unlink(ROOT_PATH . 'static/upload/' . basename($filename)) ? 1 : 0;
    }

    /**
     * 获取公众号的配置参数
     * @param null|string $name 参数名称
     * @return array|string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function config($name = null)
    {
        return WechatService::instance('script', $this->appid)->config->get($name);
    }

    /**
     * 微信网页JS签名
     * @param string $url 当前会话URL地址(需包含域名的完整URL地址)
     * @return array|bool
     * @throws \WeChat\Exceptions\InvalidResponseException
     * @throws \WeChat\Exceptions\LocalCacheException
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function jsSign($url)
    {
        $this->checkInit();
        $script = WechatService::instance('script', $this->appid);
        $result = $script->getJsSign($url);
        return $result;
    }

}