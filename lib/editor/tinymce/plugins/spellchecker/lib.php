<?php

defined('MOODLE_INTERNAL') || die();


class tinymce_spellchecker extends editor_tinymce_plugin {
    
    protected $buttons = array('spellchecker');

    protected function update_init_params(array &$params, context $context,
            array $options = null) {
        global $CFG;

        if (!$this->is_legacy_browser()) {
            return;
        }

                $engine = $this->get_config('spellengine', '');
        if (!$engine or $engine === 'GoogleSpell') {
            return;
        }

                $spelllanguagelist = $this->get_config('spelllanguagelist', '');
        if ($spelllanguagelist !== '') {
                        unset($params['gecko_spellcheck']);

            if ($row = $this->find_button($params, 'code')) {
                                $this->add_button_after($params, $row, 'spellchecker', 'code');
            }

                        $this->add_js_plugin($params);
            $params['spellchecker_rpc_url'] = $CFG->httpswwwroot .
                    '/lib/editor/tinymce/plugins/spellchecker/rpc.php';
            $params['spellchecker_languages'] = $spelllanguagelist;
        }
    }

    protected function is_legacy_browser() {
                if (core_useragent::is_ie() and !core_useragent::check_ie_version(10)) {
            return true;
        }
                return false;
    }
}
