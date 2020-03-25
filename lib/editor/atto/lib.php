<?php



defined('MOODLE_INTERNAL') || die();


class atto_texteditor extends texteditor {

    
    public function supported_by_browser() {
        return true;
    }

    
    public function get_supported_formats() {
                return array(FORMAT_HTML => FORMAT_HTML);
    }

    
    public function get_preferred_format() {
        return FORMAT_HTML;
    }

    
    public function supports_repositories() {
        return true;
    }

    
    public function use_editor($elementid, array $options=null, $fpoptions=null) {
        global $PAGE;

        if (array_key_exists('atto:toolbar', $options)) {
            $configstr = $options['atto:toolbar'];
        } else {
            $configstr = get_config('editor_atto', 'toolbar');
        }

        $grouplines = explode("\n", $configstr);

        $groups = array();

        foreach ($grouplines as $groupline) {
            $line = explode('=', $groupline);
            if (count($line) > 1) {
                $group = trim(array_shift($line));
                $plugins = array_map('trim', explode(',', array_shift($line)));
                $groups[$group] = $plugins;
            }
        }

        $modules = array('moodle-editor_atto-editor');
        $options['context'] = empty($options['context']) ? context_system::instance() : $options['context'];

        $jsplugins = array();
        foreach ($groups as $group => $plugins) {
            $groupplugins = array();
            foreach ($plugins as $plugin) {
                                if (!core_component::get_component_directory('atto_' . $plugin))  {
                    continue;
                }

                                if ($plugin == 'managefiles' && isset($options['enable_filemanagement']) && !$options['enable_filemanagement']) {
                    continue;
                }

                $jsplugin = array();
                $jsplugin['name'] = $plugin;
                $jsplugin['params'] = array();
                $modules[] = 'moodle-atto_' . $plugin . '-button';

                component_callback('atto_' . $plugin, 'strings_for_js');
                $extra = component_callback('atto_' . $plugin, 'params_for_js', array($elementid, $options, $fpoptions));

                if ($extra) {
                    $jsplugin = array_merge($jsplugin, $extra);
                }
                                $PAGE->requires->string_for_js('pluginname', 'atto_' . $plugin);
                $groupplugins[] = $jsplugin;
            }
            $jsplugins[] = array('group'=>$group, 'plugins'=>$groupplugins);
        }

        $PAGE->requires->strings_for_js(array(
                'editor_command_keycode',
                'editor_control_keycode',
                'plugin_title_shortcut',
                'textrecovered',
                'autosavefailed',
                'autosavesucceeded',
                'errortextrecovery'
            ), 'editor_atto');
        $PAGE->requires->strings_for_js(array(
                'warning',
                'info'
            ), 'moodle');
        $PAGE->requires->yui_module($modules,
                                    'Y.M.editor_atto.Editor.init',
                                    array($this->get_init_params($elementid, $options, $fpoptions, $jsplugins)));

    }

    
    protected function get_init_params($elementid, array $options = null, array $fpoptions = null, $plugins = null) {
        global $PAGE;

        $directionality = get_string('thisdirection', 'langconfig');
        $strtime        = get_string('strftimetime');
        $strdate        = get_string('strftimedaydate');
        $lang           = current_language();
        $autosave       = true;
        $autosavefrequency = get_config('editor_atto', 'autosavefrequency');
        if (isset($options['autosave'])) {
            $autosave       = $options['autosave'];
        }
        $contentcss     = $PAGE->theme->editor_css_url()->out(false);

                if (isguestuser()) {
            $autosave = false;
        }
                $pagehash = sha1($PAGE->url . '<>' . s($this->get_text()));
        $params = array(
            'elementid' => $elementid,
            'content_css' => $contentcss,
            'contextid' => $options['context']->id,
            'autosaveEnabled' => $autosave,
            'autosaveFrequency' => $autosavefrequency,
            'language' => $lang,
            'directionality' => $directionality,
            'filepickeroptions' => array(),
            'plugins' => $plugins,
            'pageHash' => $pagehash,
        );
        if ($fpoptions) {
            $params['filepickeroptions'] = $fpoptions;
        }
        return $params;
    }
}
