<?php

defined('MOODLE_INTERNAL') || die();


class tinymce_pdw extends editor_tinymce_plugin {
    
    protected function update_init_params(array &$params, context $context,
            array $options = null) {

        $rowsnumber = $this->count_button_rows($params);
        if ($rowsnumber > 1) {
            $this->add_button_before($params, 1, 'pdw_toggle', '');
            $params['pdw_toggle_on'] = 1;
            $params['pdw_toggle_toolbars'] = join(',', range(2, $rowsnumber));

                        $this->add_js_plugin($params);
        }
    }

    
    protected function get_sort_order() {
        return 100000;
    }
}
