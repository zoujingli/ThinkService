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

namespace service;

use think\Exception;
use think\facade\Log;

/**
 * Soap服务对象
 * Class SoapService
 * @package service
 */
class SoapService
{

    /**
     * SOAP实例对象
     * @var \SoapClient
     */
    protected $soap;

    /**
     * SoapService constructor.
     * @param string|null $wsdl WSDL连接参数
     * @param array $params Params连接参数
     * @throws \think\Exception
     */
    public function __construct($wsdl, $params)
    {
        set_time_limit(3600);
        if (!extension_loaded('soap')) {
            throw new Exception('Not support soap.');
        }
        $this->soap = new \SoapClient($wsdl, $params);
    }

    /**
     * @param string $name SOAP调用方法名
     * @param array|string $arguments SOAP调用参数
     * @return array|string|bool
     * @throws \think\Exception
     */
    public function __call($name, $arguments)
    {
        try {
            return $this->soap->__soapCall($name, $arguments);
        } catch (\Exception $e) {
            Log::error("Soap Error. Call {$name} Method --- " . $e->getMessage());
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

}
