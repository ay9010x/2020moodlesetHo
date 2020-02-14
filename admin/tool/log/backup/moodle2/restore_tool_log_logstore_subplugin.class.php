<?php



defined('MOODLE_INTERNAL') || die();


abstract class restore_tool_log_logstore_subplugin extends restore_subplugin {

    
    protected function process_log($data) {
        $data = (object) $data;

                $contextid = $data->contextid;
        if (!$data->contextid = $this->get_mappingid('context', $contextid)) {
            $message = "Context id \"$contextid\" could not be mapped. Skipping log record.";
            $this->log($message, backup::LOG_DEBUG);
            return;
        }
        $context = context::instance_by_id($data->contextid, MUST_EXIST);
        $data->contextlevel = $context->contextlevel;
        $data->contextinstanceid = $context->instanceid;
        $data->courseid = $this->task->get_courseid();

                $userid = $data->userid;
        if (!$data->userid = $this->get_mappingid('user', $userid)) {
            $message = "User id \"$userid\" could not be mapped. Skipping log record.";
            $this->log($message, backup::LOG_DEBUG);
            return;
        }
        if (!empty($data->relateduserid)) {             $relateduserid = $data->relateduserid;
            if (!$data->relateduserid = $this->get_mappingid('user', $relateduserid)) {
                $message = "Related user id \"$relateduserid\" could not be mapped. Skipping log record.";
                $this->log($message, backup::LOG_DEBUG);
                return;
            }
        }
        if (!empty($data->realuserid)) {             $realuserid = $data->realuserid;
            if (!$data->realuserid = $this->get_mappingid('user', $realuserid)) {
                $message = "Real user id \"$realuserid\" could not be mapped. Skipping log record.";
                $this->log($message, backup::LOG_DEBUG);
                return;
            }
        }

                $data->timecreated = $this->apply_date_offset($data->timecreated);

                $data->other = unserialize(base64_decode($data->other));

                                        if (!empty($data->objectid)) {
                        $eventclass = $data->eventname;
            if (class_exists($eventclass)) {
                $mapping = $eventclass::get_objectid_mapping();
                if ($mapping) {
                                        if ((is_int($mapping) && $mapping === \core\event\base::NOT_MAPPED) ||
                        ($mapping['restore'] === \core\event\base::NOT_MAPPED)) {
                        $data->objectid = \core\event\base::NOT_MAPPED;
                    } else {
                        $data->objectid = $this->get_mappingid($mapping['restore'], $data->objectid,
                            \core\event\base::NOT_FOUND);
                    }
                }
            } else {
                $message = "Event class not found: \"$eventclass\". Skipping log record.";
                $this->log($message, backup::LOG_DEBUG);
                return;             }
        }
        if (!empty($data->other)) {
                        $eventclass = $data->eventname;
            if (class_exists($eventclass)) {
                $othermapping = $eventclass::get_other_mapping();
                if ($othermapping) {
                                        foreach ($data->other as $key => $value) {
                                                if (isset($othermapping[$key]) && !empty($value)) {
                                                        $mapping = $othermapping[$key];
                                                        if ((is_int($mapping) && $mapping === \core\event\base::NOT_MAPPED) ||
                                ($mapping['restore'] === \core\event\base::NOT_MAPPED)) {
                                $data->other[$key] = \core\event\base::NOT_MAPPED;
                            } else {
                                $data->other[$key] = $this->get_mappingid($mapping['restore'], $value,
                                    \core\event\base::NOT_FOUND);
                            }
                        }
                    }
                }
                                $data->other = serialize($data->other);
            } else {
                $message = "Event class not found: \"$eventclass\". Skipping log record.";
                $this->log($message, backup::LOG_DEBUG);
                return;             }
        }

        return $data;
    }
}
