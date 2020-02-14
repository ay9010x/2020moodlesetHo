<?php

function glossary_show_entry_continuous($course, $cm, $glossary, $entry, $mode='', $hook='', $printicons=1, $aliases=false) {

    global $USER;

    echo '<table class="glossarypost continuous" cellspacing="0">';
    echo '<tr valign="top">';
    echo '<td class="entry">';
    glossary_print_entry_approval($cm, $entry, $mode);
    echo '<div class="concept">';
    glossary_print_entry_concept($entry);
    echo '</div> ';
    glossary_print_entry_definition($entry, $glossary, $cm);
    glossary_print_entry_attachment($entry, $cm, 'html');
    $entry->alias = '';
    echo '</td></tr>';

    echo '<tr valign="top"><td class="entrylowersection">';
    glossary_print_entry_lower_section($course, $cm, $glossary, $entry, $mode, $hook, $printicons, $aliases);
    echo '</td>';
    echo '</tr>';
    echo "</table>\n";
}

function glossary_print_entry_continuous($course, $cm, $glossary, $entry, $mode='', $hook='', $printicons=1) {

    
        $entry->definition = '<span class="nolink">'.$entry->definition.'</span>';

        glossary_show_entry_continuous($course, $cm, $glossary, $entry, $mode, $hook, false, false, false);

}


