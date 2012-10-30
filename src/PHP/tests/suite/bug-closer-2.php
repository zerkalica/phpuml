<?php

$netstat = function($return = null, $precision = 2) {
    // supplement function
    $_convert = function ($bytes) use ($precision) {
        $i = 0; $iec = array('b', 'kb', 'mb', 'gb', 'tb', 'pb', 'eb', 'zb', 'yb');
        while (($bytes / 1024) > 1): $bytes = $bytes / 1024; $i++; endwhile;
        return round(substr($bytes, 0, strpos($bytes, '.') + 4), $precision) . ' ' . strtoupper($iec[$i]);
    };

    foreach (explode("\n", `netstat -e`) as $d) {
        if (preg_match('/^Bytes([\s].+)([0-9])([\s].+)([0-9])/', $d, $m)) {
            switch ($return) {
                case 'sent':    return $_convert(trim($m[1].$m[2])); break;
                case 'recv':    return $_convert(trim($m[3].$m[4])); break;
                default:        return array('sent' => $_convert(trim($m[1].$m[2])),
                                             'recv' => $_convert(trim($m[3].$m[4]))); break;
            }
        }
    }
};

function test() {

}


?>