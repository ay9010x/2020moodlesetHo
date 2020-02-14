<?php




class block_admin_bookmarks extends block_base {

    
    public $blockname = null;

    
    protected $contentgenerated = false;

    
    protected $docked = null;

    
    function init() {
        $this->blockname = get_class($this);
        $this->title = get_string('pluginname', $this->blockname);
    }

    
    function instance_allow_multiple() {
        return false;
    }

    
    function applicable_formats() {
        if (has_capability('moodle/site:config', context_system::instance())) {
            return array('all' => true);
        } else {
            return array('site' => true);
        }
    }

    
    function get_content() {

        global $CFG;

                if ($this->contentgenerated === true) {
            return $this->content;
        }
        $this->content = new stdClass();

        if (get_user_preferences('admin_bookmarks')) {
            require_once($CFG->libdir.'/adminlib.php');
            $adminroot = admin_get_root(false, false);  
            $bookmarks = explode(',', get_user_preferences('admin_bookmarks'));
                        $contents = array();
            foreach($bookmarks as $bookmark) {
                $temp = $adminroot->locate($bookmark);
                if ($temp instanceof admin_settingpage) {
                    $contenturl = new moodle_url('/admin/settings.php', array('section'=>$bookmark));
                    $contentlink = html_writer::link($contenturl, $temp->visiblename);
                    $contents[] = html_writer::tag('li', $contentlink);
                } else if ($temp instanceof admin_externalpage) {
                    $contenturl = new moodle_url($temp->url);
                    $contentlink = html_writer::link($contenturl, $temp->visiblename);
                    $contents[] = html_writer::tag('li', $contentlink);
                }
            }
            $this->content->text = html_writer::tag('ol', implode('', $contents), array('class' => 'list'));
        } else {
            $bookmarks = array();
        }

        $this->content->footer = '';
        $this->page->settingsnav->initialise();
        $node = $this->page->settingsnav->get('root', navigation_node::TYPE_SITE_ADMIN);
        if (!$node || !$node->contains_active_node()) {
            return $this->content;
        }
        $section = $node->find_active_node()->key;

        if ($section == 'search' || empty($section)){
                        $this->content->footer = '';
        } else if (in_array($section, $bookmarks)) {
            $deleteurl = new moodle_url('/blocks/admin_bookmarks/delete.php', array('section'=>$section, 'sesskey'=>sesskey()));
            $this->content->footer =  html_writer::link($deleteurl, get_string('unbookmarkthispage','admin'));
        } else {
            $createurl = new moodle_url('/blocks/admin_bookmarks/create.php', array('section'=>$section, 'sesskey'=>sesskey()));
            $this->content->footer = html_writer::link($createurl, get_string('bookmarkthispage','admin'));
        }

        return $this->content;
    }

    
    public function get_aria_role() {
        return 'navigation';
    }
}


