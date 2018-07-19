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

namespace app\index\controller;

use controller\BasicAdmin;
use service\WechatService;

/**
 * 应用入口控制器
 * @author Anyon <zoujingli@qq.com>
 */
class Index extends BasicAdmin
{

    public function index()
    {
        $this->redirect('@admin/login');
    }

    /**
     * 开始绑定
     */
    public function bind()
    {
        $url = url('index/index/bindSuccess', '', true, true);
        $this->redirect('@wechat/api.push/auth/' . encode($url));
    }

    /**
     * 绑定成功
     * @throws \WeChat\Exceptions\InvalidResponseException
     * @throws \WeChat\Exceptions\LocalCacheException
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function bindSuccess()
    {
        if ($appid = $this->request->get('appid')) {
            $user = WechatService::instance('User', $appid);
            $list = $user->getUserList();
            dump($list);
        }
        dump($_GET);
    }

    /**
     * 微信网页授权
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function oauth()
    {
        $openid = WechatService::oauth('wx60a43dd8161666d4', 1);
        dump($openid);
    }

}
