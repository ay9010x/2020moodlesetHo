<?php



defined('MOODLE_INTERNAL') || die();


class block_blog_menu extends block_base {

    function init() {
        $this->title = get_string('pluginname', 'block_blog_menu');
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

    function get_content() {
        global $CFG;

                if ($this->content !== NULL) {
            return $this->content;
        }

        if (empty($CFG->enableblogs)) {
            $this->content = new stdClass();
            $this->content->text = '';
            if ($this->page->user_is_editing()) {
                $this->content->text = get_string('blogdisable', 'blog');
            }
            return $this->content;

        } else if ($CFG->bloglevel < BLOG_GLOBAL_LEVEL and (!isloggedin() or isguestuser())) {
            $this->content = new stdClass();
            $this->content->text = '';
            return $this->content;
        }

                require_once($CFG->dirroot .'/blog/lib.php');

                $this->content = new stdClass();

        $options = blog_get_all_options($this->page);
        if (count($options) == 0) {
            $this->content->text = '';
            return $this->content;
        }

                $menulist = array();
        foreach ($options as $types) {
            foreach ($types as $link) {
                $menulist[] = html_writer::link($link['link'], $link['string']);
            }
            $menulist[] = '<hr />';
        }
                array_pop($menulist);
                $this->content->text = html_writer::alist($menulist, array('class'=>'list'));

                if (has_capability('moodle/blog:search', context_system::instance())) {
                        $form  = html_writer::tag('label', get_string('search', 'admin'), array('for'=>'blogsearchquery', 'class'=>'accesshide'));
            $form .= html_writer::empty_tag('input', array('id'=>'blogsearchquery', 'type'=>'text', 'name'=>'search'));
            $form .= html_writer::empty_tag('input', array('type'=>'submit', 'value'=>get_string('search')));
            $this->content->footer = html_writer::tag('form', html_writer::tag('div', $form), array('class'=>'blogsearchform', 'method'=>'get', 'action'=>new moodle_url('/blog/index.php')));
        } else {
                        $this->content->footer = '';
        }

                return $this->content;
    }

    
    public function get_aria_role() {
        return 'navigation';
    }
}
