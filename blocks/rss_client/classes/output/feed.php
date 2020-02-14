<?php



namespace block_rss_client\output;

defined('MOODLE_INTERNAL') || die();


class feed implements \renderable, \templatable {

    
    protected $title = null;

    
    protected $items = array();

    
    protected $image = null;

    
    protected $showtitle;

    
    protected $showimage;

    
    public function __construct($title, $showtitle = true, $showimage = true) {
        $this->title = $title;
        $this->showtitle = $showtitle;
        $this->showimage = $showimage;
    }

    
    public function export_for_template(\renderer_base $output) {
        $data = array(
            'title' => $this->showtitle ? $this->title : null,
            'image' => null,
            'items' => array(),
        );

        if ($this->showimage && $this->image) {
            $data['image'] = $this->image->export_for_template($output);
        }

        foreach ($this->items as $item) {
            $data['items'][] = $item->export_for_template($output);
        }

        return $data;
    }

    
    public function set_title($title) {
        $this->title = $title;

        return $this;
    }

    
    public function get_title() {
        return $this->title;
    }

    
    public function add_item(item $item) {
        $this->items[] = $item;

        return $this;
    }

    
    public function set_items(array $items) {
        $this->items = $items;

        return $this;
    }

    
    public function get_items() {
        return $this->items;
    }

    
    public function set_image(channel_image $image) {
        $this->image = $image;
    }

    
    public function get_image() {
        return $this->image;
    }

    
    public function set_showtitle($showtitle) {
        $this->showtitle = boolval($showtitle);

        return $this;
    }

    
    public function get_showtitle() {
        return $this->showtitle;
    }

    
    public function set_showimage($showimage) {
        $this->showimage = boolval($showimage);

        return $this;
    }

    
    public function get_showimage() {
        return $this->showimage;
    }
}
