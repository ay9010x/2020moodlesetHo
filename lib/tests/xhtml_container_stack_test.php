<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir . '/outputlib.php');



class core_xhtml_container_stack_testcase extends advanced_testcase {
    public function test_push_then_pop() {
                $stack = new xhtml_container_stack();
                $stack->push('testtype', '</div>');
        $html = $stack->pop('testtype');
                $this->assertEquals('</div>', $html);
        $this->assertDebuggingNotCalled();
    }

    public function test_mismatched_pop_prints_warning() {
                $stack = new xhtml_container_stack();
        $stack->push('testtype', '</div>');
                $html = $stack->pop('mismatch');
                $this->assertEquals('</div>', $html);
        $this->assertDebuggingCalled();
    }

    public function test_pop_when_empty_prints_warning() {
                $stack = new xhtml_container_stack();
                $html = $stack->pop('testtype');
                $this->assertEquals('', $html);
        $this->assertDebuggingCalled();
    }

    public function test_correct_nesting() {
                $stack = new xhtml_container_stack();
                $stack->push('testdiv', '</div>');
        $stack->push('testp', '</p>');
        $html2 = $stack->pop('testp');
        $html1 = $stack->pop('testdiv');
                $this->assertEquals('</p>', $html2);
        $this->assertEquals('</div>', $html1);
        $this->assertDebuggingNotCalled();
    }

    public function test_pop_all_but_last() {
                $stack = new xhtml_container_stack();
        $stack->push('test1', '</h1>');
        $stack->push('test2', '</h2>');
        $stack->push('test3', '</h3>');
                $html = $stack->pop_all_but_last();
                $this->assertEquals('</h3></h2>', $html);
        $this->assertDebuggingNotCalled();
                $stack->discard();
    }

    public function test_pop_all_but_last_only_one() {
                $stack = new xhtml_container_stack();
        $stack->push('test1', '</h1>');
                $html = $stack->pop_all_but_last();
                $this->assertEquals('', $html);
        $this->assertDebuggingNotCalled();
                $stack->discard();
    }

    public function test_pop_all_but_last_empty() {
                $stack = new xhtml_container_stack();
                $html = $stack->pop_all_but_last();
                $this->assertEquals('', $html);
        $this->assertDebuggingNotCalled();
    }

    public function test_discard() {
                $stack = new xhtml_container_stack();
        $stack->push('test1', '</somethingdistinctive>');
        $stack->discard();
                $stack = null;
                $this->assertDebuggingNotCalled();
    }
}
