<?php



use block_online_users\fetcher;


class block_online_users extends block_base {
    function init() {
        $this->title = get_string('pluginname','block_online_users');
    }

    function has_config() {
        return true;
    }

    function get_content() {
        global $USER, $CFG, $DB, $OUTPUT, $PAGE;

        if ($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->text = '';
        $this->content->footer = '';

        if (empty($this->instance)) {
            return $this->content;
        }

        $timetoshowusers = 300;         if (isset($CFG->block_online_users_timetosee)) {
            $timetoshowusers = $CFG->block_online_users_timetosee * 60;
        }
        $now = time();

                $isseparategroups = ($this->page->course->groupmode == SEPARATEGROUPS
                             && $this->page->course->groupmodeforce
                             && !has_capability('moodle/site:accessallgroups', $this->page->context));

                $currentgroup = $isseparategroups ? groups_get_course_group($this->page->course) : NULL;

        $sitelevel = $this->page->course->id == SITEID || $this->page->context->contextlevel < CONTEXT_COURSE;

        $onlineusers = new fetcher($currentgroup, $now, $timetoshowusers, $this->page->context,
                $sitelevel, $this->page->course->id);

                $minutes  = floor($timetoshowusers/60);

                if (!has_capability('block/online_users:viewlist', $this->page->context)) {
            if (!$usercount = $onlineusers->count_users()) {
                $usercount = get_string("none");
            }
            $this->content->text = "<div class=\"info\">".get_string("periodnminutes","block_online_users",$minutes).": $usercount</div>";
            return $this->content;
        }
        $userlimit = 50;         if ($users = $onlineusers->get_users($userlimit)) {
            foreach ($users as $user) {
                $users[$user->id]->fullname = fullname($user);
            }
        } else {
            $users = array();
        }

        if (count($users) < $userlimit) {
            $usercount = "";
        } else {
            $usercount = $onlineusers->count_users();
            $usercount = ": $usercount";
        }

        $this->content->text = "<div class=\"info\">(".get_string("periodnminutes","block_online_users",$minutes)."$usercount)</div>";

                        if (!empty($users)) {
                                    $this->content->text .= "<ul class='list'>\n";
            if (isloggedin() && has_capability('moodle/site:sendmessage', $this->page->context)
                           && !empty($CFG->messaging) && !isguestuser()) {
                $canshowicon = true;
                message_messenger_requirejs();
            } else {
                $canshowicon = false;
            }
            foreach ($users as $user) {
                $this->content->text .= '<li class="listentry">';
                $timeago = format_time($now - $user->lastaccess); 
                if (isguestuser($user)) {
                    $this->content->text .= '<div class="user">'.$OUTPUT->user_picture($user, array('size'=>16, 'alttext'=>false));
                    $this->content->text .= get_string('guestuser').'</div>';

                } else {
                    $this->content->text .= '<div class="user">';
                    $this->content->text .= '<a href="'.$CFG->wwwroot.'/user/view.php?id='.$user->id.'&amp;course='.$this->page->course->id.'" title="'.$timeago.'">';
                    $this->content->text .= $OUTPUT->user_picture($user, array('size'=>16, 'alttext'=>false, 'link'=>false)) .$user->fullname.'</a></div>';
                }
                if ($canshowicon and ($USER->id != $user->id) and !isguestuser($user)) {                      $anchortagcontents = '<img class="iconsmall" src="'.$OUTPUT->pix_url('t/message') . '" alt="'. get_string('messageselectadd') .'" />';
                    $anchorurl = new moodle_url('/message/index.php', array('id' => $user->id));
                    $anchortag = html_writer::link($anchorurl, $anchortagcontents, array_merge(
                      message_messenger_sendmessage_link_params($user),
                      array('title' => get_string('messageselectadd'))
                    ));

                    $this->content->text .= '<div class="message">'.$anchortag.'</div>';
                }
                $this->content->text .= "</li>\n";
            }
            $this->content->text .= '</ul><div class="clearer"><!-- --></div>';
        } else {
            $this->content->text .= "<div class=\"info\">".get_string("none")."</div>";
        }

        return $this->content;
    }
}


