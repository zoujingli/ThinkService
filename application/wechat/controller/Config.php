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

namespace app\wechat\controller;

use controller\BasicAdmin;
use service\LogService;

/**
 * 微信配置管理
 * Class Index
 * @package app\wechat\controller
 * @author Anyon <zoujingli@qq.com>
 * @date 2017/03/27 14:43
 */
class Config extends BasicAdmin
{

    /**
     * 定义当前操作表名
     * @var string
     */
    public $table = 'WechatConfig';

    /**
     * 微信基础参数配置
     * @return array|string
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function index()
    {
        if (!$this->request->isPost()) {
            return $this->fetch('', ['title' => '微信接口服务']);
        }
        foreach ($this->request->post() as $k => $v) sysconf($k, $v);
        LogService::write('微信管理', '修改微信接口服务参数成功');
        $this->success('数据修改成功！', '');
    }

}
