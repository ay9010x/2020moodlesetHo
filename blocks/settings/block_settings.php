<?php




class block_settings extends block_base {

    
    public static $navcount;
    public $blockname = null;
    
    protected $contentgenerated = false;
    
    protected $docked = null;

    
    function init() {
        $this->blockname = get_class($this);
        $this->title = get_string('pluginname', $this->blockname);
    }

    
    function instance_allow_multiple() {
        return false;
    }

    
    function  instance_can_be_hidden() {
        return false;
    }

    
    function applicable_formats() {
        return array('all' => true);
    }

    
    function instance_allow_config() {
        return true;
    }

    function instance_can_be_docked() {
        return (parent::instance_can_be_docked() && (empty($this->config->enabledock) || $this->config->enabledock=='yes'));
    }

    function get_required_javascript() {
        global $PAGE;
        $adminnode = $PAGE->settingsnav->find('siteadministration', navigation_node::TYPE_SITE_ADMIN);
        parent::get_required_javascript();
        $arguments = array(
            'instanceid' => $this->instance->id,
            'adminnodeid' => $adminnode ? $adminnode->id : null
        );
        $this->page->requires->js_call_amd('block_settings/settingsblock', 'init', $arguments);
    }

    
    function get_content() {
        global $CFG, $OUTPUT;
                if ($this->contentgenerated === true) {
            return true;
        }
                        block_settings::$navcount++;

                if ($this->docked === null) {
            $this->docked = get_user_preferences('nav_in_tab_panel_settingsnav'.block_settings::$navcount, 0);
        }

                if ($this->docked && optional_param('undock', null, PARAM_INT)==$this->instance->id) {
            unset_user_preference('nav_in_tab_panel_settingsnav'.block_settings::$navcount, 0);
            $url = $this->page->url;
            $url->remove_params(array('undock'));
            redirect($url);
        } else if (!$this->docked && optional_param('dock', null, PARAM_INT)==$this->instance->id) {
            set_user_preferences(array('nav_in_tab_panel_settingsnav'.block_settings::$navcount=>1));
            $url = $this->page->url;
            $url->remove_params(array('dock'));
            redirect($url);
        }

        $renderer = $this->page->get_renderer('block_settings');
        $this->content = new stdClass();
        $this->content->text = $renderer->settings_tree($this->page->settingsnav);

                if (!empty($this->content->text)) {
            if (has_capability('moodle/site:config',context_system::instance()) ) {
                $this->content->footer = $renderer->search_form(new moodle_url("$CFG->wwwroot/$CFG->admin/search.php"), optional_param('query', '', PARAM_RAW));
            } else {
                $this->content->footer = '';
            }

            if (!empty($this->config->enabledock) && $this->config->enabledock == 'yes') {
                user_preference_allow_ajax_update('nav_in_tab_panel_settingsnav'.block_settings::$navcount, PARAM_INT);
            }
        }

        $this->contentgenerated = true;
        return true;
    }

    
    public function get_aria_role() {
        return 'navigation';
    }
}
