<?php



defined('MOODLE_INTERNAL') || die();


class mod_scorm_generator extends testing_module_generator {

    public function create_instance($record = null, array $options = null) {
        global $CFG, $USER;
        require_once($CFG->dirroot.'/mod/scorm/lib.php');
        require_once($CFG->dirroot.'/mod/scorm/locallib.php');
        $cfgscorm = get_config('scorm');

                $record = (array)$record + array(
            'scormtype' => SCORM_TYPE_LOCAL,
            'packagefile' => '',
            'packagefilepath' => $CFG->dirroot.'/mod/scorm/tests/packages/singlescobasic.zip',
            'packageurl' => '',
            'updatefreq' => SCORM_UPDATE_NEVER,
            'popup' => 0,
            'width' => $cfgscorm->framewidth,
            'height' => $cfgscorm->frameheight,
            'skipview' => $cfgscorm->skipview,
            'hidebrowse' => $cfgscorm->hidebrowse,
            'displaycoursestructure' => $cfgscorm->displaycoursestructure,
            'hidetoc' => $cfgscorm->hidetoc,
            'nav' => $cfgscorm->nav,
            'navpositionleft' => $cfgscorm->navpositionleft,
            'navpositiontop' => $cfgscorm->navpositiontop,
            'displayattemptstatus' => $cfgscorm->displayattemptstatus,
            'timeopen' => 0,
            'timeclose' => 0,
            'grademethod' => GRADESCOES,
            'maxgrade' => $cfgscorm->maxgrade,
            'maxattempt' => $cfgscorm->maxattempt,
            'whatgrade' => $cfgscorm->whatgrade,
            'forcenewattempt' => $cfgscorm->forcenewattempt,
            'lastattemptlock' => $cfgscorm->lastattemptlock,
            'forcecompleted' => $cfgscorm->forcecompleted,
            'masteryoverride' => $cfgscorm->masteryoverride,
            'auto' => $cfgscorm->auto,
            'displayactivityname' => $cfgscorm->displayactivityname
        );

                if (empty($record['packagefile']) && $record['scormtype'] === SCORM_TYPE_LOCAL) {
            if (!isloggedin() || isguestuser()) {
                throw new coding_exception('Scorm generator requires a current user');
            }
            if (!file_exists($record['packagefilepath'])) {
                throw new coding_exception("File {$record['packagefilepath']} does not exist");
            }
            $usercontext = context_user::instance($USER->id);

                        $record['packagefile'] = file_get_unused_draft_itemid();

                        $filerecord = array('component' => 'user', 'filearea' => 'draft',
                    'contextid' => $usercontext->id, 'itemid' => $record['packagefile'],
                    'filename' => basename($record['packagefilepath']), 'filepath' => '/');
            $fs = get_file_storage();
            $fs->create_file_from_pathname($filerecord, $record['packagefilepath']);
        }

        return parent::create_instance($record, (array)$options);
    }
}
