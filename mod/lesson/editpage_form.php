<?php




defined('MOODLE_INTERNAL') || die();


class lesson_add_page_form_selection extends lesson_add_page_form_base {

    public $qtype = 'questiontype';
    public $qtypestring = 'selectaqtype';
    protected $standard = false;
    protected $manager = null;

    public function __construct($arg1, $arg2) {
        $this->manager = lesson_page_type_manager::get($arg2['lesson']);
        parent::__construct($arg1, $arg2);
    }

    public function custom_definition() {
        $mform = $this->_form;
        $types = $this->manager->get_page_type_strings(lesson_page::TYPE_QUESTION);
        asort($types);
        $mform->addElement('select', 'qtype', get_string('selectaqtype', 'lesson'), $types);
        $mform->setDefault('qtype', LESSON_PAGE_MULTICHOICE);     }
}


final class lesson_add_page_form_unknown extends lesson_add_page_form_base {}
