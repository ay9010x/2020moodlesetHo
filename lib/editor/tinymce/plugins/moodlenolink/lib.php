<?php

defined('MOODLE_INTERNAL') || die();


class tinymce_moodlenolink extends editor_tinymce_plugin {
    
    protected $buttons = array('moodlenolink');

    protected function update_init_params(array &$params, context $context,
            array $options = null) {

        if ($row = $this->find_button($params, 'unlink')) {
                        $this->add_button_after($params, $row, 'moodlenolink', 'unlink');
        } else {
                        $this->add_button_after($params, 1, 'moodlenolink');
        }

                $this->add_js_plugin($params);
    }
}
