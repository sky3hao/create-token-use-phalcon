<?php

class Utils
{
    const BASECHARS = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890';

    static public function base62_encode($val)
    {
        $baseStr = '';
        $baseChars = self::BASECHARS;
        do {
            $i = $val % 62;
            $baseStr .= $baseChars[$i];
            $val = ($val - $i) / 62;
        } while ($val > 0);
        return $baseStr;
    }

    static public function base62_decode($str)
    {
        $val = 0;
        $len = strlen($str);
        $baseCharArray = array_flip(str_split(self::BASECHARS));

        for ($i = 0; $i < $len; ++$i) {
            $val += $baseCharArray[$str[$i]] * pow(62, $i);
        }
        return $val;
    }
}
