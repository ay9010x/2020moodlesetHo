<?php




class block_news_items extends block_base {
    function init() {
        $this->title = get_string('pluginname', 'block_news_items');
    }

    function get_content() {
        global $CFG, $USER;

        if ($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->text = '';
        $this->content->footer = '';

        if (empty($this->instance)) {
            return $this->content;
        }


        if ($this->page->course->newsitems) {   
            require_once($CFG->dirroot.'/mod/forum/lib.php');   
            $text = '';

            if (!$forum = forum_get_course_forum($this->page->course->id, 'news')) {
                return '';
            }

            $modinfo = get_fast_modinfo($this->page->course);
            if (empty($modinfo->instances['forum'][$forum->id])) {
                return '';
            }
            $cm = $modinfo->instances['forum'][$forum->id];

            if (!$cm->uservisible) {
                return '';
            }

            $context = context_module::instance($cm->id);

                    if (!has_capability('mod/forum:viewdiscussion', $context)) {
                return '';
            }

                    $groupmode    = groups_get_activity_groupmode($cm);
            $currentgroup = groups_get_activity_group($cm, true);

            if (forum_user_can_post_discussion($forum, $currentgroup, $groupmode, $cm, $context)) {
                $text .= '<div class="newlink"><a href="'.$CFG->wwwroot.'/mod/forum/post.php?forum='.$forum->id.'">'.
                          get_string('addanewtopic', 'forum').'</a>...</div>';
            }

        
                                                                        $sort = forum_get_default_sort_order(true, 'p.modified', 'd', false);
            if (! $discussions = forum_get_discussions($cm, $sort, false,
                                                        -1, $this->page->course->newsitems,
                                                        false, -1, 0, FORUM_POSTS_ALL_USER_GROUPS) ) {
                $text .= '('.get_string('nonews', 'forum').')';
                $this->content->text = $text;
                return $this->content;
            }

        
            $strftimerecent = get_string('strftimerecent');
            $strmore = get_string('more', 'forum');

                    $text .= "\n<ul class='unlist'>\n";
            foreach ($discussions as $discussion) {

                $discussion->subject = $discussion->name;

                $discussion->subject = format_string($discussion->subject, true, $forum->course);

                $text .= '<li class="post">'.
                         '<div class="head clearfix">'.
                         '<div class="date">'.userdate($discussion->modified, $strftimerecent).'</div>'.
                         '<div class="name">'.fullname($discussion).'</div></div>'.
                         '<div class="info"><a href="'.$CFG->wwwroot.'/mod/forum/discuss.php?d='.$discussion->discussion.'">'.$discussion->subject.'</a></div>'.
                         "</li>\n";
            }
            $text .= "</ul>\n";

            $this->content->text = $text;

            $this->content->footer = '<a href="'.$CFG->wwwroot.'/mod/forum/view.php?f='.$forum->id.'">'.
                                      get_string('oldertopics', 'forum').'</a> ...';

                    if (isset($CFG->enablerssfeeds) && isset($CFG->forum_enablerssfeeds) &&
                $CFG->enablerssfeeds && $CFG->forum_enablerssfeeds && $forum->rsstype && $forum->rssarticles) {
                require_once($CFG->dirroot.'/lib/rsslib.php');                   if ($forum->rsstype == 1) {
                    $tooltiptext = get_string('rsssubscriberssdiscussions','forum');
                } else {
                    $tooltiptext = get_string('rsssubscriberssposts','forum');
                }
                if (!isloggedin()) {
                    $userid = $CFG->siteguest;
                } else {
                    $userid = $USER->id;
                }

                $this->content->footer .= '<br />'.rss_get_link($context->id, $userid, 'mod_forum', $forum->id, $tooltiptext);
            }

        }

        return $this->content;
    }
}


