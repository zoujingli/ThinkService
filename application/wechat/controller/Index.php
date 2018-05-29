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

use app\wechat\service\BuildService;
use controller\BasicAdmin;
use service\DataService;
use service\WechatService;
use think\Db;

/**
 * 微信配置管理
 * Class Index
 * @package app\wechat\controller
 * @author Anyon <zoujingli@qq.com>
 * @date 2017/03/27 14:43
 */
class Index extends BasicAdmin
{

    /**
     * 定义当前操作表名
     * @var string
     */
    public $table = 'WechatConfig';

    /**
     * 微信基础参数配置
     * @return string
     * @throws \think\Exception
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public function index()
    {
        $this->title = '微信授权管理';
        $get = $this->request->get();
        $db = Db::name($this->table)->where(['is_deleted' => '0']);
        foreach (['authorizer_appid', 'nick_name', 'principal_name'] as $key) {
            (isset($get[$key]) && $get[$key] !== '') && $db->whereLike($key, "%{$get[$key]}%");
        }
        if (isset($get['service_type']) && $get['service_type'] !== '') {
            $db->where('service_type', $get['service_type']);
        }
        if (isset($get['create_at']) && $get['create_at'] !== '') {
            list($start, $end) = explode(' - ', $get['create_at']);
            $db->whereBetween('create_at', ["{$start} 00:00:00", "{$end} 23:59:59"]);
        }
        return $this->_list($db->order('create_at desc'));
    }

    /**
     * 同步获取权限
     * @throws \WeChat\Exceptions\InvalidResponseException
     * @throws \WeChat\Exceptions\LocalCacheException
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function sync()
    {
        $appid = $this->request->get('appid');
        $where = ['authorizer_appid' => $appid, 'is_deleted' => '0', 'status' => '1'];
        $author = Db::name('WechatConfig')->where($where)->find();
        empty($author) && $this->error('无效的授权信息，请同步其它公众号！');
        $wechat = WechatService::service();
        $info = BuildService::filter($wechat->getAuthorizerInfo($appid));
        $info['authorizer_appid'] = $appid;
        if (DataService::save('WechatConfig', $info, 'authorizer_appid')) {
            $this->success('更新授权信息成功！', '');
        }
        $this->error('获取授权信息失败，请稍候再试！');
    }

    /**
     * 同步获取所有授权公众号记录
     * @throws \WeChat\Exceptions\InvalidResponseException
     * @throws \WeChat\Exceptions\LocalCacheException
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function syncall()
    {
        $wechat = WechatService::service();
        $result = $wechat->getAuthorizerList();
        foreach ($result['list'] as $item) {
            if (!empty($item['refresh_token']) && !empty($item['auth_time'])) {
                $data = BuildService::filter($wechat->getAuthorizerInfo($item['authorizer_appid']));
                $data['authorizer_appid'] = $item['authorizer_appid'];
                $data['authorizer_refresh_token'] = $item['refresh_token'];
                $data['create_at'] = date('Y-m-d H:i:s', $item['auth_time']);
                if (!DataService::save('WechatConfig', $data, 'authorizer_appid')) {
                    $this->error('获取授权信息失败，请稍候再试！', '');
                }
            }
        }
        $this->success('同步获取所有授权信息成功！', '');
    }

    /**
     * 删除微信
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function del()
    {
        $wechat = WechatService::service();
        if (DataService::update($this->table)) {
            $this->success("微信删除成功！", '');
        }
        $this->error("微信删除失败，请稍候再试！");
    }

    /**
     * 微信禁用
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function forbid()
    {
        if (DataService::update($this->table)) {
            $this->success("微信禁用成功！", '');
        }
        $this->error("微信禁用失败，请稍候再试！");
    }

    /**
     * 微信禁用
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public function resume()
    {
        if (DataService::update($this->table)) {
            $this->success("微信启用成功！", '');
        }
        $this->error("微信启用失败，请稍候再试！");
    }


}
