<?php



namespace Html2Text;


function mb_internal_encoding($encoding = null) {
    static $internalencoding = 'utf-8';
    if ($encoding !== null) {
        $internalencoding = $encoding;
        return true;
    } else {
        return $internalencoding;
    }
}


function mb_substr($str, $start, $length = null, $encoding = null) {
    if ($encoding === null) {
        $encoding = mb_internal_encoding();
    }
    return \core_text::substr($str, $start, $length, $encoding);
}


function mb_strlen($str, $encoding = null) {
    if ($encoding === null) {
        $encoding = mb_internal_encoding();
    }
    return \core_text::strlen($str, $encoding);
}


function mb_strtolower($str, $encoding = null) {
    if ($encoding === null) {
        $encoding = mb_internal_encoding();
    }
    return \core_text::strtolower($str, $encoding);
}


function mb_strtoupper($str, $encoding = null) {
    if ($encoding === null) {
        $encoding = mb_internal_encoding();
    }
    return \core_text::strtoupper($str, $encoding);
}
