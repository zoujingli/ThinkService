<?php

// +----------------------------------------------------------------------
// | ThinkAdmin
// +----------------------------------------------------------------------
// | 版权所有 2014~2017 广州楚才信息科技有限公司 [ http://www.cuci.cc ]
// +----------------------------------------------------------------------
// | 官方网站: http://think.ctolog.com
// +----------------------------------------------------------------------
// | 开源协议 ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | github开源项目：https://github.com/zoujingli/ThinkAdmin
// +----------------------------------------------------------------------

namespace service;

use think\Db;
use think\Exception;
use WeChat\Media;
use WeChat\Tags;
use WeChat\User;
use WeOpen\Service;

/**
 * 微信数据服务
 * Class WechatService
 * @package service
 * @author Anyon <zoujingli@qq.com>
 * @date 2017/03/22 15:32
 */
class WechatService
{

    /**
     * 获取微信实例ID
     * @param string $type 微信操作对象
     * @param string $appid 公众号
     * @return \WeOpen\Service|\WeChat\Card|\WeChat\Custom|Media|\WeChat\Menu|\WeChat\Oauth|\WeChat\Pay|\WeChat\Product|\WeChat\Qrcode|\WeChat\Receive|\WeChat\Scan|\WeChat\Script|\WeChat\Shake|Tags|\WeChat\Template|User|\WeChat\Wifi|Service
     * @throws Exception
     * @throws \think\exception\PDOException
     */
    public static function instance($type, $appid = '')
    {
        $config = [
            'component_token'          => sysconf('component_token'),
            'component_appid'          => sysconf('component_appid'),
            'component_appsecret'      => sysconf('component_appsecret'),
            'component_encodingaeskey' => sysconf('component_encodingaeskey'),
            'cache_path'               => env('runtime_path') . 'wechat' . DIRECTORY_SEPARATOR,
        ];
        // 注册授权公众号 AccessToken 处理
        $config['GetAccessTokenCallback'] = function ($authorizer_appid) use ($config) {
            $where = ['authorizer_appid' => $authorizer_appid];
            if (!($refresh_token = Db::name('WechatConfig')->where($where)->value('authorizer_refresh_token'))) {
                throw new Exception('The WeChat information is not configured.', '404');
            }
            $open = new Service($config);
            $result = $open->refreshAccessToken($authorizer_appid, $refresh_token);
            if (empty($result['authorizer_access_token']) || empty($result['authorizer_refresh_token'])) {
                throw new Exception($result['errmsg'], '0');
            }
            Db::name('WechatConfig')->where($where)->update([
                'authorizer_access_token'  => $result['authorizer_access_token'],
                'authorizer_refresh_token' => $result['authorizer_refresh_token'],
            ]);
            return $result['authorizer_access_token'];
        };
        $open = new Service($config);
        if (strtolower($type) === 'service') {
            return $open;
        }
        return $open->instance($type, $appid);
    }

    /**
     * 初始化进入授权
     * @param string $appid 公众号授权
     * @param int $fullMode 授权公众号模式
     * @return array
     * @throws Exception
     * @throws \think\exception\PDOException
     */
    public static function oauth($appid, $fullMode = 0)
    {
        list($openid, $fansinfo) = [session("{$appid}_openid"), session("{$appid}_fansinfo")];
        if ((empty($fullMode) && !empty($openid)) || (!empty($fullMode) && !empty($fansinfo))) {
            empty($fansinfo) || self::setFansInfo($fansinfo);
            return ['openid' => $openid, 'fansinfo' => $fansinfo];
        }
        $service = self::instance('service');
        $mode = empty($fullMode) ? 'snsapi_base' : 'snsapi_userinfo';
        $params = ['mode' => $fullMode, 'enurl' => encode(request()->url(true))];
        $authUrl = url('@wechat/api.push/oauth', '', true, true) . '?' . http_build_query($params);
        redirect($service->getOauthRedirect($appid, $authUrl, $mode), [], 301)->send();
    }

    /**
     * 通过图文ID读取图文信息
     * @param int $id 本地图文ID
     * @param array $where 额外的查询条件
     * @return array
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getNewsById($id, $where = [])
    {
        $data = Db::name('WechatNews')->where(['id' => $id])->where($where)->find();
        $article_ids = explode(',', $data['article_id']);
        $articles = Db::name('WechatNewsArticle')->whereIn('id', $article_ids)->select();
        $data['articles'] = [];
        foreach ($article_ids as $article_id) {
            foreach ($articles as $article) {
                if (intval($article['id']) === intval($article_id)) {
                    unset($article['create_by'], $article['create_at']);
                    $article['content'] = htmlspecialchars_decode($article['content']);
                    $data['articles'][] = $article;
                }
            }
        }
        unset($articles);
        return $data;
    }

    /**
     * 上传图片到微信服务器
     * @param string $local_url
     * @return string|null
     * @throws \Exception
     */
    public static function uploadImage($local_url)
    {
        $where = ['md5' => md5($local_url)];
        if (!($media_url = Db::name('WechatNewsImage')->where($where)->value('media_url'))) {
            return $media_url;
        }
        $result = ['file' => $local_url];
        if (!file_exists($local_url)) {
            $result = FileService::local(basename($local_url), file_get_contents($local_url));
        }
        try {
            $wechat = new Media(config('wechat.'));
            $info = $wechat->uploadImg($result['file']);
            $data = ['local_url' => $local_url, 'media_url' => $info['url'], 'md5' => md5($local_url)];
            Db::name('WechatNewsImage')->insert($data);
            return $info['url'];
        } catch (\Exception $e) {
            throw new Exception($e->getMessage(), $e->getCode());
        }
    }

    /**
     * 上传图片永久素材，返回素材media_id
     * @param string $local_url 文件URL地址
     * @param string $type 文件类型
     * @param bool $is_video 是否为视频文件
     * @param array $video_info 视频信息
     * @return string|null
     * @throws \Exception
     */
    public static function uploadForeverMedia($local_url, $type = 'image', $is_video = false, $video_info = [])
    {
        $wechat = new Media(config('wechat.'));
        $map = ['md5' => md5($local_url), 'appid' => $wechat->config->get('appid')];
        if (($media_id = Db::name('WechatNewsMedia')->where($map)->value('media_id'))) {
            return $media_id;
        }
        $result = ['file' => $local_url];
        if (!file_exists($local_url)) {
            $result = FileService::local(basename($local_url), file_get_contents($local_url));
        }
        $result = $wechat->addMaterial($result['file'], $type, $video_info);
        $data = ['md5' => $map['md5'], 'type' => $type, 'appid' => $map['appid'], 'media_id' => $result['media_id'], 'local_url' => $local_url];
        isset($result['url']) && $data['media_url'] = $result['url'];
        Db::name('WechatNewsMedia')->insert($data);
        return $data['media_id'];
    }

    /**
     * 从微信服务器获取所有标签
     * @return bool
     * @throws \Exception
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public static function syncFansTags()
    {
        $wechat = new Tags(config('wechat.'));
        if (!($result = $wechat->getTags())) {
            return false;
        }
        $appid = $wechat->config->get('appid');
        Db::name('WechatFansTags')->where(['appid' => $appid])->delete();
        foreach (array_chunk($result['tags'], 100) as $list) {
            foreach ($list as &$vo) {
                $vo['appid'] = $appid;
            }
            Db::name('WechatFansTags')->insertAll($list);
        }
        return true;
    }

    /**
     * 同步粉丝的标签
     * @param string $openid
     * @return bool
     * @throws \Exception
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public static function syncFansTagsByOpenid($openid)
    {
        $wechat = new Tags(config('wechat.'));
        if (!is_array($tagsid = $wechat->getUserTagId($openid))) {
            return false;
        }
        $data = ['openid' => $openid, 'tagid_list' => join(',', $tagsid)];
        return DataService::save('WechatFans', $data, 'openid', ['appid' => $wechat->config->get('appid')]);
    }

    /**
     * 保存/更新粉丝信息
     * @param array $user
     * @param string $appid
     * @return bool
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public static function setFansInfo($user, $appid = '')
    {
        $user['appid'] = $appid;
        unset($user['privilege']);
        if (!empty($user['subscribe_time'])) {
            $user['subscribe_at'] = date('Y-m-d H:i:s', $user['subscribe_time']);
        }
        if (isset($user['tagid_list']) && is_array($user['tagid_list'])) {
            $user['tagid_list'] = join(',', $user['tagid_list']);
        }
        foreach (['country', 'province', 'city', 'nickname', 'remark'] as $field) {
            isset($user[$field]) && $user[$field] = ToolsService::emojiEncode($user[$field]);
        }
        return DataService::save('WechatFans', $user, 'openid');
    }

    /**
     * 读取粉丝信息
     * @param string $openid 微信用户openid
     * @param string $appid 公众号appid
     * @return array|false
     * @throws \think\db\exception\DataNotFoundException
     * @throws \think\db\exception\ModelNotFoundException
     * @throws \think\exception\DbException
     */
    public static function getFansInfo($openid, $appid = null)
    {
        $map = ['openid' => $openid];
        is_null($appid) || $map['appid'] = $appid;
        $user = Db::name('WechatFans')->where($map)->find();
        foreach (['country', 'province', 'city', 'nickname', 'remark'] as $k) {
            isset($user[$k]) && $user[$k] = ToolsService::emojiDecode($user[$k]);
        }
        return $user;
    }

    /**
     * 同步获取粉丝列表
     * @param string $next_openid
     * @return bool
     * @throws \Exception
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public static function syncFans($next_openid = '')
    {
        $wechat = new User(config('wechat.'));
        $appid = $wechat->config->get('appid');
        if (false === ($result = $wechat->getUserList($next_openid)) || empty($result['data']['openid'])) {
            return false;
        }
        foreach (array_chunk($result['data']['openid'], 100) as $openids) {
            $info = $wechat->getBatchUserInfo($openids);
            foreach ($info as $user) {
                if (false === self::setFansInfo($user, $appid)) {
                    return false;
                }
                if ($result['next_openid'] === $user['openid']) {
                    unset($result['next_openid']);
                }
            }
        }
        return empty($result['next_openid']) ? true : self::syncFans($result['next_openid']);
    }

    /**
     * 同步获取黑名单信息
     * @param string $next_openid
     * @return bool
     * @throws \Exception
     * @throws \think\Exception
     * @throws \think\exception\PDOException
     */
    public static function syncBlack($next_openid = '')
    {
        $wechat = new User(config('wechat.'));
        $appid = $wechat->config->get('appid');
        $result = $wechat->getBlackList($next_openid);
        foreach (array_chunk($result['data']['openid'], 100) as $openids) {
            $info = $wechat->getBatchUserInfo($openids);
            foreach ($info as $user) {
                $user['is_back'] = '1';
                if (false === self::setFansInfo($user, $appid)) {
                    return false;
                }
                if ($result['next_openid'] === $user['openid']) {
                    unset($result['next_openid']);
                }
            }
        }
        return empty($result['next_openid']) ? true : self::syncBlack($result['next_openid']);
    }

}
