<?php

// +----------------------------------------------------------------------
// | Library for ThinkAdmin
// +----------------------------------------------------------------------
// | 版权所有 2014~2018 广州楚才信息科技有限公司 [ http://www.cuci.cc ]
// +----------------------------------------------------------------------
// | 官方网站: http://library.thinkadmin.top
// +----------------------------------------------------------------------
// | 开源协议 ( https://mit-license.org )
// +----------------------------------------------------------------------
// | github 仓库地址 ：https://github.com/zoujingli/ThinkLibrary
// +----------------------------------------------------------------------

namespace library\tools;

/**
 * CSV 导出工具
 * Class Csv
 * @package library\tools
 */
class Csv
{
    /**
     * 写入CSV文件头部
     * @param string $filename 导出文件
     * @param array $headers CSV 头部(一级数组)
     */
    public static function header($filename, array $headers)
    {
        header('Content-Type: application/octet-stream');
        header("Content-Disposition: attachment; filename=" . iconv('utf-8', 'gbk//TRANSLIT', $filename));
        echo @iconv('utf-8', 'gbk//TRANSLIT', "\"" . implode('","', $headers) . "\"\n");
    }

    /**
     * 写入CSV文件内容
     * @param array $list 数据列表(二维数组或多维数组)
     * @param array $maps 数据规则(一维数组)
     */
    public static function body(array $list, array $maps)
    {
        foreach ($list as $data) {
            $rows = [];
            foreach ($maps as $map) {
                $rows[] = @iconv('utf-8', 'gbk//TRANSLIT', Crypt::emojiClear(self::parseKeyDot($data, $map)));
            }
            echo "\"" . implode('","', $rows) . "\"\n";
            flush();
        }
    }

    /**
     * 根据数组key查询(可带点规则)
     * @param array $data 数据
     * @param string $rule 规则，如: order.order_no
     * @return mixed
     */
    private static function parseKeyDot(array $data, $rule)
    {
        list($temp, $attr) = [$data, explode('.', trim($rule, '.'))];
        while ($key = array_shift($attr)) $temp = isset($temp[$key]) ? $temp[$key] : $temp;
        return (is_string($temp) || is_numeric($temp)) ? str_replace('"', '""', "\t{$temp}") : '';
    }
}