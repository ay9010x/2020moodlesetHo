<?php

defined('MOODLE_INTERNAL') || die();


class tinymce_managefiles extends editor_tinymce_plugin {
    
    protected $buttons = array('managefiles');

    
    protected function update_init_params(array &$params, context $context,
            array $options = null) {
        global $USER;

        if (!isloggedin() or isguestuser()) {
                        return;
        }
        if (!isset($options['maxfiles']) or $options['maxfiles'] == 0) {
                        return;
        }

                $params['managefiles'] = array('usercontext' => context_user::instance($USER->id)->id);
        foreach (array('itemid', 'context', 'areamaxbytes', 'maxbytes', 'subdirs', 'return_types') as $key) {
            if (isset($options[$key])) {
                if ($key === 'context' && is_object($options[$key])) {
                                        $params['managefiles'][$key] = $options[$key]->id;
                } else {
                    $params['managefiles'][$key] = $options[$key];
                }
            }
        }

        if ($row = $this->find_button($params, 'moodlemedia')) {
                        $this->add_button_after($params, $row, 'managefiles', 'moodlemedia');
        } else if ($row = $this->find_button($params, 'image')) {
                        $this->add_button_after($params, $row, 'managefiles', 'image');
        } else {
                        $this->add_button_after($params, $this->count_button_rows($params), 'managefiles');
        }

                $this->add_js_plugin($params);
    }

    protected function get_sort_order() {
        return 310;
    }
}
