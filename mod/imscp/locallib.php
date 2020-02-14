<?php



defined('MOODLE_INTERNAL') || die();

require_once("$CFG->dirroot/mod/imscp/lib.php");
require_once("$CFG->libdir/filelib.php");
require_once("$CFG->libdir/resourcelib.php");


function imscp_print_content($imscp, $cm, $course) {
    global $PAGE, $CFG;

    $items = unserialize($imscp->structure);
    $first = reset($items);
    $context = context_module::instance($cm->id);
    $urlbase = "$CFG->wwwroot/pluginfile.php";
    $path = '/'.$context->id.'/mod_imscp/content/'.$imscp->revision.'/'.$first['href'];
    $firsturl = file_encode_url($urlbase, $path, false);

    echo '<div id="imscp_layout">';
    echo '<div id="imscp_toc">';
    echo '<div id="imscp_tree"><ul>';
    foreach ($items as $item) {
        echo imscp_htmllize_item($item, $imscp, $cm);
    }
    echo '</ul></div>';
    echo '<div id="imscp_nav" style="display:none">';
    echo '<button id="nav_skipprev">&lt;&lt;</button><button id="nav_prev">&lt;</button><button id="nav_up">^</button>';
    echo '<button id="nav_next">&gt;</button><button id="nav_skipnext">&gt;&gt;</button>';
    echo '</div>';
    echo '</div>';
    echo '</div>';

    $PAGE->requires->js_init_call('M.mod_imscp.init');
    return;
}


function imscp_htmllize_item($item, $imscp, $cm) {
    global $CFG;

    if ($item['href']) {
        if (preg_match('|^https?://|', $item['href'])) {
            $url = $item['href'];
        } else {
            $context = context_module::instance($cm->id);
            $urlbase = "$CFG->wwwroot/pluginfile.php";
            $path = '/'.$context->id.'/mod_imscp/content/'.$imscp->revision.'/'.$item['href'];
            $url = file_encode_url($urlbase, $path, false);
        }
        $result = "<li><a href=\"$url\">".$item['title'].'</a>';
    } else {
        $result = '<li>'.$item['title'];
    }
    if ($item['subitems']) {
        $result .= '<ul>';
        foreach ($item['subitems'] as $subitem) {
            $result .= imscp_htmllize_item($subitem, $imscp, $cm);
        }
        $result .= '</ul>';
    }
    $result .= '</li>';

    return $result;
}


function imscp_parse_structure($imscp, $context) {
    $fs = get_file_storage();

    if (!$manifestfile = $fs->get_file($context->id, 'mod_imscp', 'content', $imscp->revision, '/', 'imsmanifest.xml')) {
        return null;
    }

    return imscp_parse_manifestfile($manifestfile->get_content(), $imscp, $context);
}


function imscp_parse_manifestfile($manifestfilecontents, $imscp, $context) {
    $doc = new DOMDocument();
    $oldentities = libxml_disable_entity_loader(true);
    if (!$doc->loadXML($manifestfilecontents, LIBXML_NONET)) {
        return null;
    }
    libxml_disable_entity_loader($oldentities);

        $doc->documentURI = 'http://grrr/';

    $xmlorganizations = $doc->getElementsByTagName('organizations');
    if (empty($xmlorganizations->length)) {
        return null;
    }
    $default = null;
    if ($xmlorganizations->item(0)->attributes->getNamedItem('default')) {
        $default = $xmlorganizations->item(0)->attributes->getNamedItem('default')->nodeValue;
    }
    $xmlorganization = $doc->getElementsByTagName('organization');
    if (empty($xmlorganization->length)) {
        return null;
    }
    $organization = null;
    foreach ($xmlorganization as $org) {
        if (is_null($organization)) {
                        $organization = $org;
        }
        if (!$org->attributes->getNamedItem('identifier')) {
            continue;
        }
        if ($default === $org->attributes->getNamedItem('identifier')->nodeValue) {
                        $organization = $org;
            break;
        }
    }

        $resources = array();

    $xmlresources = $doc->getElementsByTagName('resource');
    foreach ($xmlresources as $res) {
        if (!$identifier = $res->attributes->getNamedItem('identifier')) {
            continue;
        }
        $identifier = $identifier->nodeValue;
        if ($xmlbase = $res->baseURI) {
                        $xmlbase = str_replace('http://grrr/', '/', $xmlbase);
            $xmlbase = rtrim($xmlbase, '/').'/';
        } else {
            $xmlbase = '';
        }
        if (!$href = $res->attributes->getNamedItem('href')) {
                        $fileresources = $res->getElementsByTagName('file');
            foreach ($fileresources as $file) {
                $href = $file->getAttribute('href');
            }
            if (pathinfo($href, PATHINFO_EXTENSION) == 'xml') {
                $href = imscp_recursive_href($href, $imscp, $context);
            }
            if (empty($href)) {
                continue;
            }
        } else {
            $href = $href->nodeValue;
        }
        if (strpos($href, 'http://') !== 0) {
            $href = $xmlbase.$href;
        }
                $href = ltrim(strtr($href, "\\", '/'), '/');
        $resources[$identifier] = $href;
    }

    $items = array();
    foreach ($organization->childNodes as $child) {
        if ($child->nodeName === 'item') {
            if (!$item = imscp_recursive_item($child, 0, $resources)) {
                continue;
            }
            $items[] = $item;
        }
    }

    return $items;
}

function imscp_recursive_href($manifestfilename, $imscp, $context) {
    $fs = get_file_storage();

    $dirname = dirname($manifestfilename);
    $filename = basename($manifestfilename);

    if ($dirname !== '/') {
        $dirname = "/$dirname/";
    }

    if (!$manifestfile = $fs->get_file($context->id, 'mod_imscp', 'content', $imscp->revision, $dirname, $filename)) {
        return null;
    }

    $doc = new DOMDocument();
    $oldentities = libxml_disable_entity_loader(true);
    if (!$doc->loadXML($manifestfile->get_content(), LIBXML_NONET)) {
        return null;
    }
    libxml_disable_entity_loader($oldentities);

    $xmlresources = $doc->getElementsByTagName('resource');
    foreach ($xmlresources as $res) {
        if (!$href = $res->attributes->getNamedItem('href')) {
            $fileresources = $res->getElementsByTagName('file');
            foreach ($fileresources as $file) {
                $href = $file->getAttribute('href');
                if (pathinfo($href, PATHINFO_EXTENSION) == 'xml') {
                    $href = imscp_recursive_href($href, $imscp, $context);
                }

                if (pathinfo($href, PATHINFO_EXTENSION) == 'htm' || pathinfo($href, PATHINFO_EXTENSION) == 'html') {
                    return $href;
                }
            }
        }
    }

    return $href;
}

function imscp_recursive_item($xmlitem, $level, $resources) {
    $identifierref = '';
    if ($identifierref = $xmlitem->attributes->getNamedItem('identifierref')) {
        $identifierref = $identifierref->nodeValue;
    }

    $title = '?';
    $subitems = array();

    foreach ($xmlitem->childNodes as $child) {
        if ($child->nodeName === 'title') {
            $title = $child->textContent;

        } else if ($child->nodeName === 'item') {
            if ($subitem = imscp_recursive_item($child, $level + 1, $resources)) {
                $subitems[] = $subitem;
            }
        }
    }

    return array('href'     => isset($resources[$identifierref]) ? $resources[$identifierref] : '',
                 'title'    => $title,
                 'level'    => $level,
                 'subitems' => $subitems,
                );
}


class imscp_file_info extends file_info {
    protected $course;
    protected $cm;
    protected $areas;
    protected $filearea;

    public function __construct($browser, $course, $cm, $context, $areas, $filearea) {
        parent::__construct($browser, $context);
        $this->course   = $course;
        $this->cm       = $cm;
        $this->areas    = $areas;
        $this->filearea = $filearea;
    }

    
    public function get_params() {
        return array('contextid' => $this->context->id,
                     'component' => 'mod_imscp',
                     'filearea'  => $this->filearea,
                     'itemid'    => null,
                     'filepath'  => null,
                     'filename'  => null);
    }

    
    public function get_visible_name() {
        return $this->areas[$this->filearea];
    }

    
    public function is_writable() {
        return false;
    }

    
    public function is_directory() {
        return true;
    }

    
    public function get_children() {
        return $this->get_filtered_children('*', false, true);
    }

    
    private function get_filtered_children($extensions = '*', $countonly = false, $returnemptyfolders = false) {
        global $DB;
        $params = array('contextid' => $this->context->id,
            'component' => 'mod_imscp',
            'filearea' => $this->filearea);
        $sql = 'SELECT DISTINCT itemid
                    FROM {files}
                    WHERE contextid = :contextid
                    AND component = :component
                    AND filearea = :filearea';
        if (!$returnemptyfolders) {
            $sql .= ' AND filename <> :emptyfilename';
            $params['emptyfilename'] = '.';
        }
        list($sql2, $params2) = $this->build_search_files_sql($extensions);
        $sql .= ' '.$sql2;
        $params = array_merge($params, $params2);
        if ($countonly !== false) {
            $sql .= ' ORDER BY itemid';
        }

        $rs = $DB->get_recordset_sql($sql, $params);
        $children = array();
        foreach ($rs as $record) {
            if ($child = $this->browser->get_file_info($this->context, 'mod_imscp', $this->filearea, $record->itemid)) {
                $children[] = $child;
                if ($countonly !== false && count($children) >= $countonly) {
                    break;
                }
            }
        }
        $rs->close();
        if ($countonly !== false) {
            return count($children);
        }
        return $children;
    }

    
    public function get_non_empty_children($extensions = '*') {
        return $this->get_filtered_children($extensions, false);
    }

    
    public function count_non_empty_children($extensions = '*', $limit = 1) {
        return $this->get_filtered_children($extensions, $limit);
    }

    
    public function get_parent() {
        return $this->browser->get_file_info($this->context);
    }
}
