<?php



namespace block_rss_client\output;

defined('MOODLE_INTERNAL') || die();


class channel_image implements \renderable, \templatable {

    
    protected $url;

    
    protected $title;

    
    protected $link;

    
    public function __construct(\moodle_url $url, $title, \moodle_url $link = null) {
        $this->url      = $url;
        $this->title    = $title;
        $this->link     = $link;
    }

    
    public function export_for_template(\renderer_base $output) {
        return array(
            'url'   => clean_param($this->url, PARAM_URL),
            'title' => $this->title,
            'link'  => clean_param($this->link, PARAM_URL),
        );
    }

    
    public function set_url(\moodle_url $url) {
        $this->url = $url;

        return $this;
    }

    
    public function get_url() {
        return $this->url;
    }

    
    public function set_title($title) {
        $this->title = $title;

        return $this;
    }

    
    public function get_title() {
        return $this->title;
    }

    
    public function set_link($link) {
        $this->link = $link;

        return $this;
    }

    
    public function get_link() {
        return $this->link;
    }
}
