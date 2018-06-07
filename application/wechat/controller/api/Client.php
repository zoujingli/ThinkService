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

use service\WechatService;
use think\Controller;
use think\Db;
use think\Exception;
use think\facade\Log;

/**
 * 获取微信SDK实例对象
 * Class Instance
 * @package app\wechat\controller
 */
class Client extends Controller
{

    /**
     * 当前配置
     * @var string
     */
    protected $config = [];

    /**
     * 当前APPID
     * @var string
     */
    protected $appid = '';

    /**
     * 接口实例名称
     * @var string
     */
    protected $name = '';

    /**
     * 接口类型
     * @var string
     */
    protected $type = '';

    /**
     * 错误消息
     * @var string
     */
    protected $message = '';


    /**
     * 启动Yar接口服务
     * @param string $param AppName-AppId-AppKey
     * @return string
     */
    public function yar($param)
    {
        try {
            $instance = $this->create($param);
            $service = new \Yar_Server($instance);
            $service->handle();
        } catch (\Exception $e) {
            Log::error($errmsg = 'YarServieError: ' . __METHOD__ . '  ###' . $e->getMessage());
            return $errmsg;
        }
    }

    /**
     * 启动SOAP接口服务
     * @param string $param AppName-AppId-AppKey
     * @return string
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\Exception
     */
    public function soap($param)
    {
        try {
            $instance = $this->create($param);
            $service = new \SoapServer(null, ['uri' => strtolower($this->name)]);
            $service->setObject(empty($instance) ? $this : $instance);
            $service->handle();
        } catch (\SoapFault $e) {
            Log::error($errmsg = 'SoapServieError: ' . __METHOD__ . '  ###' . $e->getMessage());
            return $errmsg;
        }
    }


    /**
     * 创建接口服务
     * @param string $token
     * @return \WeChat\Oauth|\WeChat\Pay|\WeChat\Receive|\WeChat\Script|\WeChat\User|\WeOpen\Service
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     * @throws \think\Exception
     */
    private function create($token)
    {
        if ($this->auth($token)) {
            $weminiClassName = 'Account,Basic,Code,Domain,Tester,User,Crypt,Plugs,Poi,Qrcode,Template,Total';
            $wechatClassName = 'Card,Custom,Limit,Media,Menu,Oauth,Pay,Product,Qrcode,Receive,Scan,Script,Shake,Tags,Template,User,Wifi';
            if ($this->type === 'wechat' && stripos($wechatClassName, $this->name) !== false) {
                $instance = WechatService::instance($this->name, $this->appid, 'WeChat');
            } elseif ($this->type === 'wemini' && stripos($weminiClassName, $this->name) !== false) {
                $instance = WechatService::instance($this->name, $this->appid, 'WeMini');
            } elseif (stripos('Service,MiniApp', $this->name) !== false) {
                $instance = WechatService::instance($this->name, $this->appid, 'WeOpen');
            } elseif (stripos('Wechat,Config', $this->name) !== false) {
                $className = "\\app\\wechat\\handler\\{$this->name}Handler";
                $instance = new $className($this->config);
            }
        }
        if (empty($instance)) {
            throw new Exception('Authorisation does not pass.');
        }
        return $instance;
    }

    /**
     * 加载微信实例对象
     * @param string $token 数据格式 name|appid|appkey
     * @return bool
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    private function auth($token = '')
    {
        list($this->name, $this->appid, $appkey, $this->type) = explode('-', $token . '---');
        if (empty($this->name) || empty($this->appid) || empty($appkey)) {
            $this->message = '缺少必要的参数AppId或AppKey';
            return false;
        }
        $where = ['authorizer_appid' => $this->appid, 'status' => '1', 'is_deleted' => '0'];
        $this->config = Db::name('WechatConfig')->where($where)->find();
        if (empty($this->config)) {
            $this->message = '无效的微信绑定对象';
            return false;
        }
        if (strtolower($this->config['appkey']) !== strtolower($appkey)) {
            $this->message = '授权AppId与AppKey不匹配';
            return false;
        }
        $this->message = '';
        $this->name = ucfirst(strtolower($this->name));
        $this->type = strtolower(empty($this->type) ? 'WeChat' : $this->type);
        Db::name('WechatConfig')->where($where)->setInc('total', 1);
        return true;
    }

}