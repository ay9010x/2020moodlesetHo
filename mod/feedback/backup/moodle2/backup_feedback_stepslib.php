<?php






class backup_feedback_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

                $userinfo = $this->get_setting_value('userinfo');

                $feedback = new backup_nested_element('feedback', array('id'), array(
                                                'name',
                                                'intro',
                                                'introformat',
                                                'anonymous',
                                                'email_notification',
                                                'multiple_submit',
                                                'autonumbering',
                                                'site_after_submit',
                                                'page_after_submit',
                                                'page_after_submitformat',
                                                'publish_stats',
                                                'timeopen',
                                                'timeclose',
                                                'timemodified',
                                                'completionsubmit'));

        $completeds = new backup_nested_element('completeds');

        $completed = new backup_nested_element('completed', array('id'), array(
                                                'userid',
                                                'timemodified',
                                                'random_response',
                                                'anonymous_response',
                                                'courseid'));

        $items = new backup_nested_element('items');

        $item = new backup_nested_element('item', array('id'), array(
                                                'template',
                                                'name',
                                                'label',
                                                'presentation',
                                                'typ',
                                                'hasvalue',
                                                'position',
                                                'required',
                                                'dependitem',
                                                'dependvalue',
                                                'options'));

        $values = new backup_nested_element('values');

        $value = new backup_nested_element('value', array('id'), array(
                                                'item',
                                                'template',
                                                'completed',
                                                'value',
                                                'course_id'));

                $feedback->add_child($items);
        $items->add_child($item);

        $feedback->add_child($completeds);
        $completeds->add_child($completed);

        $completed->add_child($values);
        $values->add_child($value);

                $feedback->set_source_table('feedback', array('id' => backup::VAR_ACTIVITYID));

        $item->set_source_table('feedback_item', array('feedback' => backup::VAR_PARENTID));

                if ($userinfo) {
            $completed->set_source_sql('
                SELECT *
                  FROM {feedback_completed}
                 WHERE feedback = ?',
                array(backup::VAR_PARENTID));

            $value->set_source_table('feedback_value', array('completed' => backup::VAR_PARENTID));
        }

        
        $completed->annotate_ids('user', 'userid');

        
        $feedback->annotate_files('mod_feedback', 'intro', null);         $feedback->annotate_files('mod_feedback', 'page_after_submit', null); 
        $item->annotate_files('mod_feedback', 'item', 'id');

                return $this->prepare_activity_structure($feedback);
    }

}
