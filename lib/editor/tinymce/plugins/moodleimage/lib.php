<?php

defined('MOODLE_INTERNAL') || die();


class tinymce_moodleimage extends editor_tinymce_plugin {
    
    protected $buttons = array('image');

    protected function update_init_params(array &$params, context $context,
            array $options = null) {

                if (empty($options['legacy'])) {
            if (isset($options['maxfiles']) and $options['maxfiles'] != 0) {
                $params['file_browser_callback'] = "M.editor_tinymce.filepicker";
            }
        }

        
                $this->add_js_plugin($params);
    }

    protected function get_sort_order() {
        return 110;
    }
}
