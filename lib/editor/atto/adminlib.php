<?php



defined('MOODLE_INTERNAL') || die();


class editor_atto_toolbar_setting extends admin_setting_configtextarea {

    
    public function validate($data) {
        $result = parent::validate($data);
        if ($result !== true) {
            return $result;
        }

        $lines = explode("\n", $data);
        $groups = array();
        $plugins = array();

        foreach ($lines as $line) {
            if (!trim($line)) {
                continue;
            }

            $matches = array();
            if (!preg_match('/^\s*([a-z0-9]+)\s*=\s*([a-z0-9]+(\s*,\s*[a-z0-9]+)*)+\s*$/', $line, $matches)) {
                $result = get_string('errorcannotparseline', 'editor_atto', $line);
                break;
            }

            $group = $matches[1];
            if (isset($groups[$group])) {
                $result = get_string('errorgroupisusedtwice', 'editor_atto', $group);
                break;
            }
            $groups[$group] = true;

            $lineplugins = array_map('trim', explode(',', $matches[2]));
            foreach ($lineplugins as $plugin) {
                if (isset($plugins[$plugin])) {
                    $result = get_string('errorpluginisusedtwice', 'editor_atto', $plugin);
                    break 2;
                } else if (!core_component::get_component_directory('atto_' . $plugin)) {
                    $result = get_string('errorpluginnotfound', 'editor_atto', $plugin);
                    break 2;
                }
                $plugins[$plugin] = true;
            }
        }

                if (empty($groups) || empty($plugins)) {
            $result = get_string('errornopluginsorgroupsfound', 'editor_atto');
        }

        return $result;
    }

}


class editor_atto_subplugins_setting extends admin_setting {

    
    public function __construct() {
        $this->nosave = true;
        parent::__construct('attosubplugins', get_string('subplugintype_atto_plural', 'editor_atto'), '', '');
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

        $subplugins = core_component::get_plugin_list('atto');
        foreach ($subplugins as $name => $dir) {
            if (stripos($name, $query) !== false) {
                return true;
            }

            $namestr = get_string('pluginname', 'atto_' . $name);
            if (strpos(core_text::strtolower($namestr), core_text::strtolower($query)) !== false) {
                return true;
            }
        }
        return false;
    }

    
    public function output_html($data, $query = '') {
        global $CFG, $OUTPUT, $PAGE;
        require_once($CFG->libdir . "/editorlib.php");
        require_once(__DIR__ . '/lib.php');
        $pluginmanager = core_plugin_manager::instance();

                $strtoolbarconfig = get_string('toolbarconfig', 'editor_atto');
        $strname = get_string('name');
        $strsettings = get_string('settings');
        $struninstall = get_string('uninstallplugin', 'core_admin');
        $strversion = get_string('version');

        $subplugins = core_component::get_plugin_list('atto');

        $return = $OUTPUT->heading(get_string('subplugintype_atto_plural', 'editor_atto'), 3, 'main', true);
        $return .= $OUTPUT->box_start('generalbox attosubplugins');

        $table = new html_table();
        $table->head  = array($strname, $strversion, $strtoolbarconfig, $strsettings, $struninstall);
        $table->align = array('left', 'left', 'center', 'center', 'center', 'center');
        $table->data  = array();
        $table->attributes['class'] = 'admintable generaltable';

                foreach ($subplugins as $name => $dir) {
            $namestr = get_string('pluginname', 'atto_' . $name);
            $version = get_config('atto_' . $name, 'version');
            if ($version === false) {
                $version = '';
            }
            $plugininfo = $pluginmanager->get_plugin_info('atto_' . $name);

            $toolbarconfig = $name;

            $displayname = $namestr;

                        if ($PAGE->theme->resolve_image_location('icon', 'atto_' . $name, false)) {
                $icon = $OUTPUT->pix_icon('icon', '', 'atto_' . $name, array('class' => 'icon pluginicon'));
            } else {
                                $icon = $OUTPUT->pix_icon('spacer', '', 'moodle', array('class' => 'icon pluginicon noicon'));
            }
            $displayname = $icon . $displayname;

                        if (!$version) {
                $settings = '';
            } else if ($url = $plugininfo->get_settings_url()) {
                $settings = html_writer::link($url, $strsettings);
            } else {
                $settings = '';
            }

                        $uninstall = '';
            if ($uninstallurl = core_plugin_manager::instance()->get_uninstall_url('atto_' . $name, 'manage')) {
                $uninstall = html_writer::link($uninstallurl, $struninstall);
            }

                        $row = new html_table_row(array($displayname, $version, $toolbarconfig, $settings, $uninstall));
            $table->data[] = $row;
        }
        $return .= html_writer::table($table);
        $return .= html_writer::tag('p', get_string('tablenosave', 'admin'));
        $return .= $OUTPUT->box_end();
        return highlight($query, $return);
    }
}

