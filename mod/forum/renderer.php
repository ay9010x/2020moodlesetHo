<?php





class mod_forum_renderer extends plugin_renderer_base {

    
    public function neighbouring_discussion_navigation($prev, $next) {
        $html = '';
        if ($prev || $next) {
            $html .= html_writer::start_tag('div', array('class' => 'discussion-nav clearfix'));
            $html .= html_writer::start_tag('ul');
            if ($prev) {
                $url = new moodle_url('/mod/forum/discuss.php', array('d' => $prev->id));
                $html .= html_writer::start_tag('li', array('class' => 'prev-discussion'));
                $html .= html_writer::link($url, format_string($prev->name),
                    array('aria-label' => get_string('prevdiscussiona', 'mod_forum', format_string($prev->name))));
                $html .= html_writer::end_tag('li');
            }
            if ($next) {
                $url = new moodle_url('/mod/forum/discuss.php', array('d' => $next->id));
                $html .= html_writer::start_tag('li', array('class' => 'next-discussion'));
                $html .= html_writer::link($url, format_string($next->name),
                    array('aria-label' => get_string('nextdiscussiona', 'mod_forum', format_string($next->name))));
                $html .= html_writer::end_tag('li');
            }
            $html .= html_writer::end_tag('ul');
            $html .= html_writer::end_tag('div');
        }
        return $html;
    }

    
    public function subscriber_selection_form(user_selector_base $existinguc, user_selector_base $potentialuc) {
        $output = '';
        $formattributes = array();
        $formattributes['id'] = 'subscriberform';
        $formattributes['action'] = '';
        $formattributes['method'] = 'post';
        $output .= html_writer::start_tag('form', $formattributes);
        $output .= html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>'sesskey', 'value'=>sesskey()));

        $existingcell = new html_table_cell();
        $existingcell->text = $existinguc->display(true);
        $existingcell->attributes['class'] = 'existing';
        $actioncell = new html_table_cell();
        $actioncell->text  = html_writer::start_tag('div', array());
        $actioncell->text .= html_writer::empty_tag('input', array('type'=>'submit', 'name'=>'subscribe', 'value'=>$this->page->theme->larrow.' '.get_string('add'), 'class'=>'actionbutton'));
        $actioncell->text .= html_writer::empty_tag('br', array());
        $actioncell->text .= html_writer::empty_tag('input', array('type'=>'submit', 'name'=>'unsubscribe', 'value'=>$this->page->theme->rarrow.' '.get_string('remove'), 'class'=>'actionbutton'));
        $actioncell->text .= html_writer::end_tag('div', array());
        $actioncell->attributes['class'] = 'actions';
        $potentialcell = new html_table_cell();
        $potentialcell->text = $potentialuc->display(true);
        $potentialcell->attributes['class'] = 'potential';

        $table = new html_table();
        $table->attributes['class'] = 'subscribertable boxaligncenter';
        $table->data = array(new html_table_row(array($existingcell, $actioncell, $potentialcell)));
        $output .= html_writer::table($table);

        $output .= html_writer::end_tag('form');
        return $output;
    }

    
    public function subscriber_overview($users, $forum , $course) {
        $output = '';
        $modinfo = get_fast_modinfo($course);
        if (!$users || !is_array($users) || count($users)===0) {
            $output .= $this->output->heading(get_string("nosubscribers", "forum"));
        } else if (!isset($modinfo->instances['forum'][$forum->id])) {
            $output .= $this->output->heading(get_string("invalidmodule", "error"));
        } else {
            $cm = $modinfo->instances['forum'][$forum->id];
            $canviewemail = in_array('email', get_extra_user_fields(context_module::instance($cm->id)));
            $strparams = new stdclass();
            $strparams->name = format_string($forum->name);
            $strparams->count = count($users);
            $output .= $this->output->heading(get_string("subscriberstowithcount", "forum", $strparams));
            $table = new html_table();
            $table->cellpadding = 5;
            $table->cellspacing = 5;
            $table->tablealign = 'center';
            $table->data = array();
            foreach ($users as $user) {
                $info = array($this->output->user_picture($user, array('courseid'=>$course->id)), fullname($user));
                if ($canviewemail) {
                    array_push($info, $user->email);
                }
                $table->data[] = $info;
            }
            $output .= html_writer::table($table);
        }
        return $output;
    }

    
    public function subscribed_users(user_selector_base $existingusers) {
        $output  = $this->output->box_start('subscriberdiv boxaligncenter');
        $output .= html_writer::tag('p', get_string('forcesubscribed', 'forum'));
        $output .= $existingusers->display(true);
        $output .= $this->output->box_end();
        return $output;
    }

    
    public function timed_discussion_tooltip($discussion, $visiblenow) {
        $dates = array();
        if ($discussion->timestart) {
            $dates[] = get_string('displaystart', 'mod_forum').': '.userdate($discussion->timestart);
        }
        if ($discussion->timeend) {
            $dates[] = get_string('displayend', 'mod_forum').': '.userdate($discussion->timeend);
        }

        $str = $visiblenow ? 'timedvisible' : 'timedhidden';
        $dates[] = get_string($str, 'mod_forum');

        $tooltip = implode("\n", $dates);
        return $this->pix_icon('i/calendar', $tooltip, 'moodle', array('class' => 'smallicon timedpost'));
    }

    
    public function render_forum_post_email(\mod_forum\output\forum_post_email $post) {
        $data = $post->export_for_template($this, $this->target === RENDERER_TARGET_TEXTEMAIL);
        return $this->render_from_template('mod_forum/' . $this->forum_post_template(), $data);
    }

    
    public function forum_post_template() {
        return 'forum_post';
    }
}
