<?php







class backup_choice_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

                $userinfo = $this->get_setting_value('userinfo');

                $choice = new backup_nested_element('choice', array('id'), array(
            'name', 'intro', 'introformat', 'publish',
            'showresults', 'display', 'allowupdate', 'allowmultiple', 'showunanswered',
            'limitanswers', 'timeopen', 'timeclose', 'timemodified',
            'completionsubmit', 'showpreview', 'includeinactive'));

        $options = new backup_nested_element('options');

        $option = new backup_nested_element('option', array('id'), array(
            'text', 'maxanswers', 'timemodified'));

        $answers = new backup_nested_element('answers');

        $answer = new backup_nested_element('answer', array('id'), array(
            'userid', 'optionid', 'timemodified'));

                $choice->add_child($options);
        $options->add_child($option);

        $choice->add_child($answers);
        $answers->add_child($answer);

                $choice->set_source_table('choice', array('id' => backup::VAR_ACTIVITYID));

        $option->set_source_table('choice_options', array('choiceid' => backup::VAR_PARENTID), 'id ASC');

                if ($userinfo) {
            $answer->set_source_table('choice_answers', array('choiceid' => '../../id'));
        }

                $answer->annotate_ids('user', 'userid');

                $choice->annotate_files('mod_choice', 'intro', null); 
                return $this->prepare_activity_structure($choice);
    }
}
