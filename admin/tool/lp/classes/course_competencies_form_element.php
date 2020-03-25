<?php




defined('MOODLE_INTERNAL') || die();

global $CFG;

use core_competency\api;
use core_competency\external\competency_exporter;
require_once($CFG->libdir . '/form/autocomplete.php');


class tool_lp_course_competencies_form_element extends MoodleQuickForm_autocomplete {

    
    public function __construct($elementName=null, $elementLabel=null, $options=array(), $attributes=null) {
        global $OUTPUT;

        if ($elementName == null) {
                        return;
        }

        if (!isset($options['courseid'])) {
            throw new coding_exception('Course id is required for the course_competencies form element');
        }
        $courseid = $options['courseid'];

        if (!empty($options['cmid'])) {
            $current = \core_competency\api::list_course_module_competencies_in_course_module($options['cmid']);
            $ids = array();
            foreach ($current as $coursemodulecompetency) {
                array_push($ids, $coursemodulecompetency->get_competencyid());
            }
            $this->setValue($ids);
        }

        $competencies = api::list_course_competencies($courseid);
        $validoptions = array();

        $context = context_course::instance($courseid);
        foreach ($competencies as $competency) {
                        $competency['competency']->set_description(null);
            $exporter = new competency_exporter($competency['competency'], array('context' => $context));
            $templatecontext = array('competency' => $exporter->export($OUTPUT));
            $id = $competency['competency']->get_id();
            $validoptions[$id] = $OUTPUT->render_from_template('tool_lp/competency_summary', $templatecontext);
        }
        $attributes['tags'] = false;
        $attributes['multiple'] = 'multiple';
        parent::__construct($elementName, $elementLabel, $validoptions, $attributes);
    }
}
