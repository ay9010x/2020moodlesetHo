<?php




require_once($CFG->dirroot . '/mod/forum/lib.php');
require_once($CFG->libdir . '/portfolio/caller.php');


class forum_portfolio_caller extends portfolio_module_caller_base {

    protected $postid;
    protected $discussionid;
    protected $attachment;

    private $post;
    private $forum;
    private $discussion;
    private $posts;
    private $keyedfiles; 
    
    public static function expected_callbackargs() {
        return array(
            'postid'       => false,
            'discussionid' => false,
            'attachment'   => false,
        );
    }
    
    function __construct($callbackargs) {
        parent::__construct($callbackargs);
        if (!$this->postid && !$this->discussionid) {
            throw new portfolio_caller_exception('mustprovidediscussionorpost', 'forum');
        }
    }
    
    public function load_data() {
        global $DB;

        if ($this->postid) {
            if (!$this->post = $DB->get_record('forum_posts', array('id' => $this->postid))) {
                throw new portfolio_caller_exception('invalidpostid', 'forum');
            }
        }

        $dparams = array();
        if ($this->discussionid) {
            $dbparams = array('id' => $this->discussionid);
        } else if ($this->post) {
            $dbparams = array('id' => $this->post->discussion);
        } else {
            throw new portfolio_caller_exception('mustprovidediscussionorpost', 'forum');
        }

        if (!$this->discussion = $DB->get_record('forum_discussions', $dbparams)) {
            throw new portfolio_caller_exception('invaliddiscussionid', 'forum');
        }

        if (!$this->forum = $DB->get_record('forum', array('id' => $this->discussion->forum))) {
            throw new portfolio_caller_exception('invalidforumid', 'forum');
        }

        if (!$this->cm = get_coursemodule_from_instance('forum', $this->forum->id)) {
            throw new portfolio_caller_exception('invalidcoursemodule');
        }

        $this->modcontext = context_module::instance($this->cm->id);
        $fs = get_file_storage();
        if ($this->post) {
            if ($this->attachment) {
                $this->set_file_and_format_data($this->attachment);
            } else {
                $attach = $fs->get_area_files($this->modcontext->id, 'mod_forum', 'attachment', $this->post->id, 'timemodified', false);
                $embed  = $fs->get_area_files($this->modcontext->id, 'mod_forum', 'post', $this->post->id, 'timemodified', false);
                $files = array_merge($attach, $embed);
                $this->set_file_and_format_data($files);
            }
            if (!empty($this->multifiles)) {
                $this->keyedfiles[$this->post->id] = $this->multifiles;
            } else if (!empty($this->singlefile)) {
                $this->keyedfiles[$this->post->id] = array($this->singlefile);
            }
        } else {             $fs = get_file_storage();
            $this->posts = forum_get_all_discussion_posts($this->discussion->id, 'p.created ASC');
            $this->multifiles = array();
            foreach ($this->posts as $post) {
                $attach = $fs->get_area_files($this->modcontext->id, 'mod_forum', 'attachment', $post->id, 'timemodified', false);
                $embed  = $fs->get_area_files($this->modcontext->id, 'mod_forum', 'post', $post->id, 'timemodified', false);
                $files = array_merge($attach, $embed);
                if ($files) {
                    $this->keyedfiles[$post->id] = $files;
                } else {
                    continue;
                }
                $this->multifiles = array_merge($this->multifiles, array_values($this->keyedfiles[$post->id]));
            }
        }
        if (empty($this->multifiles) && !empty($this->singlefile)) {
            $this->multifiles = array($this->singlefile);         }
                if (empty($this->attachment)) {
            if (!empty($this->multifiles)) {
                $this->add_format(PORTFOLIO_FORMAT_RICHHTML);
            } else {
                $this->add_format(PORTFOLIO_FORMAT_PLAINHTML);
            }
        }
    }

    
    function get_return_url() {
        global $CFG;
        return $CFG->wwwroot . '/mod/forum/discuss.php?d=' . $this->discussion->id;
    }
    
    function get_navigation() {
        global $CFG;

        $navlinks = array();
        $navlinks[] = array(
            'name' => format_string($this->discussion->name),
            'link' => $CFG->wwwroot . '/mod/forum/discuss.php?d=' . $this->discussion->id,
            'type' => 'title'
        );
        return array($navlinks, $this->cm);
    }
    
    function prepare_package() {
        global $CFG;

                $writingleap = false;
        if ($this->exporter->get('formatclass') == PORTFOLIO_FORMAT_LEAP2A) {
            $leapwriter = $this->exporter->get('format')->leap2a_writer();
            $writingleap = true;
        }
        if ($this->attachment) {             $this->copy_files(array($this->singlefile), $this->attachment);
            if ($writingleap) {                 $entry = new portfolio_format_leap2a_file($this->singlefile->get_filename(), $this->singlefile);
                $leapwriter->add_entry($entry);
                return $this->exporter->write_new_file($leapwriter->to_xml(), $this->exporter->get('format')->manifest_name(), true);
            }

        } else if (empty($this->post)) {              $content = '';             $ids = array();             foreach ($this->posts as $post) {
                $posthtml =  $this->prepare_post($post);
                if ($writingleap) {
                    $ids[] = $this->prepare_post_leap2a($leapwriter, $post, $posthtml);
                } else {
                    $content .= $posthtml . '<br /><br />';
                }
            }
            $this->copy_files($this->multifiles);
            $name = 'discussion.html';
            $manifest = ($this->exporter->get('format') instanceof PORTFOLIO_FORMAT_RICH);
            if ($writingleap) {
                                $selection = new portfolio_format_leap2a_entry('forumdiscussion' . $this->discussionid,
                    get_string('discussion', 'forum') . ': ' . $this->discussion->name, 'selection');
                $leapwriter->add_entry($selection);
                $leapwriter->make_selection($selection, $ids, 'Grouping');
                $content = $leapwriter->to_xml();
                $name = $this->get('exporter')->get('format')->manifest_name();
            }
            $this->get('exporter')->write_new_file($content, $name, $manifest);

        } else {             $posthtml = $this->prepare_post($this->post);

            $content = $posthtml;
            $name = 'post.html';
            $manifest = ($this->exporter->get('format') instanceof PORTFOLIO_FORMAT_RICH);

            if ($writingleap) {
                $this->prepare_post_leap2a($leapwriter, $this->post, $posthtml);
                $content = $leapwriter->to_xml();
                $name = $this->exporter->get('format')->manifest_name();
            }
            $this->copy_files($this->multifiles);
            $this->get('exporter')->write_new_file($content, $name, $manifest);
        }
    }

    
    private function prepare_post_leap2a(portfolio_format_leap2a_writer $leapwriter, $post, $posthtml) {
        $entry = new portfolio_format_leap2a_entry('forumpost' . $post->id,  $post->subject, 'resource', $posthtml);
        $entry->published = $post->created;
        $entry->updated = $post->modified;
        $entry->author = $post->author;
        if (is_array($this->keyedfiles) && array_key_exists($post->id, $this->keyedfiles) && is_array($this->keyedfiles[$post->id])) {
            $leapwriter->link_files($entry, $this->keyedfiles[$post->id], 'forumpost' . $post->id . 'attachment');
        }
        $entry->add_category('web', 'resource_type');
        $leapwriter->add_entry($entry);
        return $entry->id;
    }

    
    private function copy_files($files, $justone=false) {
        if (empty($files)) {
            return;
        }
        foreach ($files as $f) {
            if ($justone && $f->get_id() != $justone) {
                continue;
            }
            $this->get('exporter')->copy_existing_file($f);
            if ($justone && $f->get_id() == $justone) {
                return true;             }
        }
    }
    
    private function prepare_post($post, $fileoutputextras=null) {
        global $DB;
        static $users;
        if (empty($users)) {
            $users = array($this->user->id => $this->user);
        }
        if (!array_key_exists($post->userid, $users)) {
            $users[$post->userid] = $DB->get_record('user', array('id' => $post->userid));
        }
                $post->author = $users[$post->userid];
        $viewfullnames = true;
                $options = portfolio_format_text_options();
        $format = $this->get('exporter')->get('format');
        $formattedtext = format_text($post->message, $post->messageformat, $options, $this->get('course')->id);
        $formattedtext = portfolio_rewrite_pluginfile_urls($formattedtext, $this->modcontext->id, 'mod_forum', 'post', $post->id, $format);

        $output = '<table border="0" cellpadding="3" cellspacing="0" class="forumpost">';

        $output .= '<tr class="header"><td>';        $output .= '</td>';

        if ($post->parent) {
            $output .= '<td class="topic">';
        } else {
            $output .= '<td class="topic starter">';
        }
        $output .= '<div class="subject">'.format_string($post->subject).'</div>';

        $fullname = fullname($users[$post->userid], $viewfullnames);
        $by = new stdClass();
        $by->name = $fullname;
        $by->date = userdate($post->modified, '', core_date::get_user_timezone($this->user));
        $output .= '<div class="author">'.get_string('bynameondate', 'forum', $by).'</div>';

        $output .= '</td></tr>';

        $output .= '<tr><td class="left side" valign="top">';

        $output .= '</td><td class="content">';

        $output .= $formattedtext;

        if (is_array($this->keyedfiles) && array_key_exists($post->id, $this->keyedfiles) && is_array($this->keyedfiles[$post->id]) && count($this->keyedfiles[$post->id]) > 0) {
            $output .= '<div class="attachments">';
            $output .= '<br /><b>' .  get_string('attachments', 'forum') . '</b>:<br /><br />';
            foreach ($this->keyedfiles[$post->id] as $file) {
                $output .= $format->file_output($file)  . '<br/ >';
            }
            $output .= "</div>";
        }

        $output .= '</td></tr></table>'."\n\n";

        return $output;
    }
    
    function get_sha1() {
        $filesha = '';
        try {
            $filesha = $this->get_sha1_file();
        } catch (portfolio_caller_exception $e) { } 
        if ($this->post) {
            return sha1($filesha . ',' . $this->post->subject . ',' . $this->post->message);
        } else {
            $sha1s = array($filesha);
            foreach ($this->posts as $post) {
                $sha1s[] = sha1($post->subject . ',' . $post->message);
            }
            return sha1(implode(',', $sha1s));
        }
    }

    function expected_time() {
        $filetime = $this->expected_time_file();
        if ($this->posts) {
            $posttime = portfolio_expected_time_db(count($this->posts));
            if ($filetime < $posttime) {
                return $posttime;
            }
        }
        return $filetime;
    }
    
    function check_permissions() {
        $context = context_module::instance($this->cm->id);
        if ($this->post) {
            return (has_capability('mod/forum:exportpost', $context)
                || ($this->post->userid == $this->user->id
                    && has_capability('mod/forum:exportownpost', $context)));
        }
        return has_capability('mod/forum:exportdiscussion', $context);
    }
    
    public static function display_name() {
        return get_string('modulename', 'forum');
    }

    public static function base_supported_formats() {
        return array(PORTFOLIO_FORMAT_FILE, PORTFOLIO_FORMAT_RICHHTML, PORTFOLIO_FORMAT_PLAINHTML, PORTFOLIO_FORMAT_LEAP2A);
    }
}



class forum_file_info_container extends file_info {
    
    protected $browser;
    
    protected $course;
    
    protected $cm;
    
    protected $component;
    
    protected $context;
    
    protected $areas;
    
    protected $filearea;

    
    public function __construct($browser, $course, $cm, $context, $areas, $filearea) {
        parent::__construct($browser, $context);
        $this->browser = $browser;
        $this->course = $course;
        $this->cm = $cm;
        $this->component = 'mod_forum';
        $this->context = $context;
        $this->areas = $areas;
        $this->filearea = $filearea;
    }

    
    public function get_params() {
        return array(
            'contextid' => $this->context->id,
            'component' => $this->component,
            'filearea' => $this->filearea,
            'itemid' => null,
            'filepath' => null,
            'filename' => null,
        );
    }

    
    public function is_writable() {
        return false;
    }

    
    public function is_directory() {
        return true;
    }

    
    public function get_visible_name() {
        return $this->areas[$this->filearea];
    }

    
    public function get_children() {
        return $this->get_filtered_children('*', false, true);
    }
    
    private function get_filtered_children($extensions = '*', $countonly = false, $returnemptyfolders = false) {
        global $DB;
        $params = array('contextid' => $this->context->id,
            'component' => $this->component,
            'filearea' => $this->filearea);
        $sql = 'SELECT DISTINCT itemid
                    FROM {files}
                    WHERE contextid = :contextid
                    AND component = :component
                    AND filearea = :filearea';
        if (!$returnemptyfolders) {
            $sql .= ' AND filename <> :emptyfilename';
            $params['emptyfilename'] = '.';
        }
        list($sql2, $params2) = $this->build_search_files_sql($extensions);
        $sql .= ' '.$sql2;
        $params = array_merge($params, $params2);
        if ($countonly !== false) {
            $sql .= ' ORDER BY itemid DESC';
        }

        $rs = $DB->get_recordset_sql($sql, $params);
        $children = array();
        foreach ($rs as $record) {
            if (($child = $this->browser->get_file_info($this->context, 'mod_forum', $this->filearea, $record->itemid))
                    && ($returnemptyfolders || $child->count_non_empty_children($extensions))) {
                $children[] = $child;
            }
            if ($countonly !== false && count($children) >= $countonly) {
                break;
            }
        }
        $rs->close();
        if ($countonly !== false) {
            return count($children);
        }
        return $children;
    }

    
    public function get_non_empty_children($extensions = '*') {
        return $this->get_filtered_children($extensions, false);
    }

    
    public function count_non_empty_children($extensions = '*', $limit = 1) {
        return $this->get_filtered_children($extensions, $limit);
    }

    
    public function get_parent() {
        return $this->browser->get_file_info($this->context);
    }
}
