<?php



require_once($CFG->dirroot . '/blocks/rss_client/backup/moodle2/backup_rss_client_stepslib.php'); 

class backup_rss_client_block_task extends backup_block_task {

    protected function define_my_settings() {
    }

    protected function define_my_steps() {
                $this->add_step(new backup_rss_client_block_structure_step('rss_client_structure', 'rss_client.xml'));
    }

    public function get_fileareas() {
        return array();     }

    public function get_configdata_encoded_attributes() {
        return array();     }

    static public function encode_content_links($content) {
        return $content;     }
}

