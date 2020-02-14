<?php




class mod_glossary_generator_testcase extends advanced_testcase {

    public function test_create_instance() {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();

        $this->assertFalse($DB->record_exists('glossary', array('course' => $course->id)));
        $glossary = $this->getDataGenerator()->create_module('glossary', array('course' => $course));
        $records = $DB->get_records('glossary', array('course' => $course->id), 'id');
        $this->assertCount(1, $records);
        $this->assertTrue(array_key_exists($glossary->id, $records));

        $params = array('course' => $course->id, 'name' => 'Another glossary');
        $glossary = $this->getDataGenerator()->create_module('glossary', $params);
        $records = $DB->get_records('glossary', array('course' => $course->id), 'id');
        $this->assertCount(2, $records);
        $this->assertEquals('Another glossary', $records[$glossary->id]->name);
    }

    public function test_create_content() {
        global $DB;
        $this->resetAfterTest();
        $this->setAdminUser();

        $course = $this->getDataGenerator()->create_course();
        $glossary = $this->getDataGenerator()->create_module('glossary', array('course' => $course));
        
        $glossarygenerator = $this->getDataGenerator()->get_plugin_generator('mod_glossary');

        $entry1 = $glossarygenerator->create_content($glossary);
        $entry2 = $glossarygenerator->create_content($glossary, array('concept' => 'Custom concept'), array('alias1', 'alias2'));
        $records = $DB->get_records('glossary_entries', array('glossaryid' => $glossary->id), 'id');
        $this->assertCount(2, $records);
        $this->assertEquals($entry1->id, $records[$entry1->id]->id);
        $this->assertEquals($entry2->id, $records[$entry2->id]->id);
        $this->assertEquals('Custom concept', $records[$entry2->id]->concept);
        $aliases = $DB->get_records_menu('glossary_alias', array('entryid' => $entry2->id), 'id ASC', 'id, alias');
        $this->assertSame(array('alias1', 'alias2'), array_values($aliases));

                $categories = $DB->get_records('glossary_categories', array('glossaryid' => $glossary->id));
        $this->assertCount(0, $categories);
        $entry3 = $glossarygenerator->create_content($glossary, array('concept' => 'In category'));
        $category1 = $glossarygenerator->create_category($glossary, array());
        $categories = $DB->get_records('glossary_categories', array('glossaryid' => $glossary->id));
        $this->assertCount(1, $categories);
        $category2 = $glossarygenerator->create_category($glossary, array('name' => 'Some category'), array($entry2, $entry3));
        $categories = $DB->get_records('glossary_categories', array('glossaryid' => $glossary->id));
        $this->assertCount(2, $categories);
        $members = $DB->get_records_menu('glossary_entries_categories', array('categoryid' => $category2->id), 'id ASC', 'id, entryid');
        $this->assertSame(array($entry2->id, $entry3->id), array_values($members));
    }
}
