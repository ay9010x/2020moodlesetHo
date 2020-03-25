<?php



require_once($CFG->dirroot . '/blocks/rss_client/backup/moodle2/restore_rss_client_stepslib.php'); 

class restore_rss_client_block_task extends restore_block_task {

    protected function define_my_settings() {
    }

    protected function define_my_steps() {
                $this->add_step(new restore_rss_client_block_structure_step('rss_client_structure', 'rss_client.xml'));
    }

    public function get_fileareas() {
        return array();     }

    public function get_configdata_encoded_attributes() {
        return array();     }

    static public function define_decode_contents() {
        return array();
    }

    static public function define_decode_rules() {
        return array();
    }
}

