<?php



defined('MOODLE_INTERNAL') || die();

global $CFG;


class tool_task_form_testcase extends advanced_testcase {

    
    public function test_validate_fields_minute() {
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('minute', '*');
        $this->assertTrue($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('minute', '1');
        $this->assertTrue($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('minute', '20');
        $this->assertTrue($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('minute', '65');
        $this->assertFalse($valid);

        $valid = \tool_task_edit_scheduled_task_form::validate_fields('minute', '*/');
        $this->assertFalse($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('minute', '*/1');
        $this->assertTrue($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('minute', '*/20');
        $this->assertTrue($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('minute', '*/65');
        $this->assertFalse($valid);

        $valid = \tool_task_edit_scheduled_task_form::validate_fields('minute', '1,2');
        $this->assertTrue($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('minute', '2,20');
        $this->assertTrue($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('minute', '20,30,45');
        $this->assertTrue($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('minute', '65,20,30');
        $this->assertFalse($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('minute', '25,75');
        $this->assertFalse($valid);

        $valid = \tool_task_edit_scheduled_task_form::validate_fields('minute', '1-2');
        $this->assertTrue($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('minute', '2-20');
        $this->assertTrue($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('minute', '20-30');
        $this->assertTrue($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('minute', '65-20');
        $this->assertFalse($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('minute', '25-75');
        $this->assertFalse($valid);
    }

    
    public function test_validate_fields_hour() {
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('hour', '*');
        $this->assertTrue($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('hour', '1');
        $this->assertTrue($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('hour', '20');
        $this->assertTrue($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('hour', '65');
        $this->assertFalse($valid);

        $valid = \tool_task_edit_scheduled_task_form::validate_fields('hour', '*/');
        $this->assertFalse($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('hour', '*/1');
        $this->assertTrue($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('hour', '*/20');
        $this->assertTrue($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('hour', '*/65');
        $this->assertFalse($valid);

        $valid = \tool_task_edit_scheduled_task_form::validate_fields('hour', '1,2');
        $this->assertTrue($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('hour', '2,20');
        $this->assertTrue($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('hour', '20,30,45');
        $this->assertTrue($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('hour', '65,20,30');
        $this->assertFalse($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('hour', '25,75');
        $this->assertFalse($valid);

        $valid = \tool_task_edit_scheduled_task_form::validate_fields('hour', '1-2');
        $this->assertTrue($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('hour', '2-20');
        $this->assertTrue($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('hour', '20-30');
        $this->assertTrue($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('hour', '65-20');
        $this->assertFalse($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('hour', '25-75');
        $this->assertFalse($valid);
    }

    
    public function test_validate_fields_day() {
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('day', '*');
        $this->assertTrue($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('day', '1');
        $this->assertTrue($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('day', '20');
        $this->assertTrue($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('day', '65');
        $this->assertFalse($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('day', '35');
        $this->assertFalse($valid);

        $valid = \tool_task_edit_scheduled_task_form::validate_fields('day', '*/');
        $this->assertFalse($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('day', '*/1');
        $this->assertTrue($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('day', '*/20');
        $this->assertTrue($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('day', '*/65');
        $this->assertFalse($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('day', '*/35');
        $this->assertFalse($valid);

        $valid = \tool_task_edit_scheduled_task_form::validate_fields('day', '1,2');
        $this->assertTrue($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('day', '2,20');
        $this->assertTrue($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('day', '20,30,25');
        $this->assertTrue($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('day', '65,20,30');
        $this->assertFalse($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('day', '25,35');
        $this->assertFalse($valid);

        $valid = \tool_task_edit_scheduled_task_form::validate_fields('day', '1-2');
        $this->assertTrue($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('day', '2-20');
        $this->assertTrue($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('day', '20-30');
        $this->assertTrue($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('day', '65-20');
        $this->assertFalse($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('day', '25-35');
        $this->assertFalse($valid);
    }

    
    public function test_validate_fields_month() {
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('month', '*');
        $this->assertTrue($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('month', '1');
        $this->assertTrue($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('month', '10');
        $this->assertTrue($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('month', '13');
        $this->assertFalse($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('month', '35');
        $this->assertFalse($valid);

        $valid = \tool_task_edit_scheduled_task_form::validate_fields('month', '*/');
        $this->assertFalse($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('month', '*/1');
        $this->assertTrue($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('month', '*/12');
        $this->assertTrue($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('month', '*/13');
        $this->assertFalse($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('month', '*/35');
        $this->assertFalse($valid);

        $valid = \tool_task_edit_scheduled_task_form::validate_fields('month', '1,2');
        $this->assertTrue($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('month', '2,11');
        $this->assertTrue($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('month', '2,10,12');
        $this->assertTrue($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('month', '65,2,13');
        $this->assertFalse($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('month', '25,35');
        $this->assertFalse($valid);

        $valid = \tool_task_edit_scheduled_task_form::validate_fields('month', '1-2');
        $this->assertTrue($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('month', '2-12');
        $this->assertTrue($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('month', '3-6');
        $this->assertTrue($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('month', '65-2');
        $this->assertFalse($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('month', '25-26');
        $this->assertFalse($valid);
    }

    
    public function test_validate_fields_dayofweek() {
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('dayofweek', '*');
        $this->assertTrue($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('dayofweek', '0');
        $this->assertTrue($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('dayofweek', '1');
        $this->assertTrue($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('dayofweek', '6');
        $this->assertTrue($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('dayofweek', '7');
        $this->assertFalse($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('dayofweek', '20');
        $this->assertFalse($valid);

        $valid = \tool_task_edit_scheduled_task_form::validate_fields('dayofweek', '*/');
        $this->assertFalse($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('dayofweek', '*/1');
        $this->assertTrue($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('dayofweek', '*/6');
        $this->assertTrue($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('dayofweek', '*/13');
        $this->assertFalse($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('dayofweek', '*/35');
        $this->assertFalse($valid);

        $valid = \tool_task_edit_scheduled_task_form::validate_fields('dayofweek', '1,2');
        $this->assertTrue($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('dayofweek', '2,6');
        $this->assertTrue($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('dayofweek', '2,6,3');
        $this->assertTrue($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('dayofweek', '65,2,13');
        $this->assertFalse($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('dayofweek', '25,35');
        $this->assertFalse($valid);

        $valid = \tool_task_edit_scheduled_task_form::validate_fields('dayofweek', '1-2');
        $this->assertTrue($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('dayofweek', '2-6');
        $this->assertTrue($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('dayofweek', '65-2');
        $this->assertFalse($valid);
        $valid = \tool_task_edit_scheduled_task_form::validate_fields('dayofweek', '3-7');
        $this->assertFalse($valid);
    }
}

