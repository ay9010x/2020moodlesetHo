<?php



namespace mod_quiz\output;
defined('MOODLE_INTERNAL') || die();

use \mod_quiz\structure;
use \html_writer;


class edit_renderer extends \plugin_renderer_base {

    
    public function edit_page(\quiz $quizobj, structure $structure,
            \question_edit_contexts $contexts, \moodle_url $pageurl, array $pagevars) {
        $output = '';

                $output .= $this->heading_with_help(get_string('editingquizx', 'quiz',
                format_string($quizobj->get_quiz_name())), 'editingquiz', 'quiz', '',
                get_string('basicideasofquiz', 'quiz'), 2);

                $output .= $this->quiz_state_warnings($structure);
        $output .= $this->quiz_information($structure);
        $output .= $this->maximum_grade_input($structure, $pageurl);
        $output .= $this->repaginate_button($structure, $pageurl);
        $output .= $this->total_marks($quizobj->get_quiz());

                $output .= $this->start_section_list($structure);

        foreach ($structure->get_sections() as $section) {
            $output .= $this->start_section($structure, $section);
            $output .= $this->questions_in_section($structure, $section, $contexts, $pagevars, $pageurl);

            if ($structure->is_last_section($section)) {
                $output .= \html_writer::start_div('last-add-menu');
                $output .= html_writer::tag('span', $this->add_menu_actions($structure, 0,
                        $pageurl, $contexts, $pagevars), array('class' => 'add-menu-outer'));
                $output .= \html_writer::end_div();
            }

            $output .= $this->end_section();
        }

        $output .= $this->end_section_list();

                $this->initialise_editing_javascript($structure, $contexts, $pagevars, $pageurl);

                if ($structure->can_be_edited()) {
            $popups = '';

            $popups .= $this->question_bank_loading();
            $this->page->requires->yui_module('moodle-mod_quiz-quizquestionbank',
                    'M.mod_quiz.quizquestionbank.init',
                    array('class' => 'questionbank', 'cmid' => $structure->get_cmid()));

            $popups .= $this->random_question_form($pageurl, $contexts, $pagevars);
            $this->page->requires->yui_module('moodle-mod_quiz-randomquestion',
                    'M.mod_quiz.randomquestion.init');

            $output .= html_writer::div($popups, 'mod_quiz_edit_forms');

                        $output .= $this->question_chooser();
            $this->page->requires->yui_module('moodle-mod_quiz-questionchooser', 'M.mod_quiz.init_questionchooser');
        }

        return $output;
    }

    
    public function quiz_state_warnings(structure $structure) {
        $warnings = $structure->get_edit_page_warnings();

        if (empty($warnings)) {
            return '';
        }

        $output = array();
        foreach ($warnings as $warning) {
            $output[] = \html_writer::tag('p', $warning);
        }
        return $this->box(implode("\n", $output), 'statusdisplay');
    }

    
    public function quiz_information(structure $structure) {
        list($currentstatus, $explanation) = $structure->get_dates_summary();

        $output = html_writer::span(
                    get_string('numquestionsx', 'quiz', $structure->get_question_count()),
                    'numberofquestions') . ' | ' .
                html_writer::span($currentstatus, 'quizopeningstatus',
                    array('title' => $explanation));

        return html_writer::div($output, 'statusbar');
    }

    
    public function maximum_grade_input($structure, \moodle_url $pageurl) {
        $output = '';
        $output .= html_writer::start_div('maxgrade');
        $output .= html_writer::start_tag('form', array('method' => 'post', 'action' => 'edit.php',
                'class' => 'quizsavegradesform'));
        $output .= html_writer::start_tag('fieldset', array('class' => 'invisiblefieldset'));
        $output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()));
        $output .= html_writer::input_hidden_params($pageurl);
        $output .= html_writer::tag('label', get_string('maximumgrade') . ' ',
                array('for' => 'inputmaxgrade'));
        $output .= html_writer::empty_tag('input', array('type' => 'text', 'id' => 'inputmaxgrade',
                'name' => 'maxgrade', 'size' => ($structure->get_decimal_places_for_grades() + 2),
                'value' => $structure->formatted_quiz_grade()));
        $output .= html_writer::empty_tag('input', array('type' => 'submit',
                'name' => 'savechanges', 'value' => get_string('save', 'quiz')));
        $output .= html_writer::end_tag('fieldset');
        $output .= html_writer::end_tag('form');
        $output .= html_writer::end_tag('div');
        return $output;
    }

    
    protected function repaginate_button(structure $structure, \moodle_url $pageurl) {

        $header = html_writer::tag('span', get_string('repaginatecommand', 'quiz'), array('class' => 'repaginatecommand'));
        $form = $this->repaginate_form($structure, $pageurl);
        $containeroptions = array(
                'class'  => 'rpcontainerclass',
                'cmid'   => $structure->get_cmid(),
                'header' => $header,
                'form'   => $form,
        );

        $buttonoptions = array(
            'type'  => 'submit',
            'name'  => 'repaginate',
            'id'    => 'repaginatecommand',
            'value' => get_string('repaginatecommand', 'quiz'),
        );
        if (!$structure->can_be_repaginated()) {
            $buttonoptions['disabled'] = 'disabled';
        } else {
            $this->page->requires->yui_module('moodle-mod_quiz-repaginate', 'M.mod_quiz.repaginate.init');
        }

        return html_writer::tag('div',
                html_writer::empty_tag('input', $buttonoptions), $containeroptions);
    }

    
    protected function repaginate_form(structure $structure, \moodle_url $pageurl) {
        $perpage = array();
        $perpage[0] = get_string('allinone', 'quiz');
        for ($i = 1; $i <= 50; ++$i) {
            $perpage[$i] = $i;
        }

        $hiddenurl = clone($pageurl);
        $hiddenurl->param('sesskey', sesskey());

        $select = html_writer::select($perpage, 'questionsperpage',
                $structure->get_questions_per_page(), false);

        $buttonattributes = array('type' => 'submit', 'name' => 'repaginate', 'value' => get_string('go'));

        $formcontent = html_writer::tag('form', html_writer::div(
                    html_writer::input_hidden_params($hiddenurl) .
                    get_string('repaginate', 'quiz', $select) .
                    html_writer::empty_tag('input', $buttonattributes)
                ), array('action' => 'edit.php', 'method' => 'post'));

        return html_writer::div($formcontent, '', array('id' => 'repaginatedialog'));
    }

    
    public function total_marks($quiz) {
        $totalmark = html_writer::span(quiz_format_grade($quiz, $quiz->sumgrades), 'mod_quiz_summarks');
        return html_writer::tag('span',
                get_string('totalmarksx', 'quiz', $totalmark),
                array('class' => 'totalpoints'));
    }

    
    protected function start_section_list(structure $structure) {
        $class = 'slots';
        if ($structure->get_section_count() == 1) {
            $class .= ' only-one-section';
        }
        return html_writer::start_tag('ul', array('class' => $class));
    }

    
    protected function end_section_list() {
        return html_writer::end_tag('ul');
    }

    
    protected function start_section($structure, $section) {

        $output = '';

        $sectionstyle = '';
        if ($structure->is_only_one_slot_in_section($section)) {
            $sectionstyle = ' only-has-one-slot';
        }

        $output .= html_writer::start_tag('li', array('id' => 'section-'.$section->id,
            'class' => 'section main clearfix'.$sectionstyle, 'role' => 'region',
            'aria-label' => $section->heading));

        $output .= html_writer::start_div('content');

        $output .= html_writer::start_div('section-heading');

        $headingtext = $this->heading(html_writer::span(
                html_writer::span($section->heading, 'instancesection'), 'sectioninstance'), 3);

        if (!$structure->can_be_edited()) {
            $editsectionheadingicon = '';
        } else {
            $editsectionheadingicon = html_writer::link(new \moodle_url('#'),
                $this->pix_icon('t/editstring', get_string('sectionheadingedit', 'quiz', $section->heading),
                        'moodle', array('class' => 'editicon visibleifjs')),
                        array('class' => 'editing_section', 'data-action' => 'edit_section_title'));
        }
        $output .= html_writer::div($headingtext . $editsectionheadingicon, 'instancesectioncontainer');

        if (!$structure->is_first_section($section) && $structure->can_be_edited()) {
            $output .= $this->section_remove_icon($section);
        }
        $output .= $this->section_shuffle_questions($structure, $section);

        $output .= html_writer::end_div($output, 'section-heading');

        return $output;
    }

    
    public function section_shuffle_questions(structure $structure, $section) {
        $checkboxattributes = array(
            'type' => 'checkbox',
            'id' => 'shuffle-' . $section->id,
            'value' => 1,
            'data-action' => 'shuffle_questions',
            'class' => 'cm-edit-action',
        );

        if (!$structure->can_be_edited()) {
            $checkboxattributes['disabled'] = 'disabled';
        }
        if ($section->shufflequestions) {
            $checkboxattributes['checked'] = 'checked';
        }

        if ($structure->is_first_section($section)) {
            $help = $this->help_icon('shufflequestions', 'quiz');
        } else {
            $help = '';
        }

        $progressspan = html_writer::span('', 'shuffle-progress');
        $checkbox = html_writer::empty_tag('input', $checkboxattributes);
        $label = html_writer::label(get_string('shufflequestions', 'quiz') . ' ' . $help,
                $checkboxattributes['id'], false);
        return html_writer::span($progressspan . $checkbox . $label,
                'instanceshufflequestions', array('data-action' => 'shuffle_questions'));
    }

    
    protected function end_section() {
        $output = html_writer::end_tag('div');
        $output .= html_writer::end_tag('li');

        return $output;
    }

    
    public function section_remove_icon($section) {
        $title = get_string('sectionheadingremove', 'quiz', $section->heading);
        $url = new \moodle_url('/mod/quiz/edit.php',
                array('sesskey' => sesskey(), 'removesection' => '1', 'sectionid' => $section->id));
        $image = $this->pix_icon('t/delete', $title);
        return $this->action_link($url, $image, null, array(
                'class' => 'cm-edit-action editing_delete', 'data-action' => 'deletesection'));
    }

    
    public function questions_in_section(structure $structure, $section,
            $contexts, $pagevars, $pageurl) {

        $output = '';
        foreach ($structure->get_slots_in_section($section->id) as $slot) {
            $output .= $this->question_row($structure, $slot, $contexts, $pagevars, $pageurl);
        }
        return html_writer::tag('ul', $output, array('class' => 'section img-text'));
    }

    
    public function question_row(structure $structure, $slot, $contexts, $pagevars, $pageurl) {
        $output = '';

        $output .= $this->page_row($structure, $slot, $contexts, $pagevars, $pageurl);

                $joinhtml = '';
        if ($structure->can_be_edited() && !$structure->is_last_slot_in_quiz($slot) &&
                                            !$structure->is_last_slot_in_section($slot)) {
            $joinhtml = $this->page_split_join_button($structure, $slot);
        }
                $questionhtml = $this->question($structure, $slot, $pageurl);
        $qtype = $structure->get_question_type_for_slot($slot);
        $questionclasses = 'activity ' . $qtype . ' qtype_' . $qtype . ' slot';

        $output .= html_writer::tag('li', $questionhtml . $joinhtml,
                array('class' => $questionclasses, 'id' => 'slot-' . $structure->get_slot_id_for_slot($slot),
                        'data-canfinish' => $structure->can_finish_during_the_attempt($slot)));

        return $output;
    }

    
    public function page_row(structure $structure, $slot, $contexts, $pagevars, $pageurl) {
        $output = '';

        $pagenumber = $structure->get_page_number_for_slot($slot);

                $page = $this->heading(get_string('page') . ' ' . $pagenumber, 4);

        if ($structure->is_first_slot_on_page($slot)) {
                        $addmenu = html_writer::tag('span', $this->add_menu_actions($structure,
                    $pagenumber, $pageurl, $contexts, $pagevars),
                    array('class' => 'add-menu-outer'));

            $addquestionform = $this->add_question_form($structure,
                    $pagenumber, $pageurl, $pagevars);

            $output .= html_writer::tag('li', $page . $addmenu . $addquestionform,
                    array('class' => 'pagenumber activity yui3-dd-drop page', 'id' => 'page-' . $pagenumber));
        }

        return $output;
    }

    
    public function add_menu_actions(structure $structure, $page, \moodle_url $pageurl,
            \question_edit_contexts $contexts, array $pagevars) {

        $actions = $this->edit_menu_actions($structure, $page, $pageurl, $pagevars);
        if (empty($actions)) {
            return '';
        }
        $menu = new \action_menu();
        $menu->set_alignment(\action_menu::TR, \action_menu::TR);
        $menu->set_constraint('.mod-quiz-edit-content');
        $trigger = html_writer::tag('span', get_string('add', 'quiz'), array('class' => 'add-menu'));
        $menu->set_menu_trigger($trigger);
                        $menu->set_nowrap_on_items(true);

                if (!$structure->can_be_edited()) {
            return $this->render($menu);
        }

        foreach ($actions as $action) {
            if ($action instanceof \action_menu_link) {
                $action->add_class('add-menu');
            }
            $menu->add($action);
        }
        $menu->attributes['class'] .= ' page-add-actions commands';

                $menu->prioritise = true;

        return $this->render($menu);
    }

    
    public function edit_menu_actions(structure $structure, $page,
            \moodle_url $pageurl, array $pagevars) {
        $questioncategoryid = question_get_category_id_from_pagevars($pagevars);
        static $str;
        if (!isset($str)) {
            $str = get_strings(array('addasection', 'addaquestion', 'addarandomquestion',
                    'addarandomselectedquestion', 'questionbank'), 'quiz');
        }

                $actions = array();

                                $params = array('cmid' => $structure->get_cmid(), 'addsectionatpage' => $page);

        $actions['addasection'] = new \action_menu_link_secondary(
                new \moodle_url($pageurl, $params),
                new \pix_icon('t/add', $str->addasection, 'moodle', array('class' => 'iconsmall', 'title' => '')),
                $str->addasection, array('class' => 'cm-edit-action addasection', 'data-action' => 'addasection')
        );

                $returnurl = new \moodle_url($pageurl, array('addonpage' => $page));
        $params = array('returnurl' => $returnurl->out_as_local_url(false),
                'cmid' => $structure->get_cmid(), 'category' => $questioncategoryid,
                'addonpage' => $page, 'appendqnumstring' => 'addquestion');

        $actions['addaquestion'] = new \action_menu_link_secondary(
            new \moodle_url('/question/addquestion.php', $params),
            new \pix_icon('t/add', $str->addaquestion, 'moodle', array('class' => 'iconsmall', 'title' => '')),
            $str->addaquestion, array('class' => 'cm-edit-action addquestion', 'data-action' => 'addquestion')
        );

                $icon = new \pix_icon('t/add', $str->questionbank, 'moodle', array('class' => 'iconsmall', 'title' => ''));
        if ($page) {
            $title = get_string('addquestionfrombanktopage', 'quiz', $page);
        } else {
            $title = get_string('addquestionfrombankatend', 'quiz');
        }
        $attributes = array('class' => 'cm-edit-action questionbank',
                'data-header' => $title, 'data-action' => 'questionbank', 'data-addonpage' => $page);
        $actions['questionbank'] = new \action_menu_link_secondary($pageurl, $icon, $str->questionbank, $attributes);

                $returnurl = new \moodle_url('/mod/quiz/edit.php', array('cmid' => $structure->get_cmid(), 'data-addonpage' => $page));
        $params = array('returnurl' => $returnurl, 'cmid' => $structure->get_cmid(), 'appendqnumstring' => 'addarandomquestion');
        $url = new \moodle_url('/mod/quiz/addrandom.php', $params);
        $icon = new \pix_icon('t/add', $str->addarandomquestion, 'moodle', array('class' => 'iconsmall', 'title' => ''));
        $attributes = array('class' => 'cm-edit-action addarandomquestion', 'data-action' => 'addarandomquestion');
        if ($page) {
            $title = get_string('addrandomquestiontopage', 'quiz', $page);
        } else {
            $title = get_string('addrandomquestionatend', 'quiz');
        }
        $attributes = array_merge(array('data-header' => $title, 'data-addonpage' => $page), $attributes);
        $actions['addarandomquestion'] = new \action_menu_link_secondary($url, $icon, $str->addarandomquestion, $attributes);

        return $actions;
    }

    
    protected function add_question_form(structure $structure, $page, \moodle_url $pageurl, array $pagevars) {

        $questioncategoryid = question_get_category_id_from_pagevars($pagevars);

        $output = html_writer::tag('input', null,
                array('type' => 'hidden', 'name' => 'returnurl',
                        'value' => $pageurl->out_as_local_url(false, array('addonpage' => $page))));
        $output .= html_writer::tag('input', null,
                array('type' => 'hidden', 'name' => 'cmid', 'value' => $structure->get_cmid()));
        $output .= html_writer::tag('input', null,
                array('type' => 'hidden', 'name' => 'appendqnumstring', 'value' => 'addquestion'));
        $output .= html_writer::tag('input', null,
                array('type' => 'hidden', 'name' => 'category', 'value' => $questioncategoryid));

        return html_writer::tag('form', html_writer::div($output),
                array('class' => 'addnewquestion', 'method' => 'post',
                        'action' => new \moodle_url('/question/addquestion.php')));
    }

    
    public function question(structure $structure, $slot, \moodle_url $pageurl) {
        $output = '';
        $output .= html_writer::start_tag('div');

        if ($structure->can_be_edited()) {
            $output .= $this->question_move_icon($structure, $slot);
        }

        $output .= html_writer::start_div('mod-indent-outer');
        $output .= $this->question_number($structure->get_displayed_number_for_slot($slot));

                $output .= html_writer::div('', 'mod-indent');

                if ($structure->get_question_type_for_slot($slot) == 'random') {
            $questionname = $this->random_question($structure, $slot, $pageurl);
        } else {
            $questionname = $this->question_name($structure, $slot, $pageurl);
        }

                $output .= html_writer::start_div('activityinstance');
        $output .= $questionname;

                $output .= html_writer::end_tag('div'); 
                $questionicons = '';
        $questionicons .= $this->question_preview_icon($structure->get_quiz(), $structure->get_question_in_slot($slot));
        if ($structure->can_be_edited()) {
            $questionicons .= $this->question_remove_icon($structure, $slot, $pageurl);
        }
        $questionicons .= $this->marked_out_of_field($structure, $slot);
        $output .= html_writer::span($questionicons, 'actions');         if ($structure->can_be_edited()) {
            $output .= $this->question_dependency_icon($structure, $slot);
        }

                $output .= html_writer::end_tag('div');
        $output .= html_writer::end_tag('div');

        return $output;
    }

    
    public function question_move_icon(structure $structure, $slot) {
        return html_writer::link(new \moodle_url('#'),
            $this->pix_icon('i/dragdrop', get_string('move'), 'moodle', array('class' => 'iconsmall', 'title' => '')),
            array('class' => 'editing_move', 'data-action' => 'move')
        );
    }

    
    public function question_number($number) {
        if (is_numeric($number)) {
            $number = html_writer::span(get_string('question'), 'accesshide') . ' ' . $number;
        }
        return html_writer::tag('span', $number, array('class' => 'slotnumber'));
    }

    
    public function question_preview_icon($quiz, $question, $label = null, $variant = null) {
        $url = quiz_question_preview_url($quiz, $question, $variant);

                $strpreviewlabel = '';
        if ($label) {
            $strpreviewlabel = ' ' . get_string('preview', 'quiz');
        }

                $strpreviewquestion = get_string('previewquestion', 'quiz');
        $image = $this->pix_icon('t/preview', $strpreviewquestion);

        $action = new \popup_action('click', $url, 'questionpreview',
                                        question_preview_popup_params());

        return $this->action_link($url, $image . $strpreviewlabel, $action,
                array('title' => $strpreviewquestion, 'class' => 'preview'));
    }

    
    public function question_remove_icon(structure $structure, $slot, $pageurl) {
        $url = new \moodle_url($pageurl, array('sesskey' => sesskey(), 'remove' => $slot));
        $strdelete = get_string('delete');

        $image = $this->pix_icon('t/delete', $strdelete);

        return $this->action_link($url, $image, null, array('title' => $strdelete,
                    'class' => 'cm-edit-action editing_delete', 'data-action' => 'delete'));
    }

    
    public function page_split_join_button($structure, $slot) {
        $insertpagebreak = !$structure->is_last_slot_on_page($slot);
        $url = new \moodle_url('repaginate.php', array('quizid' => $structure->get_quizid(),
                'slot' => $slot, 'repag' => $insertpagebreak ? 2 : 1, 'sesskey' => sesskey()));

        if ($insertpagebreak) {
            $title = get_string('addpagebreak', 'quiz');
            $image = $this->pix_icon('e/insert_page_break', $title);
            $action = 'addpagebreak';
        } else {
            $title = get_string('removepagebreak', 'quiz');
            $image = $this->pix_icon('e/remove_page_break', $title);
            $action = 'removepagebreak';
        }

                $disabled = null;
        if (!$structure->can_be_edited()) {
            $disabled = 'disabled';
        }
        return html_writer::span($this->action_link($url, $image, null, array('title' => $title,
                    'class' => 'page_split_join cm-edit-action', 'disabled' => $disabled, 'data-action' => $action)),
                'page_split_join_wrapper');
    }

    
    public function question_dependency_icon($structure, $slot) {
        $a = array(
            'thisq' => $structure->get_displayed_number_for_slot($slot),
            'previousq' => $structure->get_displayed_number_for_slot(max($slot - 1, 1)),
        );
        if ($structure->is_question_dependent_on_previous_slot($slot)) {
            $title = get_string('questiondependencyremove', 'quiz', $a);
            $image = $this->pix_icon('t/locked', get_string('questiondependsonprevious', 'quiz'),
                    'moodle', array('title' => ''));
            $action = 'removedependency';
        } else {
            $title = get_string('questiondependencyadd', 'quiz', $a);
            $image = $this->pix_icon('t/unlocked', get_string('questiondependencyfree', 'quiz'),
                    'moodle', array('title' => ''));
            $action = 'adddependency';
        }

                $disabled = null;
        if (!$structure->can_be_edited()) {
            $disabled = 'disabled';
        }
        $extraclass = '';
        if (!$structure->can_question_depend_on_previous_slot($slot)) {
            $extraclass = ' question_dependency_cannot_depend';
        }
        return html_writer::span($this->action_link('#', $image, null, array('title' => $title,
                'class' => 'cm-edit-action', 'disabled' => $disabled, 'data-action' => $action)),
                'question_dependency_wrapper' . $extraclass);
    }

    
    public function question_name(structure $structure, $slot, $pageurl) {
        $output = '';

        $question = $structure->get_question_in_slot($slot);
        $editurl = new \moodle_url('/question/question.php', array(
                'returnurl' => $pageurl->out_as_local_url(),
                'cmid' => $structure->get_cmid(), 'id' => $question->id));

        $instancename = quiz_question_tostring($question);

        $qtype = \question_bank::get_qtype($question->qtype, false);
        $namestr = $qtype->local_name();

        $icon = $this->pix_icon('icon', $namestr, $qtype->plugin_name(), array('title' => $namestr,
                'class' => 'icon activityicon', 'alt' => ' ', 'role' => 'presentation'));

        $editicon = $this->pix_icon('t/edit', '', 'moodle', array('title' => ''));

                $title = shorten_text(format_string($question->name), 100);

                $activitylink = $icon . html_writer::tag('span', $editicon . $instancename, array('class' => 'instancename'));
        $output .= html_writer::link($editurl, $activitylink,
                array('title' => get_string('editquestion', 'quiz').' '.$title));

        return $output;
    }

    
    public function random_question(structure $structure, $slot, $pageurl) {

        $question = $structure->get_question_in_slot($slot);
        $editurl = new \moodle_url('/question/question.php', array(
                'returnurl' => $pageurl->out_as_local_url(),
                'cmid' => $structure->get_cmid(), 'id' => $question->id));

        $temp = clone($question);
        $temp->questiontext = '';
        $instancename = quiz_question_tostring($temp);

        $configuretitle = get_string('configurerandomquestion', 'quiz');
        $qtype = \question_bank::get_qtype($question->qtype, false);
        $namestr = $qtype->local_name();
        $icon = $this->pix_icon('icon', $namestr, $qtype->plugin_name(), array('title' => $namestr,
                'class' => 'icon activityicon', 'alt' => ' ', 'role' => 'presentation'));

        $editicon = $this->pix_icon('t/edit', $configuretitle, 'moodle', array('title' => ''));

                        $qbankurl = new \moodle_url('/question/edit.php', array(
                'cmid' => $structure->get_cmid(),
                'cat' => $question->category . ',' . $question->contextid,
                'recurse' => !empty($question->questiontext)));
        $qbanklink = ' ' . \html_writer::link($qbankurl,
                get_string('seequestions', 'quiz'), array('class' => 'mod_quiz_random_qbank_link'));

        return html_writer::link($editurl, $icon . $editicon, array('title' => $configuretitle)) .
                ' ' . $instancename . ' ' . $qbanklink;
    }

    
    public function marked_out_of_field(structure $structure, $slot) {
        if (!$structure->is_real_question($slot)) {
            $output = html_writer::span('',
                    'instancemaxmark decimalplaces_' . $structure->get_decimal_places_for_question_marks());

            $output .= html_writer::span(
                    $this->pix_icon('spacer', '', 'moodle', array('class' => 'editicon visibleifjs', 'title' => '')),
                    'editing_maxmark');
            return html_writer::span($output, 'instancemaxmarkcontainer infoitem');
        }

        $output = html_writer::span($structure->formatted_question_grade($slot),
                'instancemaxmark decimalplaces_' . $structure->get_decimal_places_for_question_marks(),
                array('title' => get_string('maxmark', 'quiz')));

        $output .= html_writer::span(
            html_writer::link(
                new \moodle_url('#'),
                $this->pix_icon('t/editstring', '', 'moodle', array('class' => 'editicon visibleifjs', 'title' => '')),
                array(
                    'class' => 'editing_maxmark',
                    'data-action' => 'editmaxmark',
                    'title' => get_string('editmaxmark', 'quiz'),
                )
            )
        );
        return html_writer::span($output, 'instancemaxmarkcontainer');
    }

    
    public function question_chooser() {
        $container = html_writer::div(print_choose_qtype_to_add_form(array(), null, false), '',
                array('id' => 'qtypechoicecontainer'));
        return html_writer::div($container, 'createnewquestion');
    }

    
    public function question_bank_loading() {
        return html_writer::div(html_writer::empty_tag('img',
                array('alt' => 'loading', 'class' => 'loading-icon', 'src' => $this->pix_url('i/loading'))),
                'questionbankloading');
    }

    
    protected function random_question_form(\moodle_url $thispageurl, \question_edit_contexts $contexts, array $pagevars) {

        if (!$contexts->have_cap('moodle/question:useall')) {
            return '';
        }
        $randomform = new \quiz_add_random_form(new \moodle_url('/mod/quiz/addrandom.php'),
                                 array('contexts' => $contexts, 'cat' => $pagevars['cat']));
        $randomform->set_data(array(
                'category' => $pagevars['cat'],
                'returnurl' => $thispageurl->out_as_local_url(true),
                'randomnumber' => 1,
                'cmid' => $thispageurl->param('cmid'),
        ));
        return html_writer::div($randomform->render(), 'randomquestionformforpopup');
    }

    
    protected function initialise_editing_javascript(structure $structure,
            \question_edit_contexts $contexts, array $pagevars, \moodle_url $pageurl) {

        $config = new \stdClass();
        $config->resourceurl = '/mod/quiz/edit_rest.php';
        $config->sectionurl = '/mod/quiz/edit_rest.php';
        $config->pageparams = array();
        $config->questiondecimalpoints = $structure->get_decimal_places_for_question_marks();
        $config->pagehtml = $this->new_page_template($structure, $contexts, $pagevars, $pageurl);
        $config->addpageiconhtml = $this->add_page_icon_template($structure);

        $this->page->requires->yui_module('moodle-mod_quiz-toolboxes',
                'M.mod_quiz.init_resource_toolbox',
                array(array(
                        'courseid' => $structure->get_courseid(),
                        'quizid' => $structure->get_quizid(),
                        'ajaxurl' => $config->resourceurl,
                        'config' => $config,
                ))
        );
        unset($config->pagehtml);
        unset($config->addpageiconhtml);

        $this->page->requires->yui_module('moodle-mod_quiz-toolboxes',
                'M.mod_quiz.init_section_toolbox',
                array(array(
                        'courseid' => $structure,
                        'quizid' => $structure->get_quizid(),
                        'ajaxurl' => $config->sectionurl,
                        'config' => $config,
                ))
        );

        $this->page->requires->yui_module('moodle-mod_quiz-dragdrop', 'M.mod_quiz.init_section_dragdrop',
                array(array(
                        'courseid' => $structure,
                        'quizid' => $structure->get_quizid(),
                        'ajaxurl' => $config->sectionurl,
                        'config' => $config,
                )), null, true);

        $this->page->requires->yui_module('moodle-mod_quiz-dragdrop', 'M.mod_quiz.init_resource_dragdrop',
                array(array(
                        'courseid' => $structure,
                        'quizid' => $structure->get_quizid(),
                        'ajaxurl' => $config->resourceurl,
                        'config' => $config,
                )), null, true);

                $this->page->requires->strings_for_js(array(
                'clicktohideshow',
                'deletechecktype',
                'deletechecktypename',
                'edittitle',
                'edittitleinstructions',
                'emptydragdropregion',
                'hide',
                'markedthistopic',
                'markthistopic',
                'move',
                'movecontent',
                'moveleft',
                'movesection',
                'page',
                'question',
                'selectall',
                'show',
                'tocontent',
        ), 'moodle');

        $this->page->requires->strings_for_js(array(
                'addpagebreak',
                'confirmremovesectionheading',
                'confirmremovequestion',
                'dragtoafter',
                'dragtostart',
                'numquestionsx',
                'sectionheadingedit',
                'sectionheadingremove',
                'removepagebreak',
                'questiondependencyadd',
                'questiondependencyfree',
                'questiondependencyremove',
                'questiondependsonprevious',
        ), 'quiz');

        foreach (\question_bank::get_all_qtypes() as $qtype => $notused) {
            $this->page->requires->string_for_js('pluginname', 'qtype_' . $qtype);
        }

        return true;
    }

    
    protected function new_page_template(structure $structure,
            \question_edit_contexts $contexts, array $pagevars, \moodle_url $pageurl) {
        if (!$structure->has_questions()) {
            return '';
        }

        $pagehtml = $this->page_row($structure, 1, $contexts, $pagevars, $pageurl);

                $pagenumber = $structure->get_page_number_for_slot(1);
        $strcontexts = array();
        $strcontexts[] = 'page-';
        $strcontexts[] = get_string('page') . ' ';
        $strcontexts[] = 'addonpage%3D';
        $strcontexts[] = 'addonpage=';
        $strcontexts[] = 'addonpage="';
        $strcontexts[] = get_string('addquestionfrombanktopage', 'quiz', '');
        $strcontexts[] = 'data-addonpage%3D';
        $strcontexts[] = 'action-menu-';

        foreach ($strcontexts as $strcontext) {
            $pagehtml = str_replace($strcontext . $pagenumber, $strcontext . '%%PAGENUMBER%%', $pagehtml);
        }

        return $pagehtml;
    }

    
    protected function add_page_icon_template(structure $structure) {

        if (!$structure->has_questions()) {
            return '';
        }

        $html = $this->page_split_join_button($structure, 1);
        return str_replace('&amp;slot=1&amp;', '&amp;slot=%%SLOT%%&amp;', $html);
    }

    
    public function question_bank_contents(\mod_quiz\question\bank\custom_view $questionbank, array $pagevars) {

        $qbank = $questionbank->render('editq', $pagevars['qpage'], $pagevars['qperpage'],
                $pagevars['cat'], $pagevars['recurse'], $pagevars['showhidden'], $pagevars['qbshowtext']);
        return html_writer::div(html_writer::div($qbank, 'bd'), 'questionbankformforpopup');
    }
}
