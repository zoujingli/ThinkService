<?php

// +----------------------------------------------------------------------
// | ThinkService
// +----------------------------------------------------------------------
// | 版权所有 2014~2017 广州楚才信息科技有限公司 [ http://www.cuci.cc ]
// +----------------------------------------------------------------------
// | 官方网站: http://think.ctolog.com
// +----------------------------------------------------------------------
// | 开源协议 ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | github开源项目：https://github.com/zoujingli/ThinkService
// +----------------------------------------------------------------------

namespace service;

use think\Request;

/**
 * 百度统计服务端代码
 * Class BaiduService
 * @package service
 */
class BaiduService
{
    private $request;
    private $VERSION = "wap-0-0.2";
    private $VISIT_DURATION = 1800;
    private $VISITOR_MAX_AGE = 31536000;

    private $SEARCH_ENGINE_LIST = [
        ["1", "baidu.com", "word|wd"],
        ["2", "google.com", "q"],
        ["4", "sogou.com", "query"],
        ["6", "search.yahoo.com", "p"],
        ["7", "yahoo.cn", "q"],
        ["8", "soso.com", "w"],
        ["11", "youdao.com", "q"],
        ["12", "gougou.com", "search"],
        ["13", "bing.com", "q"],
        ["14", "so.com", "q"],
        ["14", "so.360.cn", "q"],
        ["15", "jike.com", "q"],
        ["16", "qihoo.com", "kw"],
        ["17", "etao.com", "q"],
        ["18", "soku.com", "keyword"],
    ];

    private $siteId = "";
    private $searchEngine = "";
    private $searchWord = "";
    private $visitUrl = "";
    private $eventType = 0;
    private $eventProperty = "";

    public function __construct($siteId)
    {
        $this->siteId = $siteId;
        $this->request = Request::instance();
    }

    public function setAccount($siteId)
    {
        $this->siteId = $siteId;
    }

    public function trackPageView($url = null)
    {
        list($this->eventType, $this->visitUrl, $this->eventProperty) = [0, '', ''];
        if (!is_null($url) && strpos($url, "/") === 0) {
            $this->visitUrl = $this->request->url(true);
        }
        return $this->getPixelUrl();
    }

    private function getQueryValue($url, $key)
    {
        preg_match("/(^|&|\\?|#)(" . $key . ")=([^&#]*)(&|$|#)/", $url, $matches);
        return count($matches) > 0 ? $matches[3] : null;
    }

    private function getSourceType($path, $referer, $currentPageVisitTime, $lastPageVisitTime)
    {
        list($parsedPath, $parsedReferer) = [parse_url($path), parse_url($referer)];
        if (is_null($referer) || (!is_null($parsedPath) && !is_null($parsedReferer) && $parsedPath["host"] === $parsedReferer["host"])) {
            return ($currentPageVisitTime - $lastPageVisitTime > $this->VISIT_DURATION) ? 1 : 4;
        }
        $sel = $this->SEARCH_ENGINE_LIST;
        for ($i = 0, $l = count($sel); $i < $l; $i++) {
            if (preg_match("/" . $sel[$i][1] . "/", $parsedReferer["host"])) {
                $this->searchWord = $this->getQueryValue($referer, $sel[$i][2]);
                if (!is_null($this->searchWord) || $sel[$i][0] === "2" || $sel[$i][0] === "14" || $sel[$i][0] === "17") {
                    $this->searchEngine = $sel[$i][0];
                    return 2;
                }
            }
        }
        return 3;
    }

    private function getPixelUrl()
    {
        list($referer, $currentPageVisitTime) = [$this->request->server('HTTP_REFERER'), time()];
        list($lastPageVisitTime, $lastVisitTime) = [(int)cookie("Hm_lpvt_" . $this->siteId), cookie("Hm_lvt_" . $this->siteId)];

        $sourceType = $this->getSourceType($this->request->url(true), $referer, $currentPageVisitTime, $lastPageVisitTime);
        $isNewVisit = ($sourceType == 4) ? 0 : 1;

        cookie("Hm_lpvt_{$this->siteId}", $currentPageVisitTime, ['path' => '/', 'prefix' => '', 'expire' => 0]);
        cookie("Hm_lvt_{$this->siteId}", $currentPageVisitTime, ['path' => '/', 'prefix' => '', 'expire' => time() + $this->VISITOR_MAX_AGE]);

        $pixelUrl = "http://hm.baidu.com/hm.gif?si={$this->siteId}&et={$this->eventType}&nv={$isNewVisit}&st={$sourceType}" .
            (is_null($lastVisitTime) ? "" : "&lt={$lastVisitTime}") . (is_null($referer) ? "" : "&su=" . urlencode($referer)) .
            ($this->searchEngine !== "" ? "&se={$this->searchEngine}" : "") . ($this->eventProperty !== "" ? "&ep={$this->eventProperty}" : "") .
            ($this->visitUrl !== "" ? "&u=" . urlencode($this->visitUrl) : "") . ($this->searchWord !== "" ? "&sw=" . urlencode($this->searchWord) : "") .
            "&v={$this->VERSION}&rnd=" . rand(10e8, 10e9);
        return $pixelUrl;//htmlspecialchars($pixelUrl);
    }
}