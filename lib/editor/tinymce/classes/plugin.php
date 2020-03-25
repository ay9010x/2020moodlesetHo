<?php

defined('MOODLE_INTERNAL') || die();


abstract class editor_tinymce_plugin {
    
    protected $plugin;

    
    protected $config = null;

    
    protected $buttons = array();

    
    public function __construct($plugin) {
        $this->plugin = $plugin;
    }

    
    public function get_buttons() {
        return $this->buttons;
    }
    
    protected function load_config() {
        if (!isset($this->config)) {
            $name = $this->get_name();
            $this->config = get_config("tinymce_$name");
        }
    }

    
    public function get_config($name, $default = null) {
        $this->load_config();
        return isset($this->config->$name) ? $this->config->$name : $default;
    }

    
    public function set_config($name, $value) {
        $pluginname = $this->get_name();
        $this->load_config();
        if ($value === null) {
            unset($this->config->$name);
        } else {
            $this->config->$name = $value;
        }
        set_config($name, $value, "tinymce_$pluginname");
    }

    
    public function get_name() {
                $words = explode('_', get_class($this), 2);
        return $words[1];
    }

    
    protected abstract function update_init_params(array &$params, context $context,
            array $options = null);

    
    protected function get_sort_order() {
        return 100;
    }

    
    protected function add_button_after(array &$params, $row, $button,
            $after = '', $alwaysadd = true) {

        if ($button !== '|' && $this->find_button($params, $button)) {
            return true;
        }

        $row = $this->fix_row($params, $row);

        $field = 'theme_advanced_buttons' . $row;
        $old = $params[$field];

                if ($after === '') {
            $params[$field] = $old . ',' . $button;
            return true;
        }

                $params[$field] = preg_replace('~(,|^)(' . preg_quote($after) . ')(,|$)~',
                '$1$2,' . $button . '$3', $old);
        if ($params[$field] !== $old) {
            return true;
        }

                if ($alwaysadd) {
            return $this->add_button_after($params, $row, $button);
        }

                return false;
    }

    
    protected function add_button_before(array &$params, $row, $button,
            $before = '', $alwaysadd = true) {

        if ($button !== '|' && $this->find_button($params, $button)) {
            return true;
        }
        $row = $this->fix_row($params, $row);

        $field = 'theme_advanced_buttons' . $row;
        $old = $params[$field];

                if ($before === '') {
            $params[$field] = $button . ',' . $old;
            return true;
        }

                $params[$field] = preg_replace('~(,|^)(' . preg_quote($before) . ')(,|$)~',
                '$1' . $button . ',$2$3', $old);
        if ($params[$field] !== $old) {
            return true;
        }

                if ($alwaysadd) {
            return $this->add_button_before($params, $row, $button);
        }

                return false;
    }

    
    protected function find_button(array &$params, $button) {
        foreach ($params as $key => $value) {
            if (preg_match('/^theme_advanced_buttons(\d+)$/', $key, $matches) &&
                    strpos(','. $value. ',', ','. $button. ',') !== false) {
                return (int)$matches[1];
            }
        }
        return false;
    }

    
    private function fix_row(array &$params, $row) {
        if ($row <= 1) {
                        return 1;
        } else if (isset($params['theme_advanced_buttons' . $row])) {
            return $row;
        } else {
            return $this->count_button_rows($params);
        }
    }

    
    protected function count_button_rows(array &$params) {
        $maxrow = 1;
        foreach ($params as $key => $value) {
            if (preg_match('/^theme_advanced_buttons(\d+)$/', $key, $matches) &&
                    (int)$matches[1] > $maxrow) {
                $maxrow = (int)$matches[1];
            }
        }
        return $maxrow;
    }

    
    protected function add_js_plugin(&$params, $pluginname='', $jsfile='editor_plugin.js') {
        global $CFG;

                if ($pluginname === '') {
            $pluginname = $this->plugin;
        }

                $params['plugins'] .= ',-' . $pluginname;

                if (!isset($params['moodle_init_plugins'])) {
            $params['moodle_init_plugins'] = '';
        } else {
            $params['moodle_init_plugins'] .= ',';
        }

                $jsurl = $this->get_tinymce_file_url($jsfile, false);
        $params['moodle_init_plugins'] .= $pluginname . ':' . $jsurl;
    }

    
    public function get_tinymce_file_url($file='', $absolute=true) {
        global $CFG;

                                if ($CFG->debugdeveloper) {
            $version = '-1';
        } else {
            $version = $this->get_version();
        }

                        if ($CFG->slasharguments) {
                        $jsurl = 'loader.php/' . $this->plugin . '/' . $version . '/' . $file;
        } else {
                                                            $jsurl = $this->plugin . '/tinymce/' . $file;
        }

        if ($absolute) {
            $jsurl = $CFG->wwwroot . '/lib/editor/tinymce/plugins/' . $jsurl;
        }

        return $jsurl;
    }

    
    protected function get_version() {
        global $CFG;

        $plugin = new stdClass;
        require($CFG->dirroot . '/lib/editor/tinymce/plugins/' . $this->plugin . '/version.php');
        return $plugin->version;
    }

    
    public static function all_update_init_params(array &$params,
            context $context, array $options = null) {
        global $CFG;

                $plugins = core_component::get_plugin_list('tinymce');

                $disabled = array();
        if ($params['moodle_config']->disabledsubplugins) {
            foreach (explode(',', $params['moodle_config']->disabledsubplugins) as $sp) {
                $sp = trim($sp);
                if ($sp !== '') {
                    $disabled[$sp] = $sp;
                }
            }
        }

                $pluginobjects = array();
        foreach ($plugins as $plugin => $dir) {
            if (isset($disabled[$plugin])) {
                continue;
            }
            require_once($dir . '/lib.php');
            $classname = 'tinymce_' . $plugin;
            $pluginobjects[] = new $classname($plugin);
        }

                usort($pluginobjects, array('editor_tinymce_plugin', 'compare_plugins'));

                foreach ($pluginobjects as $obj) {
            $obj->update_init_params($params, $context, $options);
        }
    }

    
    public static function get($plugin) {
        $dir = core_component::get_component_directory('tinymce_' . $plugin);
        require_once($dir . '/lib.php');
        $classname = 'tinymce_' . $plugin;
        return new $classname($plugin);
    }

    
    public static function compare_plugins(editor_tinymce_plugin $a, editor_tinymce_plugin $b) {
                $order = $a->get_sort_order() - $b->get_sort_order();
        if ($order != 0) {
            return $order;
        }

                return strcmp($a->plugin, $b->plugin);
    }
}
