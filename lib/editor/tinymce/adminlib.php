<?php



defined('MOODLE_INTERNAL') || die();



class tiynce_subplugins_settings extends admin_setting {
    public function __construct() {
        $this->nosave = true;
        parent::__construct('tinymcesubplugins', get_string('subplugintype_tinymce_plural', 'editor_tinymce'), '', '');
    }

    
    public function get_setting() {
        return true;
    }

    
    public function get_defaultsetting() {
        return true;
    }

    
    public function write_setting($data) {
                return '';
    }

    
    public function is_related($query) {
        if (parent::is_related($query)) {
            return true;
        }

        $subplugins = core_component::get_plugin_list('tinymce');
        foreach ($subplugins as $name=>$dir) {
            if (stripos($name, $query) !== false) {
                return true;
            }

            $namestr = get_string('pluginname', 'tinymce_'.$name);
            if (strpos(core_text::strtolower($namestr), core_text::strtolower($query)) !== false) {
                return true;
            }
        }
        return false;
    }

    
    public function output_html($data, $query='') {
        global $CFG, $OUTPUT, $PAGE;
        require_once("$CFG->libdir/editorlib.php");
        require_once(__DIR__.'/lib.php');
        $tinymce = new tinymce_texteditor();
        $pluginmanager = core_plugin_manager::instance();

                $strbuttons = get_string('availablebuttons', 'editor_tinymce');
        $strdisable = get_string('disable');
        $strenable = get_string('enable');
        $strname = get_string('name');
        $strsettings = get_string('settings');
        $struninstall = get_string('uninstallplugin', 'core_admin');
        $strversion = get_string('version');

        $subplugins = core_component::get_plugin_list('tinymce');

        $return = $OUTPUT->heading(get_string('subplugintype_tinymce_plural', 'editor_tinymce'), 3, 'main', true);
        $return .= $OUTPUT->box_start('generalbox tinymcesubplugins');

        $table = new html_table();
        $table->head  = array($strname, $strbuttons, $strversion, $strenable, $strsettings, $struninstall);
        $table->align = array('left', 'left', 'center', 'center', 'center', 'center');
        $table->data  = array();
        $table->attributes['class'] = 'admintable generaltable';

                foreach ($subplugins as $name => $dir) {
            $namestr = get_string('pluginname', 'tinymce_'.$name);
            $version = get_config('tinymce_'.$name, 'version');
            if ($version === false) {
                $version = '';
            }
            $plugin = $tinymce->get_plugin($name);
            $plugininfo = $pluginmanager->get_plugin_info('tinymce_'.$name);

                        $class = '';
            if (!$version) {
                $hideshow = '';
                $displayname = html_writer::tag('span', $name, array('class'=>'error'));
            } else if ($plugininfo->is_enabled()) {
                $url = new moodle_url('/lib/editor/tinymce/subplugins.php', array('sesskey'=>sesskey(), 'return'=>'settings', 'disable'=>$name));
                $hideshow = html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('t/hide'), 'class'=>'iconsmall', 'alt'=>$strdisable));
                $hideshow = html_writer::link($url, $hideshow);
                $displayname = $namestr;
            } else {
                $url = new moodle_url('/lib/editor/tinymce/subplugins.php', array('sesskey'=>sesskey(), 'return'=>'settings', 'enable'=>$name));
                $hideshow = html_writer::empty_tag('img', array('src'=>$OUTPUT->pix_url('t/show'), 'class'=>'iconsmall', 'alt'=>$strenable));
                $hideshow = html_writer::link($url, $hideshow);
                $displayname = $namestr;
                $class = 'dimmed_text';
            }

            if ($PAGE->theme->resolve_image_location('icon', 'tinymce_' . $name, false)) {
                $icon = $OUTPUT->pix_icon('icon', '', 'tinymce_' . $name, array('class' => 'icon pluginicon'));
            } else {
                $icon = $OUTPUT->pix_icon('spacer', '', 'moodle', array('class' => 'icon pluginicon noicon'));
            }
            $displayname  = $icon . ' ' . $displayname;

                        $buttons = implode(', ', $plugin->get_buttons());
            $buttons = html_writer::tag('span', $buttons, array('class'=>'tinymcebuttons'));

                        if (!$version) {
                $settings = '';
            } else if ($url = $plugininfo->get_settings_url()) {
                $settings = html_writer::link($url, $strsettings);
            } else {
                $settings = '';
            }

                        $uninstall = '';
            if ($uninstallurl = core_plugin_manager::instance()->get_uninstall_url('tinymce_' . $name, 'manage')) {
                $uninstall = html_writer::link($uninstallurl, $struninstall);
            }

                        $row = new html_table_row(array($displayname, $buttons, $version, $hideshow, $settings, $uninstall));
            if ($class) {
                $row->attributes['class'] = $class;
            }
            $table->data[] = $row;
        }
        $return .= html_writer::table($table);
        $return .= html_writer::tag('p', get_string('tablenosave', 'admin'));
        $return .= $OUTPUT->box_end();
        return highlight($query, $return);
    }
}

class editor_tinymce_json_setting_textarea extends admin_setting_configtextarea {
    
    public function output_html($data, $query='') {
        $result = parent::output_html($data, $query);

        $data = trim($data);
        if ($data) {
            $decoded = json_decode($data, true);
                        if (is_array($decoded)) {
                $valid = '<span class="pathok">&#x2714;</span>';
            } else {
                $valid = '<span class="patherror">&#x2718;</span>';
            }
            $result = str_replace('</textarea>', '</textarea>'.$valid, $result);
        }

        return $result;
    }
}
