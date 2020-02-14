<?php



defined('MOODLE_INTERNAL') || die();

require_once ($CFG->libdir.'/formslib.php');


final class portfolio_export_form extends moodleform {

    
    public function definition() {

        $mform =& $this->_form;
        $mform->addElement('hidden', 'stage', PORTFOLIO_STAGE_CONFIG);
        $mform->addElement('hidden', 'id', $this->_customdata['id']);
        $mform->addElement('hidden', 'instance', $this->_customdata['instance']->get('id'));
        $mform->setType('instance', PARAM_INT);
        $mform->setType('stage', PARAM_INT);
        $mform->setType('id', PARAM_INT);

        if (array_key_exists('formats', $this->_customdata) && is_array($this->_customdata['formats'])) {
            if (count($this->_customdata['formats']) > 1) {
                $options = array();
                foreach ($this->_customdata['formats'] as $key) {
                    $options[$key] = get_string('format_' . $key, 'portfolio');
                }
                $mform->addElement('select', 'format', get_string('availableformats', 'portfolio'), $options);
            } else {
                $f = array_shift($this->_customdata['formats']);
                $mform->addElement('hidden', 'format', $f);
                $mform->setType('format', PARAM_RAW);
            }
        }

                if (array_key_exists('expectedtime', $this->_customdata)
            && $this->_customdata['expectedtime'] != PORTFOLIO_TIME_LOW
            && $this->_customdata['expectedtime'] != PORTFOLIO_TIME_FORCEQUEUE) {
            $radioarray = array();
            $radioarray[] = $mform->createElement('radio', 'wait', '', get_string('wait', 'portfolio'), 1);
            $radioarray[] = $mform->createElement('radio', 'wait', '', get_string('dontwait', 'portfolio'),  0);
            $mform->addGroup($radioarray, 'radioar', get_string('wanttowait_' . $this->_customdata['expectedtime'], 'portfolio') , array(' '), false);
            $mform->setDefault('wait', 0);
        } else {
            if ($this->_customdata['expectedtime'] == PORTFOLIO_TIME_LOW) {
                $mform->addElement('hidden', 'wait', 1);
            } else {
                $mform->addElement('hidden', 'wait', 0);
            }
            $mform->setType('wait', PARAM_INT);
        }

        if (array_key_exists('plugin', $this->_customdata) && is_object($this->_customdata['plugin'])) {
            $this->_customdata['plugin']->export_config_form($mform, $this->_customdata['userid']);
        }

        if (array_key_exists('caller', $this->_customdata) && is_object($this->_customdata['caller'])) {
            $this->_customdata['caller']->export_config_form($mform, $this->_customdata['instance'], $this->_customdata['userid']);
        }

        $this->add_action_buttons(true, get_string('next'));
    }

    
    public function validation($data, $files) {

        $errors = array();

        if (array_key_exists('plugin', $this->_customdata) && is_object($this->_customdata['plugin'])) {
            $pluginerrors = $this->_customdata['plugin']->export_config_validation($data);
            if (is_array($pluginerrors)) {
                $errors = $pluginerrors;
            }
        }
        if (array_key_exists('caller', $this->_customdata) && is_object($this->_customdata['caller'])) {
            $callererrors = $this->_customdata['caller']->export_config_validation($data);
            if (is_array($callererrors)) {
                $errors = array_merge($errors, $callererrors);
            }
        }
        return $errors;
    }
}


final class portfolio_admin_form extends moodleform {

    
    protected $instance;

    
    protected $plugin;

    
    protected $portfolio;

    
    protected $action;

    
    protected $visible;

    
    public function definition() {
        global $CFG;
        $this->plugin = $this->_customdata['plugin'];
        $this->instance = (isset($this->_customdata['instance'])
                && is_subclass_of($this->_customdata['instance'], 'portfolio_plugin_base'))
            ? $this->_customdata['instance'] : null;
        $this->portfolio = $this->_customdata['portfolio'];
        $this->action = $this->_customdata['action'];
        $this->visible = $this->_customdata['visible'];

        $mform =& $this->_form;
        $strrequired = get_string('required');

        $mform->addElement('hidden', 'pf', $this->portfolio);
        $mform->setType('pf', PARAM_ALPHA);
        $mform->addElement('hidden', 'action', $this->action);
        $mform->setType('action', PARAM_ALPHA);
        $mform->addElement('hidden', 'visible', $this->visible);
        $mform->setType('visible', PARAM_INT);
        $mform->addElement('hidden', 'plugin', $this->plugin);
        $mform->setType('plugin', PARAM_PLUGIN);

        if (!$this->instance) {
            $insane = portfolio_instance_sanity_check($this->instance);
        } else {
            $insane = portfolio_plugin_sanity_check($this->plugin);
        }

        if (isset($insane) && is_array($insane)) {
            $insane = array_shift($insane);
        }
        if (isset($insane) && is_string($insane)) {             $mform->addElement('warning', 'insane', null, get_string($insane, 'portfolio_' . $this->plugin));
        }

        $mform->addElement('text', 'name', get_string('name'), 'maxlength="100" size="30"');
        $mform->addRule('name', $strrequired, 'required', null, 'client');
        $mform->setType('name', PARAM_TEXT);

                if (portfolio_static_function($this->plugin, 'has_admin_config')) {
            require_once($CFG->libdir . '/portfolio/plugin.php');
            require_once($CFG->dirroot . '/portfolio/' . $this->plugin .  '/lib.php');
            $classname = 'portfolio_plugin_' . $this->plugin;
            $classname::admin_config_form($mform);
        }

                if ($this->instance) {
            $data = array('name' => $this->instance->get('name'));
            foreach ($this->instance->get_allowed_config() as $config) {
                $data[$config] = $this->instance->get_config($config);
            }
            $this->set_data($data);
        } else {
            $this->set_data(array('name' => portfolio_static_function($this->plugin, 'get_name')));
        }

        $this->add_action_buttons(true, get_string('save', 'portfolio'));
    }

    
    public function validation($data, $files) {
        global $DB;

        $errors = array();
        if ($DB->count_records('portfolio_instance', array('name' => $data['name'], 'plugin' => $data['plugin'])) > 1) {
            $errors = array('name' => get_string('err_uniquename', 'portfolio'));
        }

        $pluginerrors = array();
        $pluginerrors = portfolio_static_function($this->plugin, 'admin_config_validation', $data);
        if (is_array($pluginerrors)) {
            $errors = array_merge($errors, $pluginerrors);
        }
        return $errors;
    }
}


final class portfolio_user_form extends moodleform {

    
    protected $instance;

    
    protected $userid;

    
    public function definition() {
        $this->instance = $this->_customdata['instance'];
        $this->userid = $this->_customdata['userid'];

        $this->_form->addElement('hidden', 'config', $this->instance->get('id'));
        $this->_form->setType('config', PARAM_INT);

        $this->instance->user_config_form($this->_form, $this->userid);

        $data = array();
        foreach ($this->instance->get_allowed_user_config() as $config) {
            $data[$config] = $this->instance->get_user_config($config, $this->userid);
        }
        $this->set_data($data);
        $this->add_action_buttons(true, get_string('save', 'portfolio'));
    }

    
    public function validation($data, $files) {

        $errors = $this->instance->user_config_validation($data);

    }
}



class portfolio_instance_select extends moodleform {

    
    private $caller;

    
    function definition() {
        $this->caller = $this->_customdata['caller'];
        $options = $this->_customdata['options'];
        $mform =& $this->_form;
        $mform->addElement('select', 'instance', get_string('selectplugin', 'portfolio'), $options);
        $mform->addElement('hidden', 'id', $this->_customdata['id']);
        $mform->setType('id', PARAM_INT);
        $this->add_action_buttons(true, get_string('next'));
    }
}
