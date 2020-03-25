<?php

function glossary_show_entry_faq($course, $cm, $glossary, $entry, $mode="", $hook="", $printicons=1, $aliases=true) {
    global $USER;
    if ( $entry ) {

        echo '<table class="glossarypost faq" cellspacing="0">';

        echo '<tr valign="top">';
        echo '<th class="entryheader">';
        $entry->course = $course->id;

        echo '<div class="concept">' . get_string('question','glossary') . ': ';
        glossary_print_entry_concept($entry);
        echo '</div>';

        echo '<span class="time">('.get_string('lastedited').': '.
             userdate($entry->timemodified).')</span>';
        echo '</th>';
        echo '<td class="entryattachment">';

        glossary_print_entry_approval($cm, $entry, $mode);
        echo '</td>';

        echo '</tr>';

        echo "\n<tr>";
        echo '<td colspan="2" class="entry">';
        echo '<b>'.get_string('answer','glossary').':</b> ';

        glossary_print_entry_definition($entry, $glossary, $cm);
        glossary_print_entry_attachment($entry, $cm, 'html');

        echo '</td></tr>';
        echo '<tr valign="top"><td colspan="3" class="entrylowersection">';
        glossary_print_entry_lower_section($course, $cm, $glossary, $entry, $mode, $hook, $printicons, $aliases);
        echo '</td></tr></table>';

    } else {
        echo '<div style="text-align:center">';
        print_string('noentry', 'glossary');
        echo '</div>';
    }
}

function glossary_print_entry_faq($course, $cm, $glossary, $entry, $mode='', $hook='', $printicons=1) {

    
        $entry->definition = '<span class="nolink">'.$entry->definition.'</span>';

        return glossary_show_entry_faq($course, $cm, $glossary, $entry, $mode, $hook, false, false, false);

}


