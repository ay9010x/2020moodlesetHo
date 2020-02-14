<?php



defined('MOODLE_INTERNAL') || die();

define('BLOCK_BLOG_TAGS_DEFAULTTIMEWITHIN', 90);
define('BLOCK_BLOG_TAGS_DEFAULTNUMBEROFTAGS', 20);
define('BLOCK_BLOG_TAGS_DEFAULTSORT', 'name');

class block_blog_tags extends block_base {
    function init() {
        $this->title = get_string('pluginname', 'block_blog_tags');
    }

    function instance_allow_multiple() {
        return true;
    }

    function has_config() {
        return false;
    }

    function applicable_formats() {
        return array('all' => true, 'my' => false, 'tag' => false);
    }

    function instance_allow_config() {
        return true;
    }

    function specialization() {

                if (empty($this->config->title)) {
            $this->title = get_string('pluginname', 'block_blog_tags');
        } else {
            $this->title = $this->config->title;
        }
    }

    function get_content() {
        global $CFG, $SITE, $USER, $DB, $OUTPUT;

        if ($this->content !== NULL) {
            return $this->content;
        }

                if (empty($CFG->bloglevel)) {
            $this->content = new stdClass();
            $this->content->text = '';
            if ($this->page->user_is_editing()) {
                $this->content->text = get_string('blogdisable', 'blog');
            }
            return $this->content;

        } else if (!core_tag_tag::is_enabled('core', 'post')) {
            $this->content = new stdClass();
            $this->content->text = '';
            if ($this->page->user_is_editing()) {
                $this->content->text = get_string('tagsaredisabled', 'tag');
            }
            return $this->content;

        } else if ($CFG->bloglevel < BLOG_GLOBAL_LEVEL and (!isloggedin() or isguestuser())) {
            $this->content = new stdClass();
            $this->content->text = '';
            return $this->content;
        }

                require_once($CFG->dirroot .'/blog/lib.php');

        if (empty($this->config)) {
            $this->config = new stdClass();
        }

        if (empty($this->config->timewithin)) {
            $this->config->timewithin = BLOCK_BLOG_TAGS_DEFAULTTIMEWITHIN;
        }
        if (empty($this->config->numberoftags)) {
            $this->config->numberoftags = BLOCK_BLOG_TAGS_DEFAULTNUMBEROFTAGS;
        }
        if (empty($this->config->sort)) {
            $this->config->sort = BLOCK_BLOG_TAGS_DEFAULTSORT;
        }

        $this->content = new stdClass();
        $this->content->text = '';
        $this->content->footer = '';

                $timewithin = time() - $this->config->timewithin * 24 * 60 * 60; 
        $context = $this->page->context;

                $type = '';
        if (!has_capability('moodle/user:readuserblogs', context_system::instance())) {
            $type = " AND (p.publishstate = 'site' or p.publishstate='public')";
        }

        $sql  = "SELECT t.id, t.isstandard, t.rawname, t.name, COUNT(DISTINCT ti.id) AS ct
                   FROM {tag} t, {tag_instance} ti, {post} p, {blog_association} ba
                  WHERE t.id = ti.tagid AND p.id = ti.itemid
                        $type
                        AND ti.itemtype = 'post'
                        AND ti.component = 'core'
                        AND ti.timemodified > $timewithin";

        if ($context->contextlevel == CONTEXT_MODULE) {
            $sql .= " AND ba.contextid = $context->id AND p.id = ba.blogid ";
        } else if ($context->contextlevel == CONTEXT_COURSE) {
            $sql .= " AND ba.contextid = $context->id AND p.id = ba.blogid ";
        }

        $sql .= "
               GROUP BY t.id, t.isstandard, t.name, t.rawname
               ORDER BY ct DESC, t.name ASC";

        if ($tags = $DB->get_records_sql($sql, null, 0, $this->config->numberoftags)) {

                                
            $totaltags  = count($tags);
            $currenttag = 0;

            $size = 20;
            $lasttagct = -1;

            $etags = array();
            foreach ($tags as $tag) {

                $currenttag++;

                if ($currenttag == 1) {
                    $lasttagct = $tag->ct;
                    $size = 20;
                } else if ($tag->ct != $lasttagct) {
                    $lasttagct = $tag->ct;
                    $size = 20 - ( (int)((($currenttag - 1) / $totaltags) * 20) );
                }

                $tag->class = ($tag->isstandard ? "standardtag " : "") . "s$size";
                $etags[] = $tag;

            }

                    $CFG->tagsort = $this->config->sort;
            usort($etags, "block_blog_tags_sort");

                            $this->content->text .= "\n<ul class='inline-list'>\n";
            foreach ($etags as $tag) {
                $blogurl = new moodle_url('/blog/index.php');

                switch ($CFG->bloglevel) {
                    case BLOG_USER_LEVEL:
                        $blogurl->param('userid', $USER->id);
                    break;

                    default:
                        if ($context->contextlevel == CONTEXT_MODULE) {
                            $blogurl->param('modid', $context->instanceid);
                        } else if ($context->contextlevel == CONTEXT_COURSE) {
                            $blogurl->param('courseid', $context->instanceid);
                        }

                    break;
                }

                $blogurl->param('tagid', $tag->id);
                $link = html_writer::link($blogurl, core_tag_tag::make_display_name($tag),
                        array('class' => $tag->class,
                            'title' => get_string('numberofentries', 'blog', $tag->ct)));
                $this->content->text .= '<li>' . $link . '</li> ';
            }
            $this->content->text .= "\n</ul>\n";

        }
        return $this->content;
    }
}

function block_blog_tags_sort($a, $b) {
    global $CFG;

    if (empty($CFG->tagsort)) {
        return 0;
    } else {
        $tagsort = $CFG->tagsort;
    }

    if (is_numeric($a->$tagsort)) {
        return ($a->$tagsort == $b->$tagsort) ? 0 : ($a->$tagsort > $b->$tagsort) ? 1 : -1;
    } elseif (is_string($a->$tagsort)) {
        return strcmp($a->$tagsort, $b->$tagsort);     } else {
        return 0;
    }
}


