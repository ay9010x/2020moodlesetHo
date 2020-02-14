<?php







class restore_wiki_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('wiki', '/activity/wiki');
        if ($userinfo) {
            $paths[] = new restore_path_element('wiki_subwiki', '/activity/wiki/subwikis/subwiki');
            $paths[] = new restore_path_element('wiki_page', '/activity/wiki/subwikis/subwiki/pages/page');
            $paths[] = new restore_path_element('wiki_version', '/activity/wiki/subwikis/subwiki/pages/page/versions/version');
            $paths[] = new restore_path_element('wiki_tag', '/activity/wiki/subwikis/subwiki/pages/page/tags/tag');
            $paths[] = new restore_path_element('wiki_synonym', '/activity/wiki/subwikis/subwiki/synonyms/synonym');
            $paths[] = new restore_path_element('wiki_link', '/activity/wiki/subwikis/subwiki/links/link');
        }

                return $this->prepare_activity_structure($paths);
    }

    protected function process_wiki($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->course = $this->get_courseid();

        $data->editbegin = $this->apply_date_offset($data->editbegin);
        $data->editend = $this->apply_date_offset($data->editend);
        $data->timemodified = $this->apply_date_offset($data->timemodified);

                $newitemid = $DB->insert_record('wiki', $data);
                $this->apply_activity_instance($newitemid);
    }

    protected function process_wiki_subwiki($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;
        $data->wikiid = $this->get_new_parentid('wiki');

                if ((int) $data->groupid !== 0) {
            $data->groupid = $this->get_mappingid('group', $data->groupid);
        }

                if ((int) $data->userid !== 0) {
            $data->userid = $this->get_mappingid('user', $data->userid);
        }

                if ($data->groupid !== false && $data->userid !== false) {
            $newitemid = $DB->insert_record('wiki_subwikis', $data);
        } else {
            $newitemid = false;
        }

        $this->set_mapping('wiki_subwiki', $oldid, $newitemid, true);
    }

    protected function process_wiki_page($data) {
        global $DB;

        $data = (object) $data;
        $oldid = $data->id;
        $data->subwikiid = $this->get_new_parentid('wiki_subwiki');
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->timemodified = $this->apply_date_offset($data->timemodified);
        $data->timecreated = $this->apply_date_offset($data->timecreated);
        $data->timerendered = $this->apply_date_offset($data->timerendered);

                if ($data->subwikiid !== false) {
            $newitemid = $DB->insert_record('wiki_pages', $data);
        } else {
            $newitemid = false;
        }

        $this->set_mapping('wiki_page', $oldid, $newitemid, true);
    }

    protected function process_wiki_version($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->pageid = $this->get_new_parentid('wiki_page');
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->timecreated = $this->apply_date_offset($data->timecreated);

        $newitemid = $DB->insert_record('wiki_versions', $data);
        $this->set_mapping('wiki_version', $oldid, $newitemid);
    }
    protected function process_wiki_synonym($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->subwikiid = $this->get_new_parentid('wiki_subwiki');
        $data->pageid = $this->get_mappingid('wiki_page', $data->pageid);

        $newitemid = $DB->insert_record('wiki_synonyms', $data);
                    }
    protected function process_wiki_link($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->subwikiid = $this->get_new_parentid('wiki_subwiki');
        $data->frompageid = $this->get_mappingid('wiki_page', $data->frompageid);
        $data->topageid = $this->get_mappingid('wiki_page', $data->topageid);

        $newitemid = $DB->insert_record('wiki_links', $data);
                    }

    protected function process_wiki_tag($data) {
        global $CFG, $DB;

        $data = (object)$data;
        $oldid = $data->id;

        if (!core_tag_tag::is_enabled('mod_wiki', 'wiki_pages')) {             return;
        }

        $tag = $data->rawname;
        $itemid = $this->get_new_parentid('wiki_page');
        $wikiid = $this->get_new_parentid('wiki');

        $context = context_module::instance($this->task->get_moduleid());
        core_tag_tag::add_item_tag('mod_wiki', 'wiki_pages', $itemid, $context, $tag);
    }

    protected function after_execute() {
                $this->add_related_files('mod_wiki', 'intro', null);
        $this->add_related_files('mod_wiki', 'attachments', 'wiki_subwiki');
    }
}
