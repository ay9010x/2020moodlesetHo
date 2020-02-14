<?php



defined ( 'MOODLE_INTERNAL' ) || die ();


class scheduler_messenger {
    
    protected static function get_message_language($user, $course) {
        if ($course && ! empty ($course->id) and $course->id != SITEID and !empty($course->lang)) {
                        $return = $course->lang;
        } else if (!empty($user->lang)) {
            $return = $user->lang;
        } else if (isset ($CFG->lang)) {
            $return = $CFG->lang;
        } else {
            $return = 'en';
        }

        return $return;
    }

    
    public static function compile_mail_template($template, $format, $parameters, $module = 'scheduler', $lang = null) {
        $params = array ();
        foreach ($parameters as $key => $value) {
            $params [strtolower($key)] = $value;
        }
        $mailstr = get_string_manager()->get_string("email_{$template}_{$format}", $module, $params, $lang);
        return $mailstr;
    }

    
    public static function send_message_from_template($modulename, $messagename, $isnotification,
                                                      stdClass $sender, stdClass $recipient, $course,
                                                      $template, array $parameters) {
        global $CFG;
        global $SITE;

        $lang = self::get_message_language($recipient, $course);

        $defaultvars = array (
                'SITE' => $SITE->fullname,
                'SITE_SHORT' => $SITE->shortname,
                'SITE_URL' => $CFG->wwwroot,
                'SENDER' => fullname ( $sender ),
                'RECIPIENT' => fullname ( $recipient )
        );

        if ($course) {
            $defaultvars['COURSE_SHORT'] = $course->shortname;
            $defaultvars['COURSE'] = $course->fullname;
            $defaultvars['COURSE_URL'] = $CFG->wwwroot . '/course/view.php?id=' . $course->id;
        }

        $vars = array_merge($defaultvars, $parameters);

        $message = new \core\message\message();
        $message->component = $modulename;
        $message->name = $messagename;
        $message->userfrom = $sender;
        $message->userto = $recipient;
        $message->subject = self::compile_mail_template($template, 'subject', $vars, $modulename, $lang);
        $message->fullmessage = self::compile_mail_template($template, 'plain', $vars, $modulename, $lang);
        $message->fullmessageformat = FORMAT_PLAIN;
        $message->fullmessagehtml = self::compile_mail_template ( $template, 'html', $vars, $modulename, $lang );
        $message->notification = '1';
        $message->contexturl = $defaultvars['COURSE_URL'];
        $message->contexturlname = $course->fullname;

        $msgid = message_send($message);
        return $msgid;
    }

    
    public static function get_scheduler_variables(scheduler_instance $scheduler,  $slot,
                                                   $teacher, $student, $course, $recipient) {

        global $CFG;

        $lang = self::get_message_language($recipient, $course);
                $oldlang = force_current_language($lang);

        $tz = core_date::get_user_timezone($recipient);

        $vars = array();

        if ($scheduler) {
            $vars['MODULE']     = format_string($scheduler->name);
            $vars['STAFFROLE']  = $scheduler->get_teacher_name();
            $vars['SCHEDULER_URL'] = $CFG->wwwroot.'/mod/scheduler/view.php?id='.$scheduler->cmid;
        }
        if ($slot) {
            $vars ['DATE']     = userdate($slot->starttime, get_string('strftimedate'), $tz);
            $vars ['TIME']     = userdate($slot->starttime, get_string('strftimetime'), $tz);
            $vars ['ENDTIME']  = userdate($slot->endtime, get_string('strftimetime'), $tz);
            $vars ['LOCATION'] = format_string($slot->appointmentlocation);
        }
        if ($teacher) {
            $vars['ATTENDANT']     = fullname($teacher);
            $vars['ATTENDANT_URL'] = $CFG->wwwroot.'/user/view.php?id='.$teacher->id.'&course='.$scheduler->course;
        }
        if ($student) {
            $vars['ATTENDEE']     = fullname($student);
            $vars['ATTENDEE_URL'] = $CFG->wwwroot.'/user/view.php?id='.$student->id.'&course='.$scheduler->course;
        }

                force_current_language($oldlang);

        return $vars;

    }


    
    public static function send_slot_notification(scheduler_slot $slot, $messagename, $template,
                                                  stdClass $sender, stdClass $recipient,
                                                  stdClass $teacher, stdClass $student, stdClass $course) {
        $vars = self::get_scheduler_variables($slot->get_scheduler(), $slot, $teacher, $student, $course, $recipient);
        self::send_message_from_template('mod_scheduler', $messagename, 1, $sender, $recipient, $course, $template, $vars);
    }

}