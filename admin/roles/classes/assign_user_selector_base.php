<?php



defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/user/selector/lib.php');


abstract class core_role_assign_user_selector_base extends user_selector_base {
    protected $roleid;
    protected $context;

    
    public function __construct($name, $options) {
        global $CFG;
        if (isset($options['context'])) {
            $this->context = $options['context'];
        } else {
            $this->context = context::instance_by_id($options['contextid']);
        }
        $options['accesscontext'] = $this->context;
        parent::__construct($name, $options);
        $this->roleid = $options['roleid'];
        require_once($CFG->dirroot . '/group/lib.php');
    }

    protected function get_options() {
        global $CFG;
        $options = parent::get_options();
        $options['file'] = $CFG->admin . '/roles/lib.php';
        $options['roleid'] = $this->roleid;
        $options['contextid'] = $this->context->id;
        return $options;
    }
}
