<?php

defined('MOODLE_INTERNAL') || die();


class tinymce_moodlemedia extends editor_tinymce_plugin {
    
    protected $buttons = array('moodlemedia');

    protected function update_init_params(array &$params, context $context,
            array $options = null) {

                if (empty($options['legacy'])) {
            if (isset($options['maxfiles']) and $options['maxfiles'] != 0) {
                $params['file_browser_callback'] = "M.editor_tinymce.filepicker";
            }
        }

        if ($row = $this->find_button($params, 'moodleemoticon')) {
                        $this->add_button_after($params, $row, 'moodlemedia', 'moodleemoticon');
        } else if ($row = $this->find_button($params, 'image')) {
                                                $this->add_button_after($params, $row, 'moodlemedia', 'image');
        } else {
                        $this->add_button_after($params, 1, 'moodlemedia');
        }

                $this->add_js_plugin($params);
    }

    protected function get_sort_order() {
        return 110;
    }
}
