<?php




class block_navigation extends block_base {

    
    public static $navcount;
    
    public $blockname = null;
    
    protected $contentgenerated = false;
    
    protected $docked = null;

    
    const TRIM_RIGHT = 1;
    
    const TRIM_LEFT = 2;
    
    const TRIM_CENTER = 3;

    
    function init() {
        $this->blockname = get_class($this);
        $this->title = get_string('pluginname', $this->blockname);
    }

    
    function instance_allow_multiple() {
        return false;
    }

    
    function applicable_formats() {
        return array('all' => true);
    }

    
    function instance_allow_config() {
        return true;
    }

    
    function  instance_can_be_hidden() {
        return false;
    }

    
    function instance_can_be_docked() {
        return (parent::instance_can_be_docked() && (empty($this->config->enabledock) || $this->config->enabledock=='yes'));
    }

    
    function get_required_javascript() {
        parent::get_required_javascript();
        $arguments = array(
            'instanceid' => $this->instance->id
        );
        $this->page->requires->string_for_js('viewallcourses', 'moodle');
        $this->page->requires->js_call_amd('block_navigation/navblock', 'init', $arguments);
    }

    
    function get_content() {
        global $CFG;
                if ($this->contentgenerated === true) {
            return $this->content;
        }
                                
        block_navigation::$navcount++;

                if ($this->docked === null) {
            $this->docked = get_user_preferences('nav_in_tab_panel_globalnav'.block_navigation::$navcount, 0);
        }

                if ($this->docked && optional_param('undock', null, PARAM_INT)==$this->instance->id) {
            unset_user_preference('nav_in_tab_panel_globalnav'.block_navigation::$navcount);
            $url = $this->page->url;
            $url->remove_params(array('undock'));
            redirect($url);
        } else if (!$this->docked && optional_param('dock', null, PARAM_INT)==$this->instance->id) {
            set_user_preferences(array('nav_in_tab_panel_globalnav'.block_navigation::$navcount=>1));
            $url = $this->page->url;
            $url->remove_params(array('dock'));
            redirect($url);
        }

        $trimmode = self::TRIM_RIGHT;
        $trimlength = 50;

        if (!empty($this->config->trimmode)) {
            $trimmode = (int)$this->config->trimmode;
        }

        if (!empty($this->config->trimlength)) {
            $trimlength = (int)$this->config->trimlength;
        }

                if (!$navigation = $this->get_navigation()) {
            return null;
        }
        $expansionlimit = null;
        if (!empty($this->config->expansionlimit)) {
            $expansionlimit = $this->config->expansionlimit;
            $navigation->set_expansion_limit($this->config->expansionlimit);
        }
        $this->trim($navigation, $trimmode, $trimlength, ceil($trimlength/2));

                $expandable = array();
        $navigation->find_expandable($expandable);
        if ($expansionlimit) {
            foreach ($expandable as $key=>$node) {
                if ($node['type'] > $expansionlimit && !($expansionlimit == navigation_node::TYPE_COURSE && $node['type'] == $expansionlimit && $node['branchid'] == SITEID)) {
                    unset($expandable[$key]);
                }
            }
        }

        $limit = 20;
        if (!empty($CFG->navcourselimit)) {
            $limit = $CFG->navcourselimit;
        }
        $expansionlimit = 0;
        if (!empty($this->config->expansionlimit)) {
            $expansionlimit = $this->config->expansionlimit;
        }
        $arguments = array(
            'id'             => $this->instance->id,
            'instance'       => $this->instance->id,
            'candock'        => $this->instance_can_be_docked(),
            'courselimit'    => $limit,
            'expansionlimit' => $expansionlimit
        );

        $options = array();
        $options['linkcategories'] = (!empty($this->config->linkcategories) && $this->config->linkcategories == 'yes');

                $renderer = $this->page->get_renderer($this->blockname);
        $this->content = new stdClass();
        $this->content->text = $renderer->navigation_tree($navigation, $expansionlimit, $options);

                $this->contentgenerated = true;

        return $this->content;
    }

    
    protected function get_navigation() {
                $this->page->navigation->initialise();
        return clone($this->page->navigation);
    }

    
    public function html_attributes() {
        $attributes = parent::html_attributes();
        if (!empty($this->config->enablehoverexpansion) && $this->config->enablehoverexpansion == 'yes') {
            $attributes['class'] .= ' block_js_expansion';
        }
        return $attributes;
    }

    
    public function trim(navigation_node $node, $mode=1, $long=50, $short=25, $recurse=true) {
        switch ($mode) {
            case self::TRIM_RIGHT :
                if (core_text::strlen($node->text)>($long+3)) {
                                        $node->text = $this->trim_right($node->text, $long);
                }
                if (is_string($node->shorttext) && core_text::strlen($node->shorttext)>($short+3)) {
                                        $node->shorttext = $this->trim_right($node->shorttext, $short);
                }
                break;
            case self::TRIM_LEFT :
                if (core_text::strlen($node->text)>($long+3)) {
                                        $node->text = $this->trim_left($node->text, $long);
                }
                if (is_string($node->shorttext) && core_text::strlen($node->shorttext)>($short+3)) {
                                        $node->shorttext = $this->trim_left($node->shorttext, $short);
                }
                break;
            case self::TRIM_CENTER :
                if (core_text::strlen($node->text)>($long+3)) {
                                        $node->text = $this->trim_center($node->text, $long);
                }
                if (is_string($node->shorttext) && core_text::strlen($node->shorttext)>($short+3)) {
                                        $node->shorttext = $this->trim_center($node->shorttext, $short);
                }
                break;
        }
        if ($recurse && $node->children->count()) {
            foreach ($node->children as &$child) {
                $this->trim($child, $mode, $long, $short, true);
            }
        }
    }
    
    protected function trim_left($string, $length) {
        return '...'.core_text::substr($string, core_text::strlen($string)-$length, $length);
    }
    
    protected function trim_right($string, $length) {
        return core_text::substr($string, 0, $length).'...';
    }
    
    protected function trim_center($string, $length) {
        $trimlength = ceil($length/2);
        $start = core_text::substr($string, 0, $trimlength);
        $end = core_text::substr($string, core_text::strlen($string)-$trimlength);
        $string = $start.'...'.$end;
        return $string;
    }

    
    public function get_aria_role() {
        return 'navigation';
    }
}
