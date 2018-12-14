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

use think\Db;

/**
 * 客户端 SOAP 基础接口
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
        $this->appid = isset($config['authorizer_appid']) ? $config['authorizer_appid'] : '';
    }

    /**
     * 检查微信配置服务初始化状态
     * @return boolean
     * @throws \think\Exception
     */
    public function checkInit()
    {
        if (empty($this->config)) {
            throw new \think\Exception('Wechat Please bind Wechat first', '304');
        }
        return true;
    }

    /**
     * 获取当前公众号配置
     * @return array|boolean
     * @throws \think\Exception
     */
    public function getConfig()
    {
        $this->checkInit();
        $info = Db::name('WechatConfig')->where(['authorizer_appid' => $this->appid])->find();
        if (empty($info)) return false;
        if (isset($info['id'])) unset($info['id']);
        return $info;
    }

    /**
     * 设置微信接口通知URL地址
     * @param string $notifyUri 接口通知URL地址
     * @return boolean
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function setApiNotifyUri($notifyUri)
    {
        $this->checkInit();
        if (empty($notifyUri)) {
            throw new \think\Exception('请传入微信通知URL', '401');
        }
        list($where, $data) = [['authorizer_appid' => $this->appid], ['appuri' => $notifyUri]];
        return Db::name('WechatConfig')->where($where)->update($data) !== false;
    }

    /**
     * 更新接口Appkey(成功返回新的Appkey)
     * @return bool|string
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function updateApiAppkey()
    {
        $this->checkInit();
        $data = ['appkey' => md5(uniqid())];
        $where = ['authorizer_appid' => $this->appid];
        Db::name('WechatConfig')->where($where)->update($data);
        return $data['appkey'];
    }

}
