<?php







class backup_data_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

                $userinfo = $this->get_setting_value('userinfo');

                $data = new backup_nested_element('data', array('id'), array(
            'name', 'intro', 'introformat', 'comments',
            'timeavailablefrom', 'timeavailableto', 'timeviewfrom', 'timeviewto',
            'requiredentries', 'requiredentriestoview', 'maxentries', 'rssarticles',
            'singletemplate', 'listtemplate', 'listtemplateheader', 'listtemplatefooter',
            'addtemplate', 'rsstemplate', 'rsstitletemplate', 'csstemplate',
            'jstemplate', 'asearchtemplate', 'approval', 'manageapproved', 'scale',
            'assessed', 'assesstimestart', 'assesstimefinish', 'defaultsort',
            'defaultsortdir', 'editany', 'notification', 'timemodified'));

        $fields = new backup_nested_element('fields');

        $field = new backup_nested_element('field', array('id'), array(
            'type', 'name', 'description', 'required', 'param1', 'param2',
            'param3', 'param4', 'param5', 'param6',
            'param7', 'param8', 'param9', 'param10'));

        $records = new backup_nested_element('records');

        $record = new backup_nested_element('record', array('id'), array(
            'userid', 'groupid', 'timecreated', 'timemodified',
            'approved'));

        $contents = new backup_nested_element('contents');

        $content = new backup_nested_element('content', array('id'), array(
            'fieldid', 'content', 'content1', 'content2',
            'content3', 'content4'));

        $ratings = new backup_nested_element('ratings');

        $rating = new backup_nested_element('rating', array('id'), array(
            'component', 'ratingarea', 'scaleid', 'value', 'userid', 'timecreated', 'timemodified'));

                $data->add_child($fields);
        $fields->add_child($field);

        $data->add_child($records);
        $records->add_child($record);

        $record->add_child($contents);
        $contents->add_child($content);

        $record->add_child($ratings);
        $ratings->add_child($rating);

                $data->set_source_table('data', array('id' => backup::VAR_ACTIVITYID));

        $field->set_source_sql('
            SELECT *
              FROM {data_fields}
             WHERE dataid = ?',
            array(backup::VAR_PARENTID));

                if ($userinfo) {
            $record->set_source_table('data_records', array('dataid' => backup::VAR_PARENTID));

            $content->set_source_table('data_content', array('recordid' => backup::VAR_PARENTID));

            $rating->set_source_table('rating', array('contextid'  => backup::VAR_CONTEXTID,
                                                      'itemid'     => backup::VAR_PARENTID,
                                                      'component'  => backup_helper::is_sqlparam('mod_data'),
                                                      'ratingarea' => backup_helper::is_sqlparam('entry')));
            $rating->set_source_alias('rating', 'value');
        }

                $data->annotate_ids('scale', 'scale');

        $record->annotate_ids('user', 'userid');
        $record->annotate_ids('group', 'groupid');

        $rating->annotate_ids('scale', 'scaleid');
        $rating->annotate_ids('user', 'userid');

                $data->annotate_files('mod_data', 'intro', null);         $content->annotate_files('mod_data', 'content', 'id'); 
                return $this->prepare_activity_structure($data);
    }
}
