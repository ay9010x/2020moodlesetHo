<?php





define('BLOCK_TYPE_LIST',    1);


define('BLOCK_TYPE_TEXT',    2);

define('BLOCK_TYPE_TREE',    3);


class block_base {

    
    var $str;

    
    var $title         = NULL;

    
    var $arialabel         = NULL;

    
    var $content_type  = BLOCK_TYPE_TEXT;

    
    var $content       = NULL;

    
    var $instance      = NULL;

    
    public $page       = NULL;

    
    public $context    = NULL;

    
    var $config        = NULL;

    

    var $cron          = NULL;


    
    function __construct() {
        $this->init();
    }

    
    function before_delete() {
    }

    
    function name() {
                        static $myname;
        if ($myname === NULL) {
            $myname = strtolower(get_class($this));
            $myname = substr($myname, strpos($myname, '_') + 1);
        }
        return $myname;
    }

    
    function get_content() {
                return NULL;
    }

    
    function get_title() {
                return $this->title;
    }

    
    function get_content_type() {
                return $this->content_type;
    }

    
    function is_empty() {
        if ( !has_capability('moodle/block:view', $this->context) ) {
            return true;
        }

        $this->get_content();
        return(empty($this->content->text) && empty($this->content->footer));
    }

    
    function refresh_content() {
                $this->content = NULL;
        return $this->get_content();
    }

    
    public function get_content_for_output($output) {
        global $CFG;

        $bc = new block_contents($this->html_attributes());
        $bc->attributes['data-block'] = $this->name();
        $bc->blockinstanceid = $this->instance->id;
        $bc->blockpositionid = $this->instance->blockpositionid;

        if ($this->instance->visible) {
            $bc->content = $this->formatted_contents($output);
            if (!empty($this->content->footer)) {
                $bc->footer = $this->content->footer;
            }
        } else {
            $bc->add_class('invisible');
        }

        if (!$this->hide_header()) {
            $bc->title = $this->title;
        }

        if (empty($bc->title)) {
            $bc->arialabel = new lang_string('pluginname', get_class($this));
            $this->arialabel = $bc->arialabel;
        }

        if ($this->page->user_is_editing()) {
            $bc->controls = $this->page->blocks->edit_controls($this);
        } else {
                        if ($this->is_empty() && !$bc->controls) {
                return null;
            }
        }

        if (empty($CFG->allowuserblockhiding)
                || (empty($bc->content) && empty($bc->footer))
                || !$this->instance_can_be_collapsed()) {
            $bc->collapsible = block_contents::NOT_HIDEABLE;
        } else if (get_user_preferences('block' . $bc->blockinstanceid . 'hidden', false)) {
            $bc->collapsible = block_contents::HIDDEN;
        } else {
            $bc->collapsible = block_contents::VISIBLE;
        }

        if ($this->instance_can_be_docked() && !$this->hide_header()) {
            $bc->dockable = true;
        }

        $bc->annotation = ''; 
        return $bc;
    }

    
    protected function formatted_contents($output) {
        $this->get_content();
        $this->get_required_javascript();
        if (!empty($this->content->text)) {
            return $this->content->text;
        } else {
            return '';
        }
    }

    

    function _self_test() {
                        $errors = array();

        $correct = true;
        if ($this->get_title() === NULL) {
            $errors[] = 'title_not_set';
            $correct = false;
        }
        if (!in_array($this->get_content_type(), array(BLOCK_TYPE_LIST, BLOCK_TYPE_TEXT, BLOCK_TYPE_TREE))) {
            $errors[] = 'invalid_content_type';
            $correct = false;
        }
        
        $formats = $this->applicable_formats();
        if (empty($formats) || array_sum($formats) === 0) {
            $errors[] = 'no_formats';
            $correct = false;
        }

        return $correct;
    }

    
    function has_config() {
        return false;
    }

    
    function config_save($data) {
        throw new coding_exception('config_save() can not be used any more, use Admin Settings functionality to save block configuration.');
    }

    
    function applicable_formats() {
                return array('all' => true, 'mod' => false, 'tag' => false);
    }


    
    function hide_header() {
        return false;
    }

    
    function html_attributes() {
        $attributes = array(
            'id' => 'inst' . $this->instance->id,
            'class' => 'block_' . $this->name(). '  block',
            'role' => $this->get_aria_role()
        );
        if ($this->hide_header()) {
            $attributes['class'] .= ' no-header';
        }
        if ($this->instance_can_be_docked() && get_user_preferences('docked_block_instance_'.$this->instance->id, 0)) {
            $attributes['class'] .= ' dock_on_load';
        }
        return $attributes;
    }

    
    function _load_instance($instance, $page) {
        if (!empty($instance->configdata)) {
            $this->config = unserialize(base64_decode($instance->configdata));
        }
        $this->instance = $instance;
        $this->context = context_block::instance($instance->id);
        $this->page = $page;
        $this->specialization();
    }

    
    function get_required_javascript() {
        if ($this->instance_can_be_docked() && !$this->hide_header()) {
            user_preference_allow_ajax_update('docked_block_instance_'.$this->instance->id, PARAM_INT);
        }
    }

    
    function specialization() {
            }

    
    function instance_allow_config() {
        return false;
    }

    
    function instance_allow_multiple() {
                        return false;
    }

    
    function instance_config_save($data, $nolongerused = false) {
        global $DB;
        $DB->set_field('block_instances', 'configdata', base64_encode(serialize($data)),
                array('id' => $this->instance->id));
    }

    
    function instance_config_commit($nolongerused = false) {
        global $DB;
        $this->instance_config_save($this->config);
    }

    
    function instance_create() {
        return true;
    }

    
    public function instance_copy($fromid) {
        return true;
    }

    
    function instance_delete() {
        return true;
    }

    
    function user_can_edit() {
        global $USER;

        if (has_capability('moodle/block:edit', $this->context)) {
            return true;
        }

                if (!empty($USER->id)
            && $this->instance->parentcontextid == $this->page->context->id               && $this->page->context->contextlevel == CONTEXT_USER                         && $this->page->context->instanceid == $USER->id) {                           return has_capability('moodle/my:manageblocks', $this->page->context);
        }

        return false;
    }

    
    function user_can_addto($page) {
        global $USER;

                if (!empty($USER->id)
            && $page->context->contextlevel == CONTEXT_USER             && $page->context->instanceid == $USER->id             && $page->pagetype == 'my-index') { 
                        $formats = $this->applicable_formats();
                                    if ((isset($formats['my']) && $formats['my'] == false)
                || (empty($formats['all']) && empty($formats['my']))) {

                                return false;
            } else {
                $capability = 'block/' . $this->name() . ':myaddinstance';
                return $this->has_add_block_capability($page, $capability)
                       && has_capability('moodle/my:manageblocks', $page->context);
            }
        }

        $capability = 'block/' . $this->name() . ':addinstance';
        if ($this->has_add_block_capability($page, $capability)
                && has_capability('moodle/block:edit', $page->context)) {
            return true;
        }

        return false;
    }

    
    private function has_add_block_capability($page, $capability) {
                if (!get_capability_info($capability)) {
                        static $warned = array();
            if (!isset($warned[$this->name()])) {
                debugging('The block ' .$this->name() . ' does not define the standard capability ' .
                        $capability , DEBUG_DEVELOPER);
                $warned[$this->name()] = 1;
            }
                        return true;
        } else {
            return has_capability($capability, $page->context);
        }
    }

    static function get_extra_capabilities() {
        return array('moodle/block:view', 'moodle/block:edit');
    }

    
    public function instance_can_be_docked() {
        global $CFG;
        return (!empty($CFG->allowblockstodock) && $this->page->theme->enable_dock);
    }

    
    public function instance_can_be_hidden() {
        return true;
    }

    
    public function instance_can_be_collapsed() {
        return true;
    }

    
    public static function comment_template($options) {
        $ret = <<<EOD
<div class="comment-userpicture">___picture___</div>
<div class="comment-content">
    ___name___ - <span>___time___</span>
    <div>___content___</div>
</div>
EOD;
        return $ret;
    }
    public static function comment_permissions($options) {
        return array('view'=>true, 'post'=>true);
    }
    public static function comment_url($options) {
        return null;
    }
    public static function comment_display($comments, $options) {
        return $comments;
    }
    public static function comment_add(&$comments, $options) {
        return true;
    }

    
    public function get_aria_role() {
        return 'complementary';
    }
}



class block_list extends block_base {
    var $content_type  = BLOCK_TYPE_LIST;

    function is_empty() {
        if ( !has_capability('moodle/block:view', $this->context) ) {
            return true;
        }

        $this->get_content();
        return (empty($this->content->items) && empty($this->content->footer));
    }

    protected function formatted_contents($output) {
        $this->get_content();
        $this->get_required_javascript();
        if (!empty($this->content->items)) {
            return $output->list_block_contents($this->content->icons, $this->content->items);
        } else {
            return '';
        }
    }

    function html_attributes() {
        $attributes = parent::html_attributes();
        $attributes['class'] .= ' list_block';
        return $attributes;
    }

}


class block_tree extends block_list {

    
    public $content_type = BLOCK_TYPE_TREE;

    
    protected function formatted_contents($output) {
                global $PAGE;         static $eventattached;
        if ($eventattached===null) {
            $eventattached = true;
        }
        if (!$this->content) {
            $this->content = new stdClass;
            $this->content->items = array();
        }
        $this->get_required_javascript();
        $this->get_content();
        $content = $output->tree_block_contents($this->content->items,array('class'=>'block_tree list'));
        if (isset($this->id) && !is_numeric($this->id)) {
            $content = $output->box($content, 'block_tree_box', $this->id);
        }
        return $content;
    }
}
