<?php




global $CFG;
require_once($CFG->libdir . '/form/autocomplete.php');


class MoodleQuickForm_course extends MoodleQuickForm_autocomplete {

    
    protected $exclude = array();

    
    protected $multiple = false;

    
    protected $requiredcapabilities = array();

    
    protected $limittoenrolled = false;

    
    public function __construct($elementname = null, $elementlabel = null, $options = array()) {
        if (isset($options['multiple'])) {
            $this->multiple = $options['multiple'];
        }
        if (isset($options['exclude'])) {
            $this->exclude = $options['exclude'];
            if (!is_array($this->exclude)) {
                $this->exclude = array($this->exclude);
            }
        }
        if (isset($options['requiredcapabilities'])) {
            $this->requiredcapabilities = $options['requiredcapabilities'];
        }
        if (isset($options['limittoenrolled'])) {
            $this->limittoenrolled = $options['limittoenrolled'];
        }

        $validattributes = array(
            'ajax' => 'core/form-course-selector',
            'data-requiredcapabilities' => implode(',', $this->requiredcapabilities),
            'data-exclude' => implode(',', $this->exclude),
            'data-limittoenrolled' => (int)$this->limittoenrolled
        );
        if ($this->multiple) {
            $validattributes['multiple'] = 'multiple';
        }
        if (isset($options['noselectionstring'])) {
            $validattributes['noselectionstring'] = $options['noselectionstring'];
        }
        if (isset($options['placeholder'])) {
            $validattributes['placeholder'] = $options['placeholder'];
        }
        if (!empty($options['includefrontpage'])) {
            $validattributes['data-includefrontpage'] = SITEID;
        }

        parent::__construct($elementname, $elementlabel, array(), $validattributes);
    }

    
    public function setValue($value) {
        global $DB;
        $values = (array) $value;
        $coursestofetch = array();

        foreach ($values as $onevalue) {
            if ((!$this->optionExists($onevalue)) &&
                    ($onevalue !== '_qf__force_multiselect_submission')) {
                array_push($coursestofetch, $onevalue);
            }
        }

        if (empty($coursestofetch)) {
            return $this->setSelected($values);
        }

                $ctxselect = context_helper::get_preload_record_columns_sql('ctx');
        $fields = array('c.id', 'c.category', 'c.sortorder',
                        'c.shortname', 'c.fullname', 'c.idnumber',
                        'c.startdate', 'c.visible', 'c.cacherev');
        list($whereclause, $params) = $DB->get_in_or_equal($coursestofetch, SQL_PARAMS_NAMED, 'id');

        $sql = "SELECT ". join(',', $fields). ", $ctxselect
                FROM {course} c
                JOIN {context} ctx ON c.id = ctx.instanceid AND ctx.contextlevel = :contextcourse
                WHERE c.id ". $whereclause." ORDER BY c.sortorder";
        $list = $DB->get_records_sql($sql, array('contextcourse' => CONTEXT_COURSE) + $params);

        $coursestoselect = array();
        foreach ($list as $course) {
            context_helper::preload_from_record($course);
            $context = context_course::instance($course->id);
                        if (!$course->visible && !has_capability('moodle/course:viewhiddencourses', $context)) {
                continue;
            }
            $label = format_string(get_course_display_name_for_list($course), true, ['context' => $context]);
            $this->addOption($label, $course->id);
            array_push($coursestoselect, $course->id);
        }

        return $this->setSelected($values);
    }
}
