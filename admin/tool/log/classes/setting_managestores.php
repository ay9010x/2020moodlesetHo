<?php



defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/adminlib.php");

class tool_log_setting_managestores extends admin_setting {
    
    public function __construct() {
        $this->nosave = true;
        parent::__construct('tool_log_manageui', get_string('managelogging', 'tool_log'), '', '');
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

        $query = core_text::strtolower($query);
        $plugins = \tool_log\log\manager::get_store_plugins();
        foreach ($plugins as $plugin => $fulldir) {
            if (strpos(core_text::strtolower($plugin), $query) !== false) {
                return true;
            }
            $localised = get_string('pluginname', $plugin);
            if (strpos(core_text::strtolower($localised), $query) !== false) {
                return true;
            }
        }
        return false;
    }

    
    public function output_html($data, $query = '') {
        global $OUTPUT, $PAGE;

                $strup = get_string('up');
        $strdown = get_string('down');
        $strsettings = get_string('settings');
        $strenable = get_string('enable');
        $strdisable = get_string('disable');
        $struninstall = get_string('uninstallplugin', 'core_admin');
        $strversion = get_string('version');

        $pluginmanager = core_plugin_manager::instance();
        $logmanager = new \tool_log\log\manager();
        $available = $logmanager->get_store_plugins();
        $enabled = get_config('tool_log', 'enabled_stores');
        if (!$enabled) {
            $enabled = array();
        } else {
            $enabled = array_flip(explode(',', $enabled));
        }

        $allstores = array();
        foreach ($enabled as $key => $store) {
            $allstores[$key] = true;
            $enabled[$key] = true;
        }
        foreach ($available as $key => $store) {
            $allstores[$key] = true;
            $available[$key] = true;
        }

        $return = $OUTPUT->heading(get_string('actlogshdr', 'tool_log'), 3, 'main', true);
        $return .= $OUTPUT->box_start('generalbox loggingui');

        $table = new html_table();
        $table->head = array(get_string('name'), get_string('reportssupported', 'tool_log'), $strversion, $strenable,
                $strup . '/' . $strdown, $strsettings, $struninstall);
        $table->colclasses = array('leftalign', 'centeralign', 'centeralign', 'centeralign', 'centeralign', 'centeralign',
                'centeralign');
        $table->id = 'logstoreplugins';
        $table->attributes['class'] = 'admintable generaltable';
        $table->data = array();

                $updowncount = 1;
        $storecount = count($enabled);
        $url = new moodle_url('/admin/tool/log/stores.php', array('sesskey' => sesskey()));
        $printed = array();
        foreach ($allstores as $store => $unused) {
            $plugininfo = $pluginmanager->get_plugin_info($store);
            $version = get_config($store, 'version');
            if ($version === false) {
                $version = '';
            }

            if (get_string_manager()->string_exists('pluginname', $store)) {
                $name = get_string('pluginname', $store);
            } else {
                $name = $store;
            }

            $reports = $logmanager->get_supported_reports($store);
            if (!empty($reports)) {
                $supportedreports = implode(', ', $reports);
            } else {
                $supportedreports = '-';
            }

                        if (isset($enabled[$store])) {
                $aurl = new moodle_url($url, array('action' => 'disable', 'store' => $store));
                $hideshow = "<a href=\"$aurl\">";
                $hideshow .= "<img src=\"" . $OUTPUT->pix_url('t/hide') . "\" class=\"iconsmall\" alt=\"$strdisable\" /></a>";
                $isenabled = true;
                $displayname = "<span>$name</span>";
            } else {
                if (isset($available[$store])) {
                    $aurl = new moodle_url($url, array('action' => 'enable', 'store' => $store));
                    $hideshow = "<a href=\"$aurl\">";
                    $hideshow .= "<img src=\"" . $OUTPUT->pix_url('t/show') . "\" class=\"iconsmall\" alt=\"$strenable\" /></a>";
                    $isenabled = false;
                    $displayname = "<span class=\"dimmed_text\">$name</span>";
                } else {
                    $hideshow = '';
                    $isenabled = false;
                    $displayname = '<span class="notifyproblem">' . $name . '</span>';
                }
            }
            if ($PAGE->theme->resolve_image_location('icon', $store, false)) {
                $icon = $OUTPUT->pix_icon('icon', '', $store, array('class' => 'icon pluginicon'));
            } else {
                $icon = $OUTPUT->pix_icon('spacer', '', 'moodle', array('class' => 'icon pluginicon noicon'));
            }

                        $updown = '';
            if ($isenabled) {
                if ($updowncount > 1) {
                    $aurl = new moodle_url($url, array('action' => 'up', 'store' => $store));
                    $updown .= "<a href=\"$aurl\">";
                    $updown .= "<img src=\"" . $OUTPUT->pix_url('t/up') . "\" alt=\"$strup\" class=\"iconsmall\" /></a>&nbsp;";
                } else {
                    $updown .= "<img src=\"" . $OUTPUT->pix_url('spacer') . "\" class=\"iconsmall\" alt=\"\" />&nbsp;";
                }
                if ($updowncount < $storecount) {
                    $aurl = new moodle_url($url, array('action' => 'down', 'store' => $store));
                    $updown .= "<a href=\"$aurl\">";
                    $updown .= "<img src=\"" . $OUTPUT->pix_url('t/down') . "\" alt=\"$strdown\" class=\"iconsmall\" /></a>";
                } else {
                    $updown .= "<img src=\"" . $OUTPUT->pix_url('spacer') . "\" class=\"iconsmall\" alt=\"\" />";
                }
                ++$updowncount;
            }

                        if (!$version) {
                $settings = '';
            } else {
                if ($surl = $plugininfo->get_settings_url()) {
                    $settings = html_writer::link($surl, $strsettings);
                } else {
                    $settings = '';
                }
            }

                        $uninstall = '';
            if ($uninstallurl = core_plugin_manager::instance()->get_uninstall_url($store, 'manage')) {
                $uninstall = html_writer::link($uninstallurl, $struninstall);
            }

                        $table->data[] = array($icon . $displayname, $supportedreports, $version, $hideshow, $updown, $settings, $uninstall);

            $printed[$store] = true;
        }

        $return .= html_writer::table($table);
        $return .= get_string('configlogplugins', 'tool_log') . '<br />' . get_string('tablenosave', 'admin');
        $return .= $OUTPUT->box_end();
        return highlight($query, $return);
    }
}
