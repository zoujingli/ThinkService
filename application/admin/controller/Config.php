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

namespace app\admin\controller;

use controller\BasicAdmin;
use service\ExtendService;
use service\LogService;

/**
 * 后台参数配置控制器
 * Class Config
 * @package app\admin\controller
 * @author Anyon <zoujingli@qq.com>
 * @date 2017/02/15 18:05
 */
class Config extends BasicAdmin
{

    /**
     * 当前默认数据模型
     * @var string
     */
    public $table = 'SystemConfig';

    /**
     * 当前页面标题
     * @var string
     */
    public $title = '网站参数配置';

    /**
     * 显示系统常规配置
     * @return string
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function index()
    {
        if (!$this->request->isPost()) {
            return $this->fetch('', ['title' => $this->title]);
        }
        foreach ($this->request->post() as $key => $vo) {
            sysconf($key, $vo);
        }
        LogService::write('系统管理', '系统参数配置成功');
        $this->success('系统参数配置成功！', '');
    }

    /**
     * 文件存储配置
     * @return string
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function file()
    {
        $alert = [
            'type'    => 'danger',
            'title'   => '操作安全警告（默认使用本地服务存储）',
            'content' => '请根据实际情况配置存储引擎，合理做好站点下载分流。建议尽量使用云存储服务，同时保证文件访问协议与网站访问协议一致！',
        ];
        $this->title = '文件存储配置';
        $this->assign('alert', $alert);
        return $this->index();
    }

    /**
     * 短信参数配置
     * @return string
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function sms()
    {
        $this->title = '短信服务配置';
        $alert = [
            'type'    => 'danger',
            'title'   => '操作安全警告（默认支付助通SMS服务）',
            'content' => '肋通SMS服务为第三方收费服务，请定期查询并保证有充足的剩余短信数量！',
        ];
        $this->assign('alert', $alert);
        $this->assign('result', ExtendService::querySmsBalance());
        return $this->index();
    }

}
