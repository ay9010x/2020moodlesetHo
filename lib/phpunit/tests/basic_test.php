<?php



defined('MOODLE_INTERNAL') || die();



class core_phpunit_basic_testcase extends basic_testcase {
    protected $testassertexecuted = false;

    protected function setUp() {
        parent::setUp();
        if ($this->getName() === 'test_setup_assert') {
            $this->assertTrue(true);
            $this->testassertexecuted = true;
            return;
        }
    }

    
    public function test_bootstrap() {
        global $CFG;
        $this->assertTrue(isset($CFG->httpswwwroot));
        $this->assertEquals($CFG->httpswwwroot, $CFG->wwwroot);
        $this->assertEquals($CFG->prefix, $CFG->phpunit_prefix);
    }

    
    public function test_assert_behaviour() {
                $a = array('a', 'b', 'c');
        $b = array('a', 'c', 'b');
        $c = array('a', 'b', 'c');
        $d = array('a', 'b', 'C');
        $this->assertNotEquals($a, $b);
        $this->assertNotEquals($a, $d);
        $this->assertEquals($a, $c);
        $this->assertEquals($a, $b, '', 0, 10, true);

                $a = new stdClass();
        $a->x = 'x';
        $a->y = 'y';
        $b = new stdClass();         $b->y = 'y';
        $b->x = 'x';
        $c = $a;
        $d = new stdClass();
        $d->x = 'x';
        $d->y = 'y';
        $d->z = 'z';
        $this->assertEquals($a, $b);
        $this->assertNotSame($a, $b);
        $this->assertEquals($a, $c);
        $this->assertSame($a, $c);
        $this->assertNotEquals($a, $d);

                $this->assertEquals(1, '1');
        $this->assertEquals(null, '');

        $this->assertNotEquals(1, '1 ');
        $this->assertNotEquals(0, '');
        $this->assertNotEquals(null, '0');
        $this->assertNotEquals(array(), '');

                $this->assertEquals(null, null);
        $this->assertEquals(false, null);
        $this->assertEquals(0, null);

                $this->assertEmpty(0);
        $this->assertEmpty(0.0);
        $this->assertEmpty('');
        $this->assertEmpty('0');
        $this->assertEmpty(false);
        $this->assertEmpty(null);
        $this->assertEmpty(array());

        $this->assertNotEmpty(1);
        $this->assertNotEmpty(0.1);
        $this->assertNotEmpty(-1);
        $this->assertNotEmpty(' ');
        $this->assertNotEmpty('0 ');
        $this->assertNotEmpty(true);
        $this->assertNotEmpty(array(null));
        $this->assertNotEmpty(new stdClass());
    }

    
    public function test_lineendings() {
        $string = <<<STRING
a
b
STRING;
        $this->assertSame("a\nb", $string, 'Make sure all project files are checked out with unix line endings.');

    }

    
    public function test_setup_assert() {
        $this->assertTrue($this->testassertexecuted);
        $this->testassertexecuted = false;
    }

        
}
