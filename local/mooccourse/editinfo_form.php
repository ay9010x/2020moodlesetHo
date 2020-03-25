<?php

defined('MOODLE_INTERNAL') || die;

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->libdir.'/completionlib.php');
require_once($CFG->libdir. '/coursecatlib.php');

class local_mooccourse_edit_info_form extends moodleform {
    public static $datefieldoptions = array('optional' => true, 'step' => 1);
    protected $course;
    protected $context;

    function definition() {
        global $CFG, $PAGE;

        $mform         = $this->_form;
        $PAGE->requires->yui_module('moodle-course-formatchooser', 'M.course.init_formatchooser', array(array('formid' => $mform->getAttribute('id'))));

        $course        = $this->_customdata['course'];
        $editoroptions = $this->_customdata['editoroptions'];
        $returnto      = $this->_customdata['returnto'];
        
        $systemcontext = context_system::instance();
        $coursecontext = context_course::instance($course->id);
        $this->course  = $course;
        $this->context = $coursecontext;
        
        $mform->addElement('hidden', 'id', $course->id); 
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'returnto', null);
        $mform->setType('returnto', PARAM_ALPHANUM);
        $mform->setConstant('returnto', $returnto);
        
        
        $mform->addElement('header', 'descriptionhdr', get_string('course_info', 'local_mooccourse'));
                
        $mform->addElement('editor','summary_editor', get_string('coursesummary'), null, $editoroptions);
        $mform->addHelpButton('summary_editor', 'coursesummary');
        $mform->setType('summary_editor', PARAM_RAW);
        $summaryfields = 'summary_editor';

        if ($overviewfilesoptions = course_overviewfiles_options($course)) {
            $mform->addElement('filemanager', 'overviewfiles_filemanager', get_string('courseoverviewfiles'), null, $overviewfilesoptions);
            $mform->addHelpButton('overviewfiles_filemanager', 'courseoverviewfiles');
            $summaryfields .= ',overviewfiles_filemanager';
        }
        
        $mform->addElement('header', 'contenthdr', get_string('course_content', 'local_mooccourse'));
        $mform->setExpanded('contenthdr', true);    // by YCJ
        $mform->addElement('editor','outline_editor', get_string('course_outline','local_mooccourse'), null, $editoroptions);
        $mform->setType('outline_editor', PARAM_RAW);
        $summaryfields .= ',outline_editor';
        
        $mform->addElement('editor','point_editor', get_string('course_point','local_mooccourse'), null, $editoroptions);
        $mform->setType('point_editor', PARAM_RAW);
        $summaryfields .= ',point_editor';
        
        $mform->addElement('editor','officehour_editor', get_string('course_officehour','local_mooccourse'), null, $editoroptions);
        $mform->setType('officehour_editor', PARAM_RAW);
        $summaryfields .= ',officehour_editor';
        
        // by YCJ        
        $mform->addElement('editor','bible_editor', get_string('course_bible','local_mooccourse'), null, $editoroptions);
        $mform->setType('bible_editor', PARAM_RAW);
        $summaryfields .= ',bible_editor';
        
        $mform->addElement('editor','qna_editor', get_string('course_qna','local_mooccourse'), null, $editoroptions);
        $mform->setType('qna_editor', PARAM_RAW);
        $summaryfields .= ',qna_editor';
                
        if (!empty($course->id) and !has_capability('moodle/course:changesummary', $coursecontext)) {
            $mform->removeElement('descriptionhdr');
            $mform->hardFreeze($summaryfields);
        }
        
        $mform->addElement('header', 'whetheropenhdr', get_string('forbidden', 'local_mooccourse'));
        $mform->setExpanded('whetheropenhdr', true);    // by YCJ
        $option = array('auditor'=>get_string('auditor', 'local_mooccourse'), 'student'=>get_string('student', 'local_mooccourse'));
        $forbidden = array();
        $forbiddens = array();
        if(!empty($course->forbiddens)){
            $forbiddens = explode(',', $course->forbiddens);
        }
        foreach($option as $key => $text){
            $forbidden[] =& $mform->createElement('checkbox', $key, null, $text);
            if(in_array($key, $forbiddens)){
                $mform->setDefault('forbidden['.$key.']', true);
            }
        }
        $mform->addGroup($forbidden, 'forbidden', get_string('forbidden', 'local_mooccourse'),null,true);
        $mform->addHelpButton('forbidden', 'forbidden','local_mooccourse');
        
                $mform->addElement('header', 'courseformathdr', get_string('type_format', 'plugin'));

        $courseformats = get_sorted_course_formats(true);
        $formcourseformats = array();
        foreach ($courseformats as $courseformat) {
            $formcourseformats[$courseformat] = get_string('pluginname', "format_$courseformat");
        }
        if (isset($course->format)) {
            $course->format = course_get_format($course)->get_format();             if (!in_array($course->format, $courseformats)) {
                                $formcourseformats[$course->format] = get_string('withdisablednote', 'moodle',
                        get_string('pluginname', 'format_'.$course->format));
            }
        }

        $mform->addElement('select', 'format', get_string('format'), $formcourseformats);
        $mform->addHelpButton('format', 'format');
        
                $mform->registerNoSubmitButton('updatecourseformat');
        $mform->addElement('submit', 'updatecourseformat', get_string('courseformatudpate'));

                $mform->addElement('hidden', 'addcourseformatoptionshere');
        $mform->setType('addcourseformatoptionshere', PARAM_BOOL);
        
                $mform->addElement('header', 'appearancehdr', get_string('appearance'));

        if (!empty($CFG->allowcoursethemes)) {
            $themeobjects = get_list_of_themes();
            $themes=array();
            $themes[''] = get_string('forceno');
            foreach ($themeobjects as $key=>$theme) {
                if (empty($theme->hidefromselector)) {
                    $themes[$key] = get_string('pluginname', 'theme_'.$theme->name);
                }
            }
            $mform->addElement('select', 'theme', get_string('forcetheme'), $themes);
        }

        $languages=array();
        $languages[''] = get_string('forceno');
        $languages += get_string_manager()->get_list_of_translations();
        $mform->addElement('select', 'lang', get_string('forcelanguage'), $languages);

                $calendartypes = \core_calendar\type_factory::get_list_of_calendar_types();
                if (count($calendartypes) > 1) {
            $calendars = array();
            $calendars[''] = get_string('forceno');
            $calendars += $calendartypes;
            $mform->addElement('select', 'calendartype', get_string('forcecalendartype', 'calendar'), $calendars);
        }

        $options = range(0, 10);
        $mform->addElement('select', 'newsitems', get_string('newsitemsnumber'), $options);
        $mform->addHelpButton('newsitems', 'newsitemsnumber');

        $mform->addElement('selectyesno', 'showgrades', get_string('showgrades'));
        $mform->addHelpButton('showgrades', 'showgrades');

        $mform->addElement('selectyesno', 'showreports', get_string('showreports'));
        $mform->addHelpButton('showreports', 'showreports');
        
        enrol_course_edit_form($mform, $course, $coursecontext);

        $mform->addElement('header','groups', get_string('groupsettingsheader', 'group'));

        $choices = array();
        $choices[NOGROUPS] = get_string('groupsnone', 'group');
        $choices[SEPARATEGROUPS] = get_string('groupsseparate', 'group');
        $choices[VISIBLEGROUPS] = get_string('groupsvisible', 'group');
        $mform->addElement('select', 'groupmode', get_string('groupmode', 'group'), $choices);
        $mform->addHelpButton('groupmode', 'groupmode', 'group');

        $mform->addElement('selectyesno', 'groupmodeforce', get_string('groupmodeforce', 'group'));
        $mform->addHelpButton('groupmodeforce', 'groupmodeforce', 'group');

                $options = array();
        $options[0] = get_string('none');
        $mform->addElement('select', 'defaultgroupingid', get_string('defaultgrouping', 'group'), $options);
        
        $this->add_action_buttons();

        $this->set_data($course);
    }

    function definition_after_data() {
        global $DB;

        $mform = $this->_form;

                if ($courseid = $mform->getElementValue('id') and $mform->elementExists('defaultgroupingid')) {
            $options = array();
            if ($groupings = $DB->get_records('groupings', array('courseid'=>$courseid))) {
                foreach ($groupings as $grouping) {
                    $options[$grouping->id] = format_string($grouping->name);
                }
            }
            core_collator::asort($options);
            $gr_el =& $mform->getElement('defaultgroupingid');
            $gr_el->load($options);
        }

                $formatvalue = $mform->getElementValue('format');
        if (is_array($formatvalue) && !empty($formatvalue)) {
            $courseformat = course_get_format((object)array('format' => $formatvalue[0]));

            $elements = $courseformat->create_edit_form_elements($mform);
            for ($i = 0; $i < count($elements); $i++) {
                $mform->insertElementBefore($mform->removeElement($elements[$i]->getName(), false),
                        'addcourseformatoptionshere');
            }
        }
    }

    function validation($data, $files) {
        global $DB, $CFG;
        $errors = parent::validation($data, $files);

        $errors = array_merge($errors, enrol_course_edit_validation($data, $this->context));

        $courseformat = course_get_format((object)array('format' => $data['format']));
        $formaterrors = $courseformat->edit_form_validation($data, $files, $errors);
        if (!empty($formaterrors) && is_array($formaterrors)) {
            $errors = array_merge($errors, $formaterrors);
        }
        
        return $errors;
    }
}