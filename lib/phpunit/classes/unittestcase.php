<?php





abstract class UnitTestCase extends PHPUnit_Framework_TestCase {

    
    public function expectException($expected, $message = '') {
                if (!$expected) {
            return;
        }
        $this->setExpectedException('moodle_exception', $message);
    }

    
    public function expectError($expected = false, $message = '') {
                if (!$expected) {
            return;
        }
        $this->setExpectedException('PHPUnit_Framework_Error', $message);
    }

    
    public static function assertTrue($actual, $messages = '') {
        parent::assertTrue((bool)$actual, $messages);
    }

    
    public static function assertFalse($actual, $messages = '') {
        parent::assertFalse((bool)$actual, $messages);
    }

    
    public static function assertEqual($expected, $actual, $message = '') {
        parent::assertEquals($expected, $actual, $message);
    }

    
    public static function assertWithinMargin($expected, $actual, $margin, $message = '') {
        parent::assertEquals($expected, $actual, '', $margin, $message);
    }

    
    public static function assertNotEqual($expected, $actual, $message = '') {
        parent::assertNotEquals($expected, $actual, $message);
    }

    
    public static function assertIdentical($expected, $actual, $message = '') {
        parent::assertSame($expected, $actual, $message);
    }

    
    public static function assertNotIdentical($expected, $actual, $message = '') {
        parent::assertNotSame($expected, $actual, $message);
    }

    
    public static function assertIsA($actual, $expected, $message = '') {
        if ($expected === 'array') {
            parent::assertEquals('array', gettype($actual), $message);
        } else {
            parent::assertInstanceOf($expected, $actual, $message);
        }
    }

    
    public static function assertPattern($pattern, $string, $message = '') {
        parent::assertRegExp($pattern, $string, $message);
    }

    
    public static function assertNotPattern($pattern, $string, $message = '') {
        parent::assertNotRegExp($pattern, $string, $message);
    }
}
