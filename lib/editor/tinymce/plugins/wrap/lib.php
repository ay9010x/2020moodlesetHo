<?php

defined('MOODLE_INTERNAL') || die();


class tinymce_wrap extends editor_tinymce_plugin {
    
    protected $buttons = array('wrap');

    protected function update_init_params(array &$params, context $context,
            array $options = null) {

                $this->add_js_plugin($params);
    }
}
