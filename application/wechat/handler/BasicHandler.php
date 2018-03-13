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

use think\Exception;

/**
 * 客户端SAOP基础接口
 * Class BasicHandler
 * @package app\wechat\handler
 * @author Anyon <zoujingli@qq.com>
 */
class BasicHandler
{

    /**
     * 当前微信配置
     * @var array
     */
    protected $config;

    /**
     * 当前微信APPID
     * @var string
     */
    protected $appid;

    /**
     * 错误消息
     * @var string
     */
    protected $message;

    /**
     * ConfigService constructor.
     * @param array $config
     */
    public function __construct($config)
    {
        $this->config = $config;
        $this->appid = empty($config['authorizer_appid']) ? '' : $config['authorizer_appid'];
    }

    /**
     * 检查微信配置服务初始化状态
     * @return bool
     * @throws Exception
     */
    public function checkInit()
    {
        if (empty($this->config)) {
            throw new Exception('Wechat Please bind Wechat first', '304');
        }
        return true;
    }

}
