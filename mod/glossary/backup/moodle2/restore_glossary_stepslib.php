<?php







class restore_glossary_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('glossary', '/activity/glossary');
        $paths[] = new restore_path_element('glossary_category', '/activity/glossary/categories/category');
        if ($userinfo) {
            $paths[] = new restore_path_element('glossary_entry', '/activity/glossary/entries/entry');
            $paths[] = new restore_path_element('glossary_alias', '/activity/glossary/entries/entry/aliases/alias');
            $paths[] = new restore_path_element('glossary_rating', '/activity/glossary/entries/entry/ratings/rating');
            $paths[] = new restore_path_element('glossary_category_entry',
                                                '/activity/glossary/categories/category/category_entries/category_entry');
        }

                return $this->prepare_activity_structure($paths);
    }

    protected function process_glossary($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        $data->assesstimestart = $this->apply_date_offset($data->assesstimestart);
        $data->assesstimefinish = $this->apply_date_offset($data->assesstimefinish);
        if ($data->scale < 0) {             $data->scale = -($this->get_mappingid('scale', abs($data->scale)));
        }
        $formats = get_list_of_plugins('mod/glossary/formats');         if (!in_array($data->displayformat, $formats)) {
            $data->displayformat = 'dictionary';
        }
        if (!empty($data->mainglossary) and $data->mainglossary == 1 and
            $DB->record_exists('glossary', array('mainglossary' => 1, 'course' => $this->get_courseid()))) {
                        $data->mainglossary = 0;
        }

                $newitemid = $DB->insert_record('glossary', $data);
        $this->apply_activity_instance($newitemid);
    }

    protected function process_glossary_entry($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->glossaryid = $this->get_new_parentid('glossary');
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->sourceglossaryid = $this->get_mappingid('glossary', $data->sourceglossaryid);

        $data->timecreated = $this->apply_date_offset($data->timecreated);
        $data->timemodified = $this->apply_date_offset($data->timemodified);

                $newitemid = $DB->insert_record('glossary_entries', $data);
        $this->set_mapping('glossary_entry', $oldid, $newitemid, true);     }

    protected function process_glossary_alias($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->entryid = $this->get_new_parentid('glossary_entry');
        $data->alias =  $data->alias_text;
        $newitemid = $DB->insert_record('glossary_alias', $data);
    }

    protected function process_glossary_rating($data) {
        global $DB;

        $data = (object)$data;

                $data->contextid = $this->task->get_contextid();
        $data->itemid    = $this->get_new_parentid('glossary_entry');
        if ($data->scaleid < 0) {             $data->scaleid = -($this->get_mappingid('scale', abs($data->scaleid)));
        }
        $data->rating = $data->value;
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->timecreated = $this->apply_date_offset($data->timecreated);
        $data->timemodified = $this->apply_date_offset($data->timemodified);

                        if (empty($data->component)) {
            $data->component = 'mod_glossary';
        }
        if (empty($data->ratingarea)) {
            $data->ratingarea = 'entry';
        }

        $newitemid = $DB->insert_record('rating', $data);
    }

    protected function process_glossary_category($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->glossaryid = $this->get_new_parentid('glossary');
        $newitemid = $DB->insert_record('glossary_categories', $data);
        $this->set_mapping('glossary_category', $oldid, $newitemid);
    }

    protected function process_glossary_category_entry($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->categoryid = $this->get_new_parentid('glossary_category');
        $data->entryid    = $this->get_mappingid('glossary_entry', $data->entryid);
        $newitemid = $DB->insert_record('glossary_entries_categories', $data);
    }

    protected function after_execute() {
                $this->add_related_files('mod_glossary', 'intro', null);
                $this->add_related_files('mod_glossary', 'entry', 'glossary_entry');
        $this->add_related_files('mod_glossary', 'attachment', 'glossary_entry');
    }
}
