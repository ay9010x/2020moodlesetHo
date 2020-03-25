<?php



defined('MOODLE_INTERNAL') || die();


class core_collator_testcase extends advanced_testcase {

    
    protected $initiallang = null;

    
    protected $error = null;

    
    protected function setUp() {
        global $SESSION;
        if (isset($SESSION->lang)) {
            $this->initiallang = $SESSION->lang;
        }
        $SESSION->lang = 'en';         if (extension_loaded('intl')) {
            $this->error = 'Collation aware sorting not supported';
        } else {
            $this->error = 'Collation aware sorting not supported, PHP extension "intl" is not available.';
        }
        parent::setUp();
    }

    
    protected function tearDown() {
        global $SESSION;
        parent::tearDown();
        if ($this->initiallang !== null) {
            $SESSION->lang = $this->initiallang;
            $this->initiallang = null;
        } else {
            unset($SESSION->lang);
        }
    }

    
    public function test_asort() {
        $arr = array('b' => 'ab', 1 => 'aa', 0 => 'cc');
        $result = core_collator::asort($arr);
        $this->assertSame(array('aa', 'ab', 'cc'), array_values($arr));
        $this->assertSame(array(1, 'b', 0), array_keys($arr));
        $this->assertTrue($result);

        $arr = array('b' => 'ab', 1 => 'aa', 0 => 'cc');
        $result = core_collator::asort($arr, core_collator::SORT_STRING);
        $this->assertSame(array('aa', 'ab', 'cc'), array_values($arr));
        $this->assertSame(array(1, 'b', 0), array_keys($arr));
        $this->assertTrue($result);

        $arr = array('b' => 'aac', 1 => 'Aac', 0 => 'cc');
        $result = core_collator::asort($arr, (core_collator::SORT_STRING | core_collator::CASE_SENSITIVE));
        $this->assertSame(array('Aac', 'aac', 'cc'), array_values($arr));
        $this->assertSame(array(1, 'b', 0), array_keys($arr));
        $this->assertTrue($result);

        $arr = array('b' => 'a1', 1 => 'a10', 0 => 'a3b');
        $result = core_collator::asort($arr);
        $this->assertSame(array('a1', 'a10', 'a3b'), array_values($arr));
        $this->assertSame(array('b', 1, 0), array_keys($arr));
        $this->assertTrue($result);

        $arr = array('b' => 'a1', 1 => 'a10', 0 => 'a3b');
        $result = core_collator::asort($arr, core_collator::SORT_NATURAL);
        $this->assertSame(array('a1', 'a3b', 'a10'), array_values($arr));
        $this->assertSame(array('b', 0, 1), array_keys($arr));
        $this->assertTrue($result);

        $arr = array('b' => '1.1.1', 1 => '1.2', 0 => '1.20.2');
        $result = core_collator::asort($arr, core_collator::SORT_NATURAL);
        $this->assertSame(array_values($arr), array('1.1.1', '1.2', '1.20.2'));
        $this->assertSame(array_keys($arr), array('b', 1, 0));
        $this->assertTrue($result);

        $arr = array('b' => '-1', 1 => 1000, 0 => -1.2, 3 => 1, 4 => false);
        $result = core_collator::asort($arr, core_collator::SORT_NUMERIC);
        $this->assertSame(array(-1.2, '-1', false, 1, 1000), array_values($arr));
        $this->assertSame(array(0, 'b', 4, 3, 1), array_keys($arr));
        $this->assertTrue($result);

        $arr = array('b' => array(1), 1 => array(2, 3), 0 => 1);
        $result = core_collator::asort($arr, core_collator::SORT_REGULAR);
        $this->assertSame(array(1, array(1), array(2, 3)), array_values($arr));
        $this->assertSame(array(0, 'b', 1), array_keys($arr));
        $this->assertTrue($result);

                $arr = array(0=>array('bb', 'z'), 1=>array('ab', 'a'), 2=>array('zz', 'x'));
        $result = core_collator::asort($arr, core_collator::SORT_REGULAR);
        $this->assertSame(array(1, 0, 2), array_keys($arr));
        $this->assertTrue($result);

        $arr = array('a' => 'áb', 'b' => 'ab', 1 => 'aa', 0=>'cc', 'x' => 'Áb');
        $result = core_collator::asort($arr);
        $this->assertSame(array('aa', 'ab', 'áb', 'Áb', 'cc'), array_values($arr), $this->error);
        $this->assertSame(array(1, 'b', 'a', 'x', 0), array_keys($arr), $this->error);
        $this->assertTrue($result);

        $a = array(2=>'b', 1=>'c');
        $c =& $a;
        $b =& $a;
        core_collator::asort($b);
        $this->assertSame($a, $b);
        $this->assertSame($c, $b);
    }

    
    public function test_asort_objects_by_method() {
        $objects = array(
            'b' => new string_test_class('ab'),
            1 => new string_test_class('aa'),
            0 => new string_test_class('cc')
        );
        $result = core_collator::asort_objects_by_method($objects, 'get_protected_name');
        $this->assertSame(array(1, 'b', 0), array_keys($objects));
        $this->assertSame(array('aa', 'ab', 'cc'), $this->get_ordered_names($objects, 'get_protected_name'));
        $this->assertTrue($result);

        $objects = array(
            'b' => new string_test_class('a20'),
            1 => new string_test_class('a1'),
            0 => new string_test_class('a100')
        );
        $result = core_collator::asort_objects_by_method($objects, 'get_protected_name', core_collator::SORT_NATURAL);
        $this->assertSame(array(1, 'b', 0), array_keys($objects));
        $this->assertSame(array('a1', 'a20', 'a100'), $this->get_ordered_names($objects, 'get_protected_name'));
        $this->assertTrue($result);
    }

    
    public function test_asort_objects_by_property() {
        $objects = array(
            'b' => new string_test_class('ab'),
            1 => new string_test_class('aa'),
            0 => new string_test_class('cc')
        );
        $result = core_collator::asort_objects_by_property($objects, 'publicname');
        $this->assertSame(array(1, 'b', 0), array_keys($objects));
        $this->assertSame(array('aa', 'ab', 'cc'), $this->get_ordered_names($objects, 'publicname'));
        $this->assertTrue($result);

        $objects = array(
            'b' => new string_test_class('a20'),
            1 => new string_test_class('a1'),
            0 => new string_test_class('a100')
        );
        $result = core_collator::asort_objects_by_property($objects, 'publicname', core_collator::SORT_NATURAL);
        $this->assertSame(array(1, 'b', 0), array_keys($objects));
        $this->assertSame(array('a1', 'a20', 'a100'), $this->get_ordered_names($objects, 'publicname'));
        $this->assertTrue($result);
    }

    
    public function test_asort_array_of_arrays_by_key() {
        $array = array(
            'a' => array('name' => 'bravo'),
            'b' => array('name' => 'charlie'),
            'c' => array('name' => 'alpha')
        );
        $this->assertSame(array('a', 'b', 'c'), array_keys($array));
        $this->assertTrue(core_collator::asort_array_of_arrays_by_key($array, 'name'));
        $this->assertSame(array('c', 'a', 'b'), array_keys($array));

        $array = array(
            'a' => array('name' => 'b'),
            'b' => array('name' => 1),
            'c' => array('name' => 0)
        );
        $this->assertSame(array('a', 'b', 'c'), array_keys($array));
        $this->assertTrue(core_collator::asort_array_of_arrays_by_key($array, 'name'));
        $this->assertSame(array('c', 'b', 'a'), array_keys($array));

        $array = array(
            'a' => array('name' => 'áb'),
            'b' => array('name' => 'ab'),
            1   => array('name' => 'aa'),
            'd' => array('name' => 'cc'),
            0   => array('name' => 'Áb')
        );
        $this->assertSame(array('a', 'b', 1, 'd', 0), array_keys($array));
        $this->assertTrue(core_collator::asort_array_of_arrays_by_key($array, 'name'));
        $this->assertSame(array(1, 'b', 'a', 0, 'd'), array_keys($array));
        $this->assertSame(array(
            1   => array('name' => 'aa'),
            'b' => array('name' => 'ab'),
            'a' => array('name' => 'áb'),
            0   => array('name' => 'Áb'),
            'd' => array('name' => 'cc')
        ), $array);

    }

    
    protected function get_ordered_names($objects, $methodproperty = 'get_protected_name') {
        $return = array();
        foreach ($objects as $object) {
            if ($methodproperty == 'publicname') {
                $return[] = $object->publicname;
            } else {
                $return[] = $object->$methodproperty();
            }
        }
        return $return;
    }

    
    public function test_ksort() {
        $arr = array('b' => 'ab', 1 => 'aa', 0 => 'cc');
        $result = core_collator::ksort($arr);
        $this->assertSame(array(0, 1, 'b'), array_keys($arr));
        $this->assertSame(array('cc', 'aa', 'ab'), array_values($arr));
        $this->assertTrue($result);

        $obj = new stdClass();
        $arr = array('1.1.1'=>array(), '1.2'=>$obj, '1.20.2'=>null);
        $result = core_collator::ksort($arr, core_collator::SORT_NATURAL);
        $this->assertSame(array('1.1.1', '1.2', '1.20.2'), array_keys($arr));
        $this->assertSame(array(array(), $obj, null), array_values($arr));
        $this->assertTrue($result);

        $a = array(2=>'b', 1=>'c');
        $c =& $a;
        $b =& $a;
        core_collator::ksort($b);
        $this->assertSame($a, $b);
        $this->assertSame($c, $b);
    }

}



class string_test_class extends stdClass {
    
    public $publicname;
    
    protected $protectedname;
    
    private $privatename;
    
    public function __construct($name) {
        $this->publicname = $name;
        $this->protectedname = $name;
        $this->privatename = $name;
    }
    
    public function get_protected_name() {
        return $this->protectedname;
    }
    
    public function get_private_name() {
        return $this->publicname;
    }
}
