<?php




class backup_html_block_task extends backup_block_task {

    protected function define_my_settings() {
    }

    protected function define_my_steps() {
    }

    public function get_fileareas() {
        return array('content');
    }

    public function get_configdata_encoded_attributes() {
        return array('text');     }

    static public function encode_content_links($content) {
        return $content;     }
}

