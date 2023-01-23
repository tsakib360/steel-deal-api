<?php

if(!function_exists('randomString'))
{
    function randomString($a)
    {
        $x = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
        $c = strlen($x) - 1;
        $z = '';
        for ($i = 0; $i < $a; $i++) {
            $y = rand(0, $c);
            $z .= substr($x, $y, 1);
        }
        return $z;
    }
}

if(!function_exists('randomNumber'))
{
    function randomNumber($a)
    {
        $x = '0123456789';
        $c = strlen($x) - 1;
        $z = '';
        for ($i = 0; $i < $a; $i++) {
            $y = rand(0, $c);
            $z .= substr($x, $y, 1);
        }
        return $z;
    }
}
