<?php



namespace block_rss_client\output;

defined('MOODLE_INTERNAL') || die();


class block implements \renderable, \templatable {

    
    protected $feeds;

    
    public function __construct(array $feeds = array()) {
        $this->feeds = $feeds;
    }

    
    public function export_for_template(\renderer_base $output) {
        $data = array('feeds' => array());

        foreach ($this->feeds as $feed) {
            $data['feeds'][] = $feed->export_for_template($output);
        }

        return $data;
    }

    
    public function add_feed(feed $feed) {
        $this->feeds[] = $feed;

        return $this;
    }

    
    public function set_feeds(array $feeds) {
        $this->feeds = $feeds;

        return $this;
    }

    
    public function get_feeds() {
        return $this->feeds;
    }
}
