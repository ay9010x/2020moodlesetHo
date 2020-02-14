<?php



namespace block_rss_client\output;

defined('MOODLE_INTERNAL') || die();


class footer implements \renderable, \templatable {

    
    protected $channelurl;

    
    public function __construct(\moodle_url $channelurl) {
        $this->channelurl = $channelurl;
    }

    
    public function set_channelurl(\moodle_url $channelurl) {
        $this->channelurl = $channelurl;

        return $this;
    }

    
    public function get_channelurl() {
        return $this->channelurl;
    }

    
    public function export_for_template(\renderer_base $output) {
        $data = new \stdClass();
        $data->channellink = clean_param($this->channelurl, PARAM_URL);

        return $data;
    }
}
