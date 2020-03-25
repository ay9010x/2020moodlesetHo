<?php



defined('MOODLE_INTERNAL') || die();

class tinymce_texteditor extends texteditor {
    
    public $version = '3.5.11';

    
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

    
    public function head_setup() {
    }

    
    public function use_editor($elementid, array $options=null, $fpoptions=null) {
        global $PAGE, $CFG;
                if ($CFG->debugdeveloper) {
            $PAGE->requires->js(new moodle_url($CFG->httpswwwroot.'/lib/editor/tinymce/tiny_mce/'.$this->version.'/tiny_mce_src.js'));
        } else {
            $PAGE->requires->js(new moodle_url($CFG->httpswwwroot.'/lib/editor/tinymce/tiny_mce/'.$this->version.'/tiny_mce.js'));
        }
        $PAGE->requires->js_init_call('M.editor_tinymce.init_editor', array($elementid, $this->get_init_params($elementid, $options)), true);
        if ($fpoptions) {
            $PAGE->requires->js_init_call('M.editor_tinymce.init_filepicker', array($elementid, $fpoptions), true);
        }
    }

    protected function get_init_params($elementid, array $options=null) {
        global $CFG, $PAGE, $OUTPUT;

        
        $directionality = get_string('thisdirection', 'langconfig');
        $strtime        = get_string('strftimetime');
        $strdate        = get_string('strftimedaydate');
        $lang           = current_language();
        $contentcss     = $PAGE->theme->editor_css_url()->out(false);

        $context = empty($options['context']) ? context_system::instance() : $options['context'];

        $config = get_config('editor_tinymce');
        if (!isset($config->disabledsubplugins)) {
            $config->disabledsubplugins = '';
        }

                if (isset($options['enable_filemanagement']) && !$options['enable_filemanagement']) {
            if (!strpos($config->disabledsubplugins, 'managefiles')) {
                $config->disabledsubplugins .= ',managefiles';
            }
        }

        $fontselectlist = empty($config->fontselectlist) ? '' : $config->fontselectlist;

        $langrev = -1;
        if (!empty($CFG->cachejs)) {
            $langrev = get_string_manager()->get_revision();
        }

        $params = array(
            'moodle_config' => $config,
            'mode' => "exact",
            'elements' => $elementid,
            'relative_urls' => false,
            'document_base_url' => $CFG->httpswwwroot,
            'moodle_plugin_base' => "$CFG->httpswwwroot/lib/editor/tinymce/plugins/",
            'content_css' => $contentcss,
            'language' => $lang,
            'directionality' => $directionality,
            'plugin_insertdate_dateFormat ' => $strdate,
            'plugin_insertdate_timeFormat ' => $strtime,
            'theme' => "advanced",
            'skin' => "moodle",
            'apply_source_formatting' => true,
            'remove_script_host' => false,
            'entity_encoding' => "raw",
            'plugins' => 'lists,table,style,layer,advhr,advlink,emotions,inlinepopups,' .
                'searchreplace,paste,directionality,fullscreen,nonbreaking,contextmenu,' .
                'insertdatetime,save,iespell,preview,print,noneditable,visualchars,' .
                'xhtmlxtras,template,pagebreak',
            'gecko_spellcheck' => true,
            'theme_advanced_font_sizes' => "1,2,3,4,5,6,7",
            'theme_advanced_layout_manager' => "SimpleLayout",
            'theme_advanced_toolbar_align' => "left",
            'theme_advanced_fonts' => $fontselectlist,
            'theme_advanced_resize_horizontal' => true,
            'theme_advanced_resizing' => true,
            'theme_advanced_resizing_min_height' => 30,
            'min_height' => 30,
            'theme_advanced_toolbar_location' => "top",
            'theme_advanced_statusbar_location' => "bottom",
            'language_load' => false,             'langrev' => $langrev,
        );

                if (!empty($config->customtoolbar) and $customtoolbar = self::parse_toolbar_setting($config->customtoolbar)) {
            $i = 1;
            foreach ($customtoolbar as $line) {
                $params['theme_advanced_buttons'.$i] = $line;
                $i++;
            }
        } else {
                        $params['theme_advanced_buttons1'] = '';
        }

        if (!empty($config->customconfig)) {
            $config->customconfig = trim($config->customconfig);
            $decoded = json_decode($config->customconfig, true);
            if (is_array($decoded)) {
                foreach ($decoded as $k=>$v) {
                    $params[$k] = $v;
                }
            }
        }

        if (!empty($options['legacy']) or !empty($options['noclean']) or !empty($options['trusted'])) {
                                    $params['valid_elements'] = 'script[src|type],*[*]';             $params['invalid_elements'] = '';
        }
                $params['extended_valid_elements'] = 'nolink,tex,algebra,lang[lang]';
        $params['custom_elements'] = 'nolink,~tex,~algebra,lang';

                if (!empty($options['required'])) {
            $params['init_instance_callback'] = 'M.editor_tinymce.onblur_event';
        }

                editor_tinymce_plugin::all_update_init_params($params, $context, $options);

                unset($params['moodle_config']);

        return $params;
    }

    
    public static function parse_toolbar_setting($customtoolbar) {
        $result = array();
        $customtoolbar = trim($customtoolbar);
        if ($customtoolbar === '') {
            return $result;
        }
        $customtoolbar = str_replace("\r", "\n", $customtoolbar);
        $customtoolbar = strtolower($customtoolbar);
        $i = 0;
        foreach (explode("\n", $customtoolbar) as $line) {
            $line = preg_replace('/[^a-z0-9_,\|\-]/', ',', $line);
            $line = str_replace('|', ',|,', $line);
            $line = preg_replace('/,,+/', ',', $line);
            $line = trim($line, ',|');
            if ($line === '') {
                continue;
            }
            if ($i == 10) {
                                $result[9] = $result[9].','.$line;
            } else {
                $result[] = $line;
                $i++;
            }
        }
        return $result;
    }

    
    public function get_plugin($plugin) {
        global $CFG;
        return editor_tinymce_plugin::get($plugin);
    }

    
    public function get_tinymce_base_url() {
        global $CFG;
        return new moodle_url("$CFG->httpswwwroot/lib/editor/tinymce/tiny_mce/$this->version/");
    }

}
