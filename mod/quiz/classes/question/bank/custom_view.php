<?php



namespace mod_quiz\question\bank;
defined('MOODLE_INTERNAL') || die();



class custom_view extends \core_question\bank\view {
    
    protected $quizhasattempts = false;
    
    protected $quiz = false;
    
    const MAX_TEXT_LENGTH = 200;

    
    public function __construct($contexts, $pageurl, $course, $cm, $quiz) {
        parent::__construct($contexts, $pageurl, $course, $cm);
        $this->quiz = $quiz;
    }

    protected function wanted_columns() {
        global $CFG;

        if (empty($CFG->quizquestionbankcolumns)) {
            $quizquestionbankcolumns = array(
                'add_action_column',
                'checkbox_column',
                'question_type_column',
                'question_name_text_column',
                'preview_action_column',
            );
        } else {
            $quizquestionbankcolumns = explode(',', $CFG->quizquestionbankcolumns);
        }

        foreach ($quizquestionbankcolumns as $fullname) {
            if (!class_exists($fullname)) {
                if (class_exists('mod_quiz\\question\\bank\\' . $fullname)) {
                    $fullname = 'mod_quiz\\question\\bank\\' . $fullname;
                } else if (class_exists('core_question\\bank\\' . $fullname)) {
                    $fullname = 'core_question\\bank\\' . $fullname;
                } else if (class_exists('question_bank_' . $fullname)) {
                    debugging('Legacy question bank column class question_bank_' .
                            $fullname . ' should be renamed to mod_quiz\\question\\bank\\' .
                            $fullname, DEBUG_DEVELOPER);
                    $fullname = 'question_bank_' . $fullname;
                } else {
                    throw new coding_exception("No such class exists: $fullname");
                }
            }
            $this->requiredcolumns[$fullname] = new $fullname($this);
        }
        return $this->requiredcolumns;
    }

    
    protected function heading_column() {
        return 'mod_quiz\\question\\bank\\question_name_text_column';
    }

    protected function default_sort() {
        return array(
            'core_question\\bank\\question_type_column' => 1,
            'mod_quiz\\question\\bank\\question_name_text_column' => 1,
        );
    }

    
    public function set_quiz_has_attempts($quizhasattempts) {
        $this->quizhasattempts = $quizhasattempts;
        if ($quizhasattempts && isset($this->visiblecolumns['addtoquizaction'])) {
            unset($this->visiblecolumns['addtoquizaction']);
        }
    }

    public function preview_question_url($question) {
        return quiz_question_preview_url($this->quiz, $question);
    }

    public function add_to_quiz_url($questionid) {
        global $CFG;
        $params = $this->baseurl->params();
        $params['addquestion'] = $questionid;
        $params['sesskey'] = sesskey();
        return new \moodle_url('/mod/quiz/edit.php', $params);
    }

    
    public function render($tabname, $page, $perpage, $cat, $recurse, $showhidden, $showquestiontext) {
        ob_start();
        $this->display($tabname, $page, $perpage, $cat, $recurse, $showhidden, $showquestiontext);
        $out = ob_get_contents();
        ob_end_clean();
        return $out;
    }

    
    protected function display_bottom_controls($totalnumber, $recurse, $category, \context $catcontext, array $addcontexts) {
        $cmoptions = new \stdClass();
        $cmoptions->hasattempts = !empty($this->quizhasattempts);

        $canuseall = has_capability('moodle/question:useall', $catcontext);

        echo '<div class="modulespecificbuttonscontainer">';
        if ($canuseall) {

                        $params = array(
                    'type' => 'submit',
                    'name' => 'add',
                    'value' => get_string('addselectedquestionstoquiz', 'quiz'),
            );
            if ($cmoptions->hasattempts) {
                $params['disabled'] = 'disabled';
            }
            echo \html_writer::empty_tag('input', $params);
        }
        echo "</div>\n";
    }

    
    protected function print_choose_category_message($categoryandcontext) {
        global $OUTPUT;
        debugging('print_choose_category_message() is deprecated, ' .
                'please use \core_question\bank\search\category_condition instead.', DEBUG_DEVELOPER);
        echo $OUTPUT->box_start('generalbox questionbank');
        $this->display_category_form($this->contexts->having_one_edit_tab_cap('edit'),
                $this->baseurl, $categoryandcontext);
        echo "<p style=\"text-align:center;\"><b>";
        print_string('selectcategoryabove', 'question');
        echo "</b></p>";
        echo $OUTPUT->box_end();
    }

    protected function display_options_form($showquestiontext, $scriptpath = '/mod/quiz/edit.php',
            $showtextoption = false) {
                parent::display_options_form($showquestiontext, $scriptpath, $showtextoption);
    }

    protected function print_category_info($category) {
        $formatoptions = new stdClass();
        $formatoptions->noclean = true;
        $strcategory = get_string('category', 'quiz');
        echo '<div class="categoryinfo"><div class="categorynamefieldcontainer">' .
                $strcategory;
        echo ': <span class="categorynamefield">';
        echo shorten_text(strip_tags(format_string($category->name)), 60);
        echo '</span></div><div class="categoryinfofieldcontainer">' .
                '<span class="categoryinfofield">';
        echo shorten_text(strip_tags(format_text($category->info, $category->infoformat,
                $formatoptions, $this->course->id)), 200);
        echo '</span></div></div>';
    }

    protected function display_options($recurse, $showhidden, $showquestiontext) {
        debugging('display_options() is deprecated, see display_options_form() instead.', DEBUG_DEVELOPER);
        echo '<form method="get" action="edit.php" id="displayoptions">';
        echo "<fieldset class='invisiblefieldset'>";
        echo \html_writer::input_hidden_params($this->baseurl,
                array('recurse', 'showhidden', 'qbshowtext'));
        $this->display_category_form_checkbox('recurse', $recurse,
                get_string('includesubcategories', 'question'));
        $this->display_category_form_checkbox('showhidden', $showhidden,
                get_string('showhidden', 'question'));
        echo '<noscript><div class="centerpara"><input type="submit" value="' .
                get_string('go') . '" />';
        echo '</div></noscript></fieldset></form>';
    }

    protected function create_new_question_form($category, $canadd) {
            }
}
