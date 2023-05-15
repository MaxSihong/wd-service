<?php

/**
 * 字符串转大驼峰
 * @param string $str
 * @return string
 * @author: 陈志洪
 * @since: 2023/5/15
 */
if (!function_exists('strToGreatHump')) {
    function strToGreatHump(string $str): string
    {
        $value = ucwords(str_replace(array('-', '_'), ' ', $str));
        return str_replace(' ', '', $value);
    }
}