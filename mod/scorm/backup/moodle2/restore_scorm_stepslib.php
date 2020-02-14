<?php






class restore_scorm_activity_structure_step extends restore_activity_structure_step {

    protected function define_structure() {

        $paths = array();
        $userinfo = $this->get_setting_value('userinfo');

        $paths[] = new restore_path_element('scorm', '/activity/scorm');
        $paths[] = new restore_path_element('scorm_sco', '/activity/scorm/scoes/sco');
        $paths[] = new restore_path_element('scorm_sco_data', '/activity/scorm/scoes/sco/sco_datas/sco_data');
        $paths[] = new restore_path_element('scorm_seq_objective', '/activity/scorm/scoes/sco/seq_objectives/seq_objective');
        $paths[] = new restore_path_element('scorm_seq_rolluprule', '/activity/scorm/scoes/sco/seq_rolluprules/seq_rolluprule');
        $paths[] = new restore_path_element('scorm_seq_rolluprulecond', '/activity/scorm/scoes/sco/seq_rollupruleconds/seq_rolluprulecond');
        $paths[] = new restore_path_element('scorm_seq_rulecond', '/activity/scorm/scoes/sco/seq_ruleconds/seq_rulecond');
        $paths[] = new restore_path_element('scorm_seq_rulecond_data', '/activity/scorm/scoes/sco/seq_rulecond_datas/seq_rulecond_data');

        $paths[] = new restore_path_element('scorm_seq_mapinfo', '/activity/scorm/scoes/sco/seq_objectives/seq_objective/seq_mapinfos/seq_mapinfo');
        if ($userinfo) {
            $paths[] = new restore_path_element('scorm_sco_track', '/activity/scorm/scoes/sco/sco_tracks/sco_track');
        }

                return $this->prepare_activity_structure($paths);
    }

    protected function process_scorm($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;

        $data->course = $this->get_courseid();

        $data->timeopen = $this->apply_date_offset($data->timeopen);
        $data->timeclose = $this->apply_date_offset($data->timeclose);
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        if (!isset($data->displayactivityname)) {
            $data->displayactivityname = true;
        }

                $newitemid = $DB->insert_record('scorm', $data);
                $this->apply_activity_instance($newitemid);
    }

    protected function process_scorm_sco($data) {
        global $DB;

        $data = (object)$data;

        $oldid = $data->id;
        $data->scorm = $this->get_new_parentid('scorm');

        $newitemid = $DB->insert_record('scorm_scoes', $data);
        $this->set_mapping('scorm_sco', $oldid, $newitemid);
    }

    protected function process_scorm_sco_data($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->scoid = $this->get_new_parentid('scorm_sco');

        $newitemid = $DB->insert_record('scorm_scoes_data', $data);
                    }

    protected function process_scorm_seq_objective($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->scoid = $this->get_new_parentid('scorm_sco');

        $newitemid = $DB->insert_record('scorm_seq_objective', $data);
        $this->set_mapping('scorm_seq_objective', $oldid, $newitemid);
    }

    protected function process_scorm_seq_rolluprule($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->scoid = $this->get_new_parentid('scorm_sco');

        $newitemid = $DB->insert_record('scorm_seq_rolluprule', $data);
        $this->set_mapping('scorm_seq_rolluprule', $oldid, $newitemid);
    }

    protected function process_scorm_seq_rolluprulecond($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->scoid = $this->get_new_parentid('scorm_sco');
        $data->ruleconditions = $this->get_new_parentid('scorm_seq_rolluprule');

        $newitemid = $DB->insert_record('scorm_seq_rolluprulecond', $data);
                    }

    protected function process_scorm_seq_rulecond($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->scoid = $this->get_new_parentid('scorm_sco');

        $newitemid = $DB->insert_record('scorm_seq_ruleconds', $data);
        $this->set_mapping('scorm_seq_ruleconds', $oldid, $newitemid);
    }

    protected function process_scorm_seq_rulecond_data($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->scoid = $this->get_new_parentid('scorm_sco');
        $data->ruleconditions = $this->get_new_parentid('scorm_seq_ruleconds');

        $newitemid = $DB->insert_record('scorm_seq_rulecond', $data);
                    }



    protected function process_scorm_seq_mapinfo($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->scoid = $this->get_new_parentid('scorm_sco');
        $data->objectiveid = $this->get_new_parentid('scorm_seq_objective');
        $newitemid = $DB->insert_record('scorm_scoes_data', $data);
                    }

    protected function process_scorm_sco_track($data) {
        global $DB;

        $data = (object)$data;
        $oldid = $data->id;
        $data->scormid = $this->get_new_parentid('scorm');
        $data->scoid = $this->get_new_parentid('scorm_sco');
        $data->userid = $this->get_mappingid('user', $data->userid);
        $data->timemodified = $this->apply_date_offset($data->timemodified);

        $newitemid = $DB->insert_record('scorm_scoes_track', $data);
                    }

    protected function after_execute() {
        global $DB;

                $this->add_related_files('mod_scorm', 'intro', null);
        $this->add_related_files('mod_scorm', 'content', null);
        $this->add_related_files('mod_scorm', 'package', null);

                $scormid = $this->get_new_parentid('scorm');
        $scorm = $DB->get_record('scorm', array('id' => $scormid));
        $scorm->launch = $this->get_mappingid('scorm_sco', $scorm->launch, '');

        if (!empty($scorm->launch)) {
                        $scolaunch = $DB->get_field('scorm_scoes', 'launch', array('id' => $scorm->launch));
            if (empty($scolaunch)) {
                                $scorm->launch = '';
            }
        }

        if (empty($scorm->launch)) {
                        $sqlselect = 'scorm = ? AND '.$DB->sql_isnotempty('scorm_scoes', 'launch', false, true);
                        $scoes = $DB->get_records_select('scorm_scoes', $sqlselect, array($scormid), 'sortorder', 'id', 0, 1);
            if (!empty($scoes)) {
                $sco = reset($scoes);                 $scorm->launch = $sco->id;
            }
        }
        if (!empty($scorm->launch)) {
            $DB->update_record('scorm', $scorm);
        }
    }
}
