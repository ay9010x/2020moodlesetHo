<?php





abstract class base_testcase extends PHPUnit_Framework_TestCase {
    
    public static function assertTag($matcher, $actual, $message = '', $ishtml = true) {
        $dom = PHPUnit_Util_XML::load($actual, $ishtml);
        $tags = PHPUnit_Util_XML::findNodes($dom, $matcher, $ishtml);
        $matched = count($tags) > 0 && $tags[0] instanceof DOMNode;
        self::assertTrue($matched, $message);
    }

    
    public static function assertNotTag($matcher, $actual, $message = '', $ishtml = true) {
        $dom = PHPUnit_Util_XML::load($actual, $ishtml);
        $tags = PHPUnit_Util_XML::findNodes($dom, $matcher, $ishtml);
        $matched = count($tags) > 0 && $tags[0] instanceof DOMNode;
        self::assertFalse($matched, $message);
    }
}
