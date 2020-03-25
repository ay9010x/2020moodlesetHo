<?php

function glossary_show_entry_TEMPLATE($course, $cm, $glossary, $entry, $mode='', $hook='', $printicons=1, $aliases=true) {
    global $CFG, $USER, $DB, $OUTPUT;


    $user = $DB->get_record('user', array('id'=>$entry->userid));
    $strby = get_string('writtenby', 'glossary');

    if ($entry) {

        echo '<table class="glossarypost TEMPLATE">';
        echo '<tr>';
        echo '<td class="entryheader">';

                        echo $OUTPUT->user_picture($user, array('courseid'=>$course->id));

                echo '<br />';

                        $fullname = fullname($user);
        $by = new stdClass();
        $by->name = '<a href="'.$CFG->wwwroot.'/user/view.php?id='.$user->id.'&amp;course='.$course->id.'">'.$fullname.'</a>';
        $by->date = userdate($entry->timemodified);
        echo '<span class="author">'.get_string('bynameondate', 'forum', $by).'</span>' . '<br />';

                        echo get_string('lastedited').': '. userdate($entry->timemodified) . '<br /></span>';

                                $approvalalign = 'right';                 $approvalinsidetable = true;                 glossary_print_entry_approval($cm, $entry, $mode, $approvalalign, $approvalinsidetable);

                echo '<br />';

        echo '</td>';

        echo '<td class="entryattachment">';

                echo "<br />\n";

        echo '</td></tr>';

        echo '<tr valign="top">';
        echo '<td class="entry">';

                        glossary_print_entry_concept($entry);

                
                        glossary_print_entry_definition($entry, $glossary, $cm);

                glossary_print_entry_attachment($entry, $cm, 'html');

                echo "<br />\n";

                                                                        glossary_print_entry_lower_section($course, $cm, $glossary, $entry, $mode, $hook, $printicons, $aliases);

        echo '</td>';
        echo '</tr>';
        echo "</table>\n";
    } else {
        echo '<div style="text-align:center">';
        print_string('noentry', 'glossary');
        echo '</div>';
    }
}

function glossary_print_entry_TEMPLATE($course, $cm, $glossary, $entry, $mode='', $hook='', $printicons=1) {

        
        $entry->definition = '<span class="nolink">'.$entry->definition.'</span>';

        return glossary_show_entry_TEMPLATE($course, $cm, $glossary, $entry, $mode, $hook, false, false, false);

}


