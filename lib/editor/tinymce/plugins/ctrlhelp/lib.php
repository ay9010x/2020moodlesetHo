<?php

defined('MOODLE_INTERNAL') || die();


class tinymce_ctrlhelp extends editor_tinymce_plugin {
    protected function update_init_params(array &$params, context $context, array $options = null) {
        $this->add_js_plugin($params);
    }

    protected function get_sort_order() {
                return 66666;
    }
}
