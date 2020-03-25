<?php



defined('MOODLE_INTERNAL') || die();

require_once(__DIR__ . '/fixtures/lib.php');


class core_grade_object_testcase extends grade_base_testcase {

    public function test_fetch_all_helper() {
                $params = array('id' => $this->grade_items[0]->id);
        $items = grade_object::fetch_all_helper('grade_items', 'grade_item', $params);
        $this->assertCount(1, $items);
        $item = array_shift($items);
        $this->assertInstanceOf('grade_item', $item);
        $this->assertEquals($item->id, $this->grade_items[0]->id);

                $params = array('courseid' => $this->course->id, 'categoryid' => $this->grade_categories[1]->id);
        $items = grade_object::fetch_all_helper('grade_items', 'grade_item', $params);
        $this->assertCount(2, $items);
        $expecteditems = array($this->grade_items[0]->id => true, $this->grade_items[1]->id => true);
        foreach ($items as $item) {
            $this->assertInstanceOf('grade_item', $item);
            $this->assertArrayHasKey($item->id, $expecteditems);
            unset($expecteditems[$item->id]);
        }

                $params = array('iteminfo' => $this->grade_items[2]->iteminfo);
        $items = grade_object::fetch_all_helper('grade_items', 'grade_item', $params);
        $this->assertCount(1, $items);
        $item = array_shift($items);
        $this->assertInstanceOf('grade_item', $item);
        $this->assertEquals($item->id, $this->grade_items[2]->id);

                $params = array('doesnotexist' => 'ignoreme', 'id' => $this->grade_items[0]->id);
        $items = grade_object::fetch_all_helper('grade_items', 'grade_item', $params);
        $this->assertCount(1, $items);
        $item = array_shift($items);
        $this->assertInstanceOf('grade_item', $item);
        $this->assertEquals($item->id, $this->grade_items[0]->id);
    }

}
