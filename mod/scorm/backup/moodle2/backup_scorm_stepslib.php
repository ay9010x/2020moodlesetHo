<?php






class backup_scorm_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

                $userinfo = $this->get_setting_value('userinfo');

                $scorm = new backup_nested_element('scorm', array('id'), array(
            'name', 'scormtype', 'reference', 'intro',
            'introformat', 'version', 'maxgrade', 'grademethod',
            'whatgrade', 'maxattempt', 'forcecompleted', 'forcenewattempt',
            'lastattemptlock', 'masteryoverride', 'displayattemptstatus', 'displaycoursestructure', 'updatefreq',
            'sha1hash', 'md5hash', 'revision', 'launch',
            'skipview', 'hidebrowse', 'hidetoc', 'nav', 'navpositionleft', 'navpositiontop',
            'auto', 'popup', 'options', 'width',
            'height', 'timeopen', 'timeclose', 'timemodified',
            'completionstatusrequired', 'completionscorerequired',
            'displayactivityname'));

        $scoes = new backup_nested_element('scoes');

        $sco = new backup_nested_element('sco', array('id'), array(
            'manifest', 'organization', 'parent', 'identifier',
            'launch', 'scormtype', 'title', 'sortorder'));

        $scodatas = new backup_nested_element('sco_datas');

        $scodata = new backup_nested_element('sco_data', array('id'), array(
            'name', 'value'));

        $seqruleconds = new backup_nested_element('seq_ruleconds');

        $seqrulecond = new backup_nested_element('seq_rulecond', array('id'), array(
            'conditioncombination', 'ruletype', 'action'));

        $seqrulecondsdatas = new backup_nested_element('seq_rulecond_datas');

        $seqrulecondsdata = new backup_nested_element('seq_rulecond_data', array('id'), array(
            'refrencedobjective', 'measurethreshold', 'operator', 'cond'));

        $seqrolluprules = new backup_nested_element('seq_rolluprules');

        $seqrolluprule = new backup_nested_element('seq_rolluprule', array('id'), array(
            'childactivityset', 'minimumcount', 'minimumpercent', 'conditioncombination',
            'action'));

        $seqrollupruleconds = new backup_nested_element('seq_rollupruleconds');

        $seqrolluprulecond = new backup_nested_element('seq_rolluprulecond', array('id'), array(
            'cond', 'operator'));

        $seqobjectives = new backup_nested_element('seq_objectives');

        $seqobjective = new backup_nested_element('seq_objective', array('id'), array(
            'primaryobj', 'objectiveid', 'satisfiedbymeasure', 'minnormalizedmeasure'));

        $seqmapinfos = new backup_nested_element('seq_mapinfos');

        $seqmapinfo = new backup_nested_element('seq_mapinfo', array('id'), array(
            'targetobjectiveid', 'readsatisfiedstatus', 'readnormalizedmeasure', 'writesatisfiedstatus',
            'writenormalizedmeasure'));

        $scotracks = new backup_nested_element('sco_tracks');

        $scotrack = new backup_nested_element('sco_track', array('id'), array(
            'userid', 'attempt', 'element', 'value',
            'timemodified'));

                $scorm->add_child($scoes);
        $scoes->add_child($sco);

        $sco->add_child($scodatas);
        $scodatas->add_child($scodata);

        $sco->add_child($seqruleconds);
        $seqruleconds->add_child($seqrulecond);

        $seqrulecond->add_child($seqrulecondsdatas);
        $seqrulecondsdatas->add_child($seqrulecondsdata);

        $sco->add_child($seqrolluprules);
        $seqrolluprules->add_child($seqrolluprule);

        $seqrolluprule->add_child($seqrollupruleconds);
        $seqrollupruleconds->add_child($seqrolluprulecond);

        $sco->add_child($seqobjectives);
        $seqobjectives->add_child($seqobjective);

        $seqobjective->add_child($seqmapinfos);
        $seqmapinfos->add_child($seqmapinfo);

        $sco->add_child($scotracks);
        $scotracks->add_child($scotrack);

                $scorm->set_source_table('scorm', array('id' => backup::VAR_ACTIVITYID));

                $sco->set_source_table('scorm_scoes', array('scorm' => backup::VAR_PARENTID), 'sortorder, id');
        $scodata->set_source_table('scorm_scoes_data', array('scoid' => backup::VAR_PARENTID), 'id ASC');
        $seqrulecond->set_source_table('scorm_seq_ruleconds', array('scoid' => backup::VAR_PARENTID), 'id ASC');
        $seqrulecondsdata->set_source_table('scorm_seq_rulecond', array('ruleconditionsid' => backup::VAR_PARENTID), 'id ASC');
        $seqrolluprule->set_source_table('scorm_seq_rolluprule', array('scoid' => backup::VAR_PARENTID), 'id ASC');
        $seqrolluprulecond->set_source_table('scorm_seq_rolluprulecond', array('rollupruleid' => backup::VAR_PARENTID), 'id ASC');
        $seqobjective->set_source_table('scorm_seq_objective', array('scoid' => backup::VAR_PARENTID), 'id ASC');
        $seqmapinfo->set_source_table('scorm_seq_mapinfo', array('objectiveid' => backup::VAR_PARENTID), 'id ASC');

                if ($userinfo) {
            $scotrack->set_source_table('scorm_scoes_track', array('scoid' => backup::VAR_PARENTID), 'id ASC');
        }

                $scotrack->annotate_ids('user', 'userid');

                $scorm->annotate_files('mod_scorm', 'intro', null);         $scorm->annotate_files('mod_scorm', 'content', null);         $scorm->annotate_files('mod_scorm', 'package', null); 
                return $this->prepare_activity_structure($scorm);
    }
}
