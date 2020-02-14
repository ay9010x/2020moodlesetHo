<?php



namespace block_rss_client\output;

defined('MOODLE_INTERNAL') || die();


class item implements \renderable, \templatable {

    
    protected $id;

    
    protected $link;

    
    protected $title;

    
    protected $description;

    
    protected $permalink;

    
    protected $timestamp;

    
    protected $showdescription;

    
    public function __construct($id, \moodle_url $link, $title, $description, \moodle_url $permalink, $timestamp,
            $showdescription = true) {
        $this->id               = $id;
        $this->link             = $link;
        $this->title            = $title;
        $this->description      = $description;
        $this->permalink        = $permalink;
        $this->timestamp        = $timestamp;
        $this->showdescription  = $showdescription;
    }

    
    public function export_for_template(\renderer_base $output) {
        $data = array(
            'id'            => $this->id,
            'permalink'     => clean_param($this->permalink, PARAM_URL),
            'datepublished' => $output->format_published_date($this->timestamp),
            'link'          => clean_param($this->link, PARAM_URL),
        );

                $title = $this->title;
        if (!$title) {
            $title = strip_tags($this->description);
            $title = \core_text::substr($title, 0, 20) . '...';
        }

                $data['title']          = $output->format_title($title);
        $data['description']    = $this->showdescription ? $output->format_description($this->description) : null;

        return $data;
    }

    
    public function set_id($id) {
        $this->id = $id;

        return $this;
    }

    
    public function get_id() {
        return $this->id;
    }

    
    public function set_link(\moodle_url $link) {
        $this->link = $link;

        return $this;
    }

    
    public function get_link() {
        return $this->link;
    }

    
    public function set_title($title) {
        $this->title = $title;

        return $this;
    }

    
    public function get_title() {
        return $this->title;
    }

    
    public function set_description($description) {
        $this->description = $description;

        return $this;
    }

    
    public function get_description() {
        return $this->description;
    }

    
    public function set_permalink($permalink) {
        $this->permalink = $permalink;

        return $this;
    }

    
    public function get_permalink() {
        return $this->permalink;
    }

    
    public function set_timestamp($timestamp) {
        $this->timestamp = $timestamp;

        return $this;
    }

    
    public function get_timestamp() {
        return $this->timestamp;
    }

    
    public function set_showdescription($showdescription) {
        $this->showdescription = boolval($showdescription);

        return $this;
    }

    
    public function get_showdescription() {
        return $this->showdescription;
    }
}
