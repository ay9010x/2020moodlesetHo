<?php



namespace block_rss_client\output;

defined('MOODLE_INTERNAL') || die();


class renderer extends \plugin_renderer_base {

    
    public function render_item(\templatable $item) {
        $data = $item->export_for_template($this);

        return $this->render_from_template('block_rss_client/item', $data);
    }

    
    public function render_feed(\templatable $feed) {
        $data = $feed->export_for_template($this);

        return $this->render_from_template('block_rss_client/feed', $data);
    }

    
    public function render_block(\templatable $block) {
        $data = $block->export_for_template($this);

        return $this->render_from_template('block_rss_client/block', $data);
    }

    
    public function render_footer(\templatable $footer) {
        $data = $footer->export_for_template($this);

        return $this->render_from_template('block_rss_client/footer', $data);
    }

    
    public function format_published_date($timestamp) {
        return strftime(get_string('strftimerecentfull', 'langconfig'), $timestamp);
        return date('j F Y, g:i a', $timestamp);
    }

    
    public function format_title($title) {
        return break_up_long_words($title, 30);
    }

    
    public function format_description($description) {
        $description = format_text($description, FORMAT_HTML, array('para' => false));
        $description = break_up_long_words($description, 30);

        return $description;
    }
}
