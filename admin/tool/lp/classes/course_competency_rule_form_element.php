<?php




defined('MOODLE_INTERNAL') || die();

global $CFG;

use core_competency\api;
use core_competency\external\competency_exporter;
use core_competency\course_module_competency;

require_once($CFG->libdir . '/form/select.php');


class tool_lp_course_competency_rule_form_element extends MoodleQuickForm_select {

    
    public function __construct($elementName=null, $elementLabel=null, $options=array(), $attributes=null) {
        if ($elementName == null) {
                        return;
        }

        if (!empty($options['cmid'])) {
            $cmid = $options['cmid'];

            $current = \core_competency\api::list_course_module_competencies_in_course_module($cmid);

                                    if (!empty($current)) {
                $one = array_pop($current);
                $this->setValue($one->get_ruleoutcome());
            }
        }
        $validoptions = course_module_competency::get_ruleoutcome_list();
        parent::__construct($elementName, $elementLabel, $validoptions, $attributes);
    }
}
