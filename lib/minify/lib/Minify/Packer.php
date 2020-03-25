<?php


if (false === (@include 'class.JavaScriptPacker.php')) {
    trigger_error(
        'The script "class.JavaScriptPacker.php" is required. Please see: http:'
        .'//code.google.com/p/minify/source/browse/trunk/min/lib/Minify/Packer.php'
        ,E_USER_ERROR
    );
}


class Minify_Packer {
    public static function minify($code, $options = array())
    {
                $packer = new JavascriptPacker($code, 'Normal', true, false);
        return trim($packer->pack());
    }
}
