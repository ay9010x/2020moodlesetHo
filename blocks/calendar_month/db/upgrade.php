<?php




function xmldb_block_calendar_month_upgrade($oldversion, $block) {
    global $DB;

    if ($oldversion < 2014062600) {
                $blockname = 'calendar_month';

                        if ($systempage = $DB->get_record('my_pages', array('userid' => null, 'private' => 1))) {
            $page = new moodle_page();
            $page->set_context(context_system::instance());

                        $criteria = array(
                'blockname' => $blockname,
                'parentcontextid' => $page->context->id,
                'pagetypepattern' => 'my-index',
                'subpagepattern' => $systempage->id,
            );

            if (!$DB->record_exists('block_instances', $criteria)) {
                                $page->blocks->add_region(BLOCK_POS_RIGHT);
                $page->blocks->add_block($blockname, BLOCK_POS_RIGHT, 0, false, 'my-index', $systempage->id);
            }
        }

        upgrade_block_savepoint(true, 2014062600, $blockname);
    }

        
        
        
        
    return true;
}
