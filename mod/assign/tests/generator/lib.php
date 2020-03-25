<?php

defined('MOODLE_INTERNAL') || die();


class mod_assign_generator extends testing_module_generator {

    public function create_instance($record = null, array $options = null) {
        $record = (object)(array)$record;

        $defaultsettings = array(
            'alwaysshowdescription'             => 1,
            'submissiondrafts'                  => 1,
            'requiresubmissionstatement'        => 0,
            'sendnotifications'                 => 0,
            'sendstudentnotifications'          => 1,
            'sendlatenotifications'             => 0,
            'duedate'                           => 0,
            'allowsubmissionsfromdate'          => 0,
            'grade'                             => 100,
            'cutoffdate'                        => 0,
            'teamsubmission'                    => 0,
            'requireallteammemberssubmit'       => 0,
            'teamsubmissiongroupingid'          => 0,
            'blindmarking'                      => 0,
            'attemptreopenmethod'               => 'none',
            'maxattempts'                       => -1,
            'markingworkflow'                   => 0,
            'markingallocation'                 => 0,
        );

        foreach ($defaultsettings as $name => $value) {
            if (!isset($record->{$name})) {
                $record->{$name} = $value;
            }
        }

        return parent::create_instance($record, (array)$options);
    }
}
