<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/adminlib.php');


class assign_admin_page_manage_assign_plugins extends admin_externalpage {

    
    private $subtype = '';

    
    public function __construct($subtype) {
        $this->subtype = $subtype;
        $url = new moodle_url('/mod/assign/adminmanageplugins.php', array('subtype'=>$subtype));
        parent::__construct('manage' . $subtype . 'plugins',
                            get_string('manage' . $subtype . 'plugins', 'assign'),
                            $url);
    }

    
    public function search($query) {
        if ($result = parent::search($query)) {
            return $result;
        }

        $found = false;

        foreach (core_component::get_plugin_list($this->subtype) as $name => $notused) {
            if (strpos(core_text::strtolower(get_string('pluginname', $this->subtype . '_' . $name)),
                    $query) !== false) {
                $found = true;
                break;
            }
        }
        if ($found) {
            $result = new stdClass();
            $result->page     = $this;
            $result->settings = array();
            return array($this->name => $result);
        } else {
            return array();
        }
    }
}



class assign_plugin_manager {

    
    private $pageurl;
    
    private $error = '';
    
    private $subtype = '';

    
    public function __construct($subtype) {
        $this->pageurl = new moodle_url('/mod/assign/adminmanageplugins.php', array('subtype'=>$subtype));
        $this->subtype = $subtype;
    }


    
    public function get_sorted_plugins_list() {
        $names = core_component::get_plugin_list($this->subtype);

        $result = array();

        foreach ($names as $name => $path) {
            $idx = get_config($this->subtype . '_' . $name, 'sortorder');
            if (!$idx) {
                $idx = 0;
            }
            while (array_key_exists($idx, $result)) {
                $idx +=1;
            }
            $result[$idx] = $name;
        }
        ksort($result);

        return $result;
    }


    
    private function format_icon_link($action, $plugin, $icon, $alt) {
        global $OUTPUT;

        $url = $this->pageurl;

        if ($action === 'delete') {
            $url = core_plugin_manager::instance()->get_uninstall_url($this->subtype.'_'.$plugin, 'manage');
            if (!$url) {
                return '&nbsp;';
            }
            return html_writer::link($url, get_string('uninstallplugin', 'core_admin'));
        }

        return $OUTPUT->action_icon(new moodle_url($url,
                array('action' => $action, 'plugin'=> $plugin, 'sesskey' => sesskey())),
                new pix_icon($icon, $alt, 'moodle', array('title' => $alt)),
                null, array('title' => $alt)) . ' ';
    }

    
    private function view_plugins_table() {
        global $OUTPUT, $CFG;
        require_once($CFG->libdir . '/tablelib.php');

                $this->view_header();
        $table = new flexible_table($this->subtype . 'pluginsadminttable');
        $table->define_baseurl($this->pageurl);
        $table->define_columns(array('pluginname', 'version', 'hideshow', 'order',
                'settings', 'uninstall'));
        $table->define_headers(array(get_string($this->subtype . 'pluginname', 'assign'),
                get_string('version'), get_string('hideshow', 'assign'),
                get_string('order'), get_string('settings'), get_string('uninstallplugin', 'core_admin')));
        $table->set_attribute('id', $this->subtype . 'plugins');
        $table->set_attribute('class', 'admintable generaltable');
        $table->setup();

        $plugins = $this->get_sorted_plugins_list();
        $shortsubtype = substr($this->subtype, strlen('assign'));

        foreach ($plugins as $idx => $plugin) {
            $row = array();
            $class = '';

            $row[] = get_string('pluginname', $this->subtype . '_' . $plugin);
            $row[] = get_config($this->subtype . '_' . $plugin, 'version');

            $visible = !get_config($this->subtype . '_' . $plugin, 'disabled');

            if ($visible) {
                $row[] = $this->format_icon_link('hide', $plugin, 't/hide', get_string('disable'));
            } else {
                $row[] = $this->format_icon_link('show', $plugin, 't/show', get_string('enable'));
                $class = 'dimmed_text';
            }

            $movelinks = '';
            if (!$idx == 0) {
                $movelinks .= $this->format_icon_link('moveup', $plugin, 't/up', get_string('up'));
            } else {
                $movelinks .= $OUTPUT->spacer(array('width'=>16));
            }
            if ($idx != count($plugins) - 1) {
                $movelinks .= $this->format_icon_link('movedown', $plugin, 't/down', get_string('down'));
            }
            $row[] = $movelinks;

            $exists = file_exists($CFG->dirroot . '/mod/assign/' . $shortsubtype . '/' . $plugin . '/settings.php');
            if ($row[1] != '' && $exists) {
                $row[] = html_writer::link(new moodle_url('/admin/settings.php',
                        array('section' => $this->subtype . '_' . $plugin)), get_string('settings'));
            } else {
                $row[] = '&nbsp;';
            }

            $row[] = $this->format_icon_link('delete', $plugin, 't/delete', get_string('uninstallplugin', 'core_admin'));

            $table->add_data($row, $class);
        }

        $table->finish_output();
        $this->view_footer();
    }

    
    private function view_header() {
        global $OUTPUT;
        admin_externalpage_setup('manage' . $this->subtype . 'plugins');
                echo $OUTPUT->header();
        echo $OUTPUT->heading(get_string('manage' . $this->subtype . 'plugins', 'assign'));
    }

    
    private function view_footer() {
        global $OUTPUT;
        echo $OUTPUT->footer();
    }

    
    private function check_permissions() {
                require_login();
        $systemcontext = context_system::instance();
        require_capability('moodle/site:config', $systemcontext);
    }

    
    public function hide_plugin($plugin) {
        set_config('disabled', 1, $this->subtype . '_' . $plugin);
        core_plugin_manager::reset_caches();
        return 'view';
    }

    
    public function move_plugin($plugintomove, $dir) {
                $plugins = $this->get_sorted_plugins_list();

        $currentindex = 0;

                $plugins = array_values($plugins);

                foreach ($plugins as $key => $plugin) {
            if ($plugin == $plugintomove) {
                $currentindex = $key;
                break;
            }
        }

                if ($dir == 'up') {
            if ($currentindex > 0) {
                $tempplugin = $plugins[$currentindex - 1];
                $plugins[$currentindex - 1] = $plugins[$currentindex];
                $plugins[$currentindex] = $tempplugin;
            }
        } else if ($dir == 'down') {
            if ($currentindex < (count($plugins) - 1)) {
                $tempplugin = $plugins[$currentindex + 1];
                $plugins[$currentindex + 1] = $plugins[$currentindex];
                $plugins[$currentindex] = $tempplugin;
            }
        }

                foreach ($plugins as $key => $plugin) {
            set_config('sortorder', $key, $this->subtype . '_' . $plugin);
        }
        return 'view';
    }


    
    public function show_plugin($plugin) {
        set_config('disabled', 0, $this->subtype . '_' . $plugin);
        core_plugin_manager::reset_caches();
        return 'view';
    }


    
    public function execute($action, $plugin) {
        if ($action == null) {
            $action = 'view';
        }

        $this->check_permissions();

                if ($action == 'hide' && $plugin != null) {
            $action = $this->hide_plugin($plugin);
        } else if ($action == 'show' && $plugin != null) {
            $action = $this->show_plugin($plugin);
        } else if ($action == 'moveup' && $plugin != null) {
            $action = $this->move_plugin($plugin, 'up');
        } else if ($action == 'movedown' && $plugin != null) {
            $action = $this->move_plugin($plugin, 'down');
        }

                if ($action == 'view') {
            $this->view_plugins_table();
        }
    }
}
