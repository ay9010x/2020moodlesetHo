<?php



function xmldb_block_quiz_results_install() {
    global $DB;

        $DB->set_field('block', 'visible', 0, array('name' => 'quiz_results'));
}

