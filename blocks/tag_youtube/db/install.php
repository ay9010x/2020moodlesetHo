<?php




function xmldb_block_tag_youtube_install() {
    global $DB;

        $DB->set_field('block', 'visible', 0, array('name' => 'tag_youtube'));
}

