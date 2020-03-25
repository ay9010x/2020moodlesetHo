<?php





$userispivot = false;
$fullpivot = true;
$pivotkey = 'concept';

switch ($tab) {

    case GLOSSARY_AUTHOR_VIEW:
        $userispivot = true;
        $pivotkey = 'userid';
        $field = ($sortkey == 'LASTNAME' ? 'LASTNAME' : 'FIRSTNAME');
        list($allentries, $count) = glossary_get_entries_by_author($glossary, $context, $hook,
            $field, $sortorder, $offset, $pagelimit);
        unset($field);
        break;

    case GLOSSARY_CATEGORY_VIEW:
        $hook = (int) $hook;         list($allentries, $count) = glossary_get_entries_by_category($glossary, $context, $hook, $offset, $pagelimit);
        $pivotkey = 'categoryname';
        if ($hook != GLOSSARY_SHOW_ALL_CATEGORIES) {
            $printpivot = false;
        }
        break;

    case GLOSSARY_DATE_VIEW:
        $printpivot = false;
        $field = ($sortkey == 'CREATION' ? 'CREATION' : 'UPDATE');
        list($allentries, $count) = glossary_get_entries_by_date($glossary, $context, $field, $sortorder,
            $offset, $pagelimit);
        unset($field);
        break;

    case GLOSSARY_APPROVAL_VIEW:
        $fullpivot = false;
        $printpivot = false;
        list($allentries, $count) = glossary_get_entries_to_approve($glossary, $context, $hook, $sortkey, $sortorder,
            $offset, $pagelimit);
        break;

    case GLOSSARY_STANDARD_VIEW:
    default:
        $fullpivot = false;
        switch ($mode) {
            case 'search':
                list($allentries, $count) = glossary_get_entries_by_search($glossary, $context, $hook, $fullsearch,
                    $sortkey, $sortorder, $offset, $pagelimit);
                break;

            case 'term':
                $printpivot = false;
                list($allentries, $count) = glossary_get_entries_by_term($glossary, $context, $hook, $offset, $pagelimit);
                break;

            case 'entry':
                $printpivot = false;
                $entry = glossary_get_entry_by_id($hook);
                $canapprove = has_capability('mod/glossary:approve', $context);
                if ($entry && ($entry->glossaryid == $glossary->id || $entry->sourceglossaryid != $glossary->id)
                        && (!empty($entry->approved) || $entry->userid == $USER->id || $canapprove)) {
                    $count = 1;
                    $allentries = array($entry);
                } else {
                    $count = 0;
                    $allentries = array();
                }
                unset($entry, $canapprove);
                break;

            case 'letter':
            default:
                list($allentries, $count) = glossary_get_entries_by_letter($glossary, $context, $hook, $offset, $pagelimit);
                break;
        }
        break;
}
