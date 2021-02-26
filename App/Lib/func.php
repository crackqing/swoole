<?php
namespace App\Lib;

//随意复制网上,适配项目
trait func
{
    public function func_substr_replace($str, $replacement = '*', $start = 3, $length = 5)
    {
        $len = mb_strlen($str, 'utf-8');
        if ($len > intval($start+$length)) {
            $str1 = mb_substr($str, 0, $start, 'utf-8');
            $str2 = mb_substr($str, intval($start+$length), null, 'utf-8');
        } else {
            $str1 = mb_substr($str, 0, 1, 'utf-8');
            $str2 = mb_substr($str, $len-1, 1, 'utf-8');
            $length = $len - 2;
        }
        $new_str = $str1;
        for ($i = 0; $i < $length; $i++) {
            $new_str .= $replacement;
        }
        $new_str .= $str2;
        return $new_str;
    }

    public function getMillisecond()
    {
        list($s1, $s2) = explode(' ', microtime());
        return (float)sprintf('%.0f', (floatval($s1) + floatval($s2)) * 1000);
    }

    public function sort_with_keyName2($arr, $orderby='asc')
    {//desc 从大到小倒序排列， asc递增排序
        $new_array = array();
        $new_sort = array();
        foreach ($arr as $key => $value) {
            $new_array[] = $value;
        }
        if ($orderby=='asc') {
            asort($new_array);
        } else {
            arsort($new_array);
        }
        foreach ($new_array as $k => $v) {
            foreach ($arr as $key => $value) {
                if ($v==$value) {
                    $new_sort[$key] = $value;
                    unset($arr[$key]);
                    break;
                }
            }
        }
        return $new_sort;
    }
}
