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
use think\Exception;

/**
 * 微信配置服务
 * Class ConfigHandler
 * @package app\wechat\handler
 * @author Anyon <zoujingli@qq.com>
 */
class ConfigHandler extends BasicHandler
{
    /**
     * 获取当前公众号配置
     * @return array|bool
     * @throws Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function getConfig()
    {
        $this->checkInit();
        $where = ['authorizer_appid' => $this->appid];
        $info = Db::name('WechatConfig')->where($where)->find();
        if (empty($info)) {
            return false;
        }
        unset($info['id']);
        return $info;
    }

    /**
     * 设置微信接口通知URL地址
     * @param string $notifyUri 接口通知URL地址
     * @return bool
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function setApiNotifyUri($notifyUri)
    {
        $this->checkInit();
        if (empty($notifyUri)) {
            throw new Exception('请传入微信通知URL', '401');
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
        list($where, $data) = [['authorizer_appid' => $this->appid], ['appkey' => md5(uniqid())]];
        Db::name('WechatConfig')->where($where)->update($data);
        return $data['appkey'];
    }

}