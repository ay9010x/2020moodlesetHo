<?php

require_once("../../config.php");
require_once("lib.php");
require_once($CFG->libdir . '/completionlib.php');
require_once("$CFG->libdir/rsslib.php");

$id = optional_param('id', 0, PARAM_INT);           $g  = optional_param('g', 0, PARAM_INT);            
$tab  = optional_param('tab', GLOSSARY_NO_VIEW, PARAM_ALPHA);    $displayformat = optional_param('displayformat',-1, PARAM_INT);  
$mode       = optional_param('mode', '', PARAM_ALPHA);           $hook       = optional_param('hook', '', PARAM_CLEAN);           $fullsearch = optional_param('fullsearch', 0,PARAM_INT);         $sortkey    = optional_param('sortkey', '', PARAM_ALPHA);$sortorder  = optional_param('sortorder', 'ASC', PARAM_ALPHA);   $offset     = optional_param('offset', 0,PARAM_INT);             $page       = optional_param('page', 0,PARAM_INT);               $show       = optional_param('show', '', PARAM_ALPHA);           
if (!empty($id)) {
    if (! $cm = get_coursemodule_from_id('glossary', $id)) {
        print_error('invalidcoursemodule');
    }
    if (! $course = $DB->get_record("course", array("id"=>$cm->course))) {
        print_error('coursemisconf');
    }
    if (! $glossary = $DB->get_record("glossary", array("id"=>$cm->instance))) {
        print_error('invalidid', 'glossary');
    }

} else if (!empty($g)) {
    if (! $glossary = $DB->get_record("glossary", array("id"=>$g))) {
        print_error('invalidid', 'glossary');
    }
    if (! $course = $DB->get_record("course", array("id"=>$glossary->course))) {
        print_error('invalidcourseid');
    }
    if (!$cm = get_coursemodule_from_instance("glossary", $glossary->id, $course->id)) {
        print_error('invalidcoursemodule');
    }
    $id = $cm->id;
} else {
    print_error('invalidid', 'glossary');
}

require_course_login($course->id, true, $cm);
$context = context_module::instance($cm->id);
require_capability('mod/glossary:view', $context);

$fmtoptions = array(
    'context' => $context);

require_once($CFG->dirroot . '/comment/lib.php');
comment::init();

if ($tab == GLOSSARY_ADDENTRY_VIEW ) {
    redirect("edit.php?cmid=$cm->id&amp;mode=$mode");
}

if ( !$entriesbypage = $glossary->entbypage ) {
    $entriesbypage = $CFG->glossary_entbypage;
}

$pagelimit = $entriesbypage;
if ($page > 0 && $offset == 0) {
    $offset = $page * $entriesbypage;
} else if ($page < 0) {
    $offset = 0;
    $pagelimit = 0;
}

if ( $dp = $DB->get_record('glossary_formats', array('name'=>$glossary->displayformat)) ) {
    $showtabs = glossary_get_visible_tabs($dp);
    switch ($dp->defaultmode) {
        case 'cat':
            $defaulttab = GLOSSARY_CATEGORY_VIEW;

                        if (!in_array(GLOSSARY_CATEGORY, $showtabs)) {
                $defaulttab = GLOSSARY_STANDARD_VIEW;
            }

            break;
        case 'date':
            $defaulttab = GLOSSARY_DATE_VIEW;

                        if (!in_array(GLOSSARY_DATE, $showtabs)) {
                $defaulttab = GLOSSARY_STANDARD_VIEW;
            }

            break;
        case 'author':
            $defaulttab = GLOSSARY_AUTHOR_VIEW;

                        if (!in_array(GLOSSARY_AUTHOR, $showtabs)) {
                $defaulttab = GLOSSARY_STANDARD_VIEW;
            }

            break;
        default:
            $defaulttab = GLOSSARY_STANDARD_VIEW;
    }
    $printpivot = $dp->showgroup;
    if ( $mode == '' and $hook == '' and $show == '') {
        $mode      = $dp->defaultmode;
        $hook      = $dp->defaulthook;
        $sortkey   = $dp->sortkey;
        $sortorder = $dp->sortorder;
    }
} else {
    $defaulttab = GLOSSARY_STANDARD_VIEW;
    $showtabs = array($defaulttab);
    $printpivot = 1;
    if ( $mode == '' and $hook == '' and $show == '') {
        $mode = 'letter';
        $hook = 'ALL';
    }
}

if ( $displayformat == -1 ) {
     $displayformat = $glossary->displayformat;
}

if ( $show ) {
    $mode = 'term';
    $hook = $show;
    $show = '';
}

if ( $sortorder = strtolower($sortorder) ) {
    if ($sortorder != 'asc' and $sortorder != 'desc') {
        $sortorder = '';
    }
}
if ( $sortkey = strtoupper($sortkey) ) {
    if ($sortkey != 'CREATION' and
        $sortkey != 'UPDATE' and
        $sortkey != 'FIRSTNAME' and
        $sortkey != 'LASTNAME'
        ) {
        $sortkey = '';
    }
}

switch ( $mode = strtolower($mode) ) {
case 'search':     $tab = GLOSSARY_STANDARD_VIEW;

        $hook = trim(strip_tags($hook));

break;

case 'entry':      $tab = GLOSSARY_STANDARD_VIEW;
    if ( $dp = $DB->get_record("glossary_formats", array("name"=>$glossary->displayformat)) ) {
        $displayformat = $dp->popupformatname;
    }
break;

case 'cat':        $tab = GLOSSARY_CATEGORY_VIEW;

        if (!in_array(GLOSSARY_CATEGORY, $showtabs)) {
        $tab = GLOSSARY_STANDARD_VIEW;
    }

    if ( $hook > 0 ) {
        $category = $DB->get_record("glossary_categories", array("id"=>$hook));
    }
break;

case 'approval':        $tab = GLOSSARY_APPROVAL_VIEW;
        if ($glossary->approvaldisplayformat !== 'default' && ($df = $DB->get_record("glossary_formats",
            array("name" => $glossary->approvaldisplayformat)))) {
        $displayformat = $df->popupformatname;
    }
    if ( !$hook and !$sortkey and !$sortorder) {
        $hook = 'ALL';
    }
break;

case 'term':       $tab = GLOSSARY_STANDARD_VIEW;
break;

case 'date':
    $tab = GLOSSARY_DATE_VIEW;

        if (!in_array(GLOSSARY_DATE, $showtabs)) {
        $tab = GLOSSARY_STANDARD_VIEW;
    }

    if ( !$sortkey ) {
        $sortkey = 'UPDATE';
    }
    if ( !$sortorder ) {
        $sortorder = 'desc';
    }
break;

case 'author':      $tab = GLOSSARY_AUTHOR_VIEW;

        if (!in_array(GLOSSARY_AUTHOR, $showtabs)) {
        $tab = GLOSSARY_STANDARD_VIEW;
    }

    if ( !$hook ) {
        $hook = 'ALL';
    }
    if ( !$sortkey ) {
        $sortkey = 'FIRSTNAME';
    }
    if ( !$sortorder ) {
        $sortorder = 'asc';
    }
break;

case 'letter':  default:
    $tab = GLOSSARY_STANDARD_VIEW;
    if ( !$hook ) {
        $hook = 'ALL';
    }
break;
}

switch ( $tab ) {
case GLOSSARY_IMPORT_VIEW:
case GLOSSARY_EXPORT_VIEW:
case GLOSSARY_APPROVAL_VIEW:
    $showcommonelements = 0;
break;

default:
    $showcommonelements = 1;
break;
}

glossary_view($glossary, $course, $cm, $context, $mode);

$strglossaries = get_string("modulenameplural", "glossary");
$strglossary = get_string("modulename", "glossary");
$strallcategories = get_string("allcategories", "glossary");
$straddentry = get_string("addentry", "glossary");
$strnoentries = get_string("noentries", "glossary");
$strsearchindefinition = get_string("searchindefinition", "glossary");
$strsearch = get_string("search");
$strwaitingapproval = get_string('waitingapproval', 'glossary');

$PAGE->set_title($glossary->name);
$PAGE->set_heading($course->fullname);
$url = new moodle_url('/mod/glossary/view.php', array('id'=>$cm->id));
if (isset($mode)) {
    $url->param('mode', $mode);
}
$PAGE->set_url($url);

if (!empty($CFG->enablerssfeeds) && !empty($CFG->glossary_enablerssfeeds)
    && $glossary->rsstype && $glossary->rssarticles) {

    $rsstitle = format_string($course->shortname, true, array('context' => context_course::instance($course->id))) . ': '. format_string($glossary->name);
    rss_add_http_header($context, 'mod_glossary', $glossary, $rsstitle);
}

if ($tab == GLOSSARY_APPROVAL_VIEW) {
    require_capability('mod/glossary:approve', $context);
    $PAGE->navbar->add($strwaitingapproval);
    echo $OUTPUT->header();
    echo $OUTPUT->heading($strwaitingapproval);
} else {     echo $OUTPUT->header();
}
echo $OUTPUT->heading(format_string($glossary->name), 2);

if ($showcommonelements) {
    $availableoptions = '';

    

    if (has_capability('mod/glossary:approve', $context)) {
            if ($hiddenentries = $DB->count_records('glossary_entries', array('glossaryid'=>$glossary->id, 'approved'=>0))) {
            if ($availableoptions) {
                $availableoptions .= '<br />';
            }
            $availableoptions .='<span class="helplink">' .
                                '<a href="' . $CFG->wwwroot . '/mod/glossary/view.php?id=' . $cm->id .
                                '&amp;mode=approval' . '"' .
                                '  title="' . s(get_string('waitingapproval', 'glossary')) . '">' .
                                get_string('waitingapproval', 'glossary') . ' ('.$hiddenentries.')</a>' .
                                '</span>';
        }
    }

    echo '<div class="glossarycontrol" style="text-align: right">';
    echo $availableoptions;

    if ( $showcommonelements and $mode != 'search') {
        if (has_capability('mod/glossary:manageentries', $context) or $glossary->allowprintview) {
            $params = array(
                'id'        => $cm->id,
                'mode'      => $mode,
                'hook'      => $hook,
                'sortkey'   => $sortkey,
                'sortorder' => $sortorder,
                'offset'    => $offset,
                'pagelimit' => $pagelimit
            );
            $printurl = new moodle_url('/mod/glossary/print.php', $params);
            $printtitle = get_string('printerfriendly', 'glossary');
            $printattributes = array(
                'class' => 'printicon',
                'title' => $printtitle
            );
            echo html_writer::link($printurl, $printtitle, $printattributes);
        }
    }
    echo '</div><br />';

}

if ($glossary->intro && $showcommonelements) {
    echo $OUTPUT->box(format_module_intro('glossary', $glossary, $cm->id), 'generalbox', 'intro');
}

if ($showcommonelements ) {
    echo '<form method="post" action="view.php">';

    echo '<table class="boxaligncenter" width="70%" border="0">';
    echo '<tr><td align="center" class="glossarysearchbox">';

    echo '<input type="submit" value="'.$strsearch.'" name="searchbutton" /> ';
    if ($mode == 'search') {
        echo '<input type="text" name="hook" size="20" value="'.s($hook).'" alt="'.$strsearch.'" /> ';
    } else {
        echo '<input type="text" name="hook" size="20" value="" alt="'.$strsearch.'" /> ';
    }
    if ($fullsearch || $mode != 'search') {
        $fullsearchchecked = 'checked="checked"';
    } else {
        $fullsearchchecked = '';
    }
    echo '<input type="checkbox" name="fullsearch" id="fullsearch" value="1" '.$fullsearchchecked.' />';
    echo '<input type="hidden" name="mode" value="search" />';
    echo '<input type="hidden" name="id" value="'.$cm->id.'" />';
    echo '<label for="fullsearch">'.$strsearchindefinition.'</label>';
    echo '</td></tr></table>';

    echo '</form>';

    echo '<br />';
}

if (has_capability('mod/glossary:write', $context) && $showcommonelements ) {
    echo '<div class="singlebutton glossaryaddentry">';
    echo "<form id=\"newentryform\" method=\"get\" action=\"$CFG->wwwroot/mod/glossary/edit.php\">";
    echo '<div>';
    echo "<input type=\"hidden\" name=\"cmid\" value=\"$cm->id\" />";
    echo '<input type="submit" value="'.get_string('addentry', 'glossary').'" />';
    echo '</div>';
    echo '</form>';
    echo "</div>\n";
}

echo '<br />';

require("tabs.php");

require("sql.php");

$entriesshown = 0;
$currentpivot = '';
$paging = NULL;

if ($allentries) {

        $specialtext = '';
    if ($glossary->showall) {
        $specialtext = get_string("allentries","glossary");
    }

        $paging = glossary_get_paging_bar($count, $page, $entriesbypage, "view.php?id=$id&amp;mode=$mode&amp;hook=".urlencode($hook)."&amp;sortkey=$sortkey&amp;sortorder=$sortorder&amp;fullsearch=$fullsearch&amp;",9999,10,'&nbsp;&nbsp;', $specialtext, -1);

    echo '<div class="paging">';
    echo $paging;
    echo '</div>';

        require_once($CFG->dirroot.'/rating/lib.php');
    if ($glossary->assessed != RATING_AGGREGATE_NONE) {
        $ratingoptions = new stdClass;
        $ratingoptions->context = $context;
        $ratingoptions->component = 'mod_glossary';
        $ratingoptions->ratingarea = 'entry';
        $ratingoptions->items = $allentries;
        $ratingoptions->aggregate = $glossary->assessed;        $ratingoptions->scaleid = $glossary->scale;
        $ratingoptions->userid = $USER->id;
        $ratingoptions->returnurl = $CFG->wwwroot.'/mod/glossary/view.php?id='.$cm->id;
        $ratingoptions->assesstimestart = $glossary->assesstimestart;
        $ratingoptions->assesstimefinish = $glossary->assesstimefinish;

        $rm = new rating_manager();
        $allentries = $rm->get_ratings($ratingoptions);
    }

    foreach ($allentries as $entry) {

                if ($printpivot) {
            $pivot = $entry->{$pivotkey};
            $upperpivot = core_text::strtoupper($pivot);
            $pivottoshow = core_text::strtoupper(format_string($pivot, true, $fmtoptions));

                        if (!$fullpivot) {
                $upperpivot = core_text::substr($upperpivot, 0, 1);
                $pivottoshow = core_text::substr($pivottoshow, 0, 1);
            }

                        if ($currentpivot != $upperpivot) {
                $currentpivot = $upperpivot;

                
                echo '<div>';
                echo '<table cellspacing="0" class="glossarycategoryheader">';

                echo '<tr>';
                if ($userispivot) {
                                    echo '<th align="left">';
                    $user = mod_glossary_entry_query_builder::get_user_from_record($entry);
                    echo $OUTPUT->user_picture($user, array('courseid'=>$course->id));
                    $pivottoshow = fullname($user, has_capability('moodle/site:viewfullnames', context_course::instance($course->id)));
                } else {
                    echo '<th >';
                }

                echo $OUTPUT->heading($pivottoshow, 3);
                echo "</th></tr></table></div>\n";
            }
        }

                if ($mode == 'search') {
                                    $searchterms = explode(' ', $hook);                foreach ($searchterms as $key => $searchterm) {
                if (preg_match('/^\-/',$searchterm)) {
                    unset($searchterms[$key]);
                } else {
                    $searchterms[$key] = preg_replace('/^\+/','',$searchterm);
                }
                                if (strlen($searchterm) < 2) {
                    unset($searchterms[$key]);
                }
            }
            $strippedsearch = implode(' ', $searchterms);                $entry->highlight = $strippedsearch;
        }

                glossary_print_entry($course, $cm, $glossary, $entry, $mode, $hook,1,$displayformat);
        $entriesshown++;
    }
}
if ( !$entriesshown ) {
    echo $OUTPUT->box(get_string("noentries","glossary"), "generalbox boxaligncenter boxwidthwide");
}

if (!empty($formsent)) {
        echo "</div>";
    echo "</form>";
}

if ( $paging ) {
    echo '<hr />';
    echo '<div class="paging">';
    echo $paging;
    echo '</div>';
}
echo '<br />';
glossary_print_tabbed_table_end();

echo $OUTPUT->footer();