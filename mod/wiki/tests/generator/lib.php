<?php



defined('MOODLE_INTERNAL') || die();


class mod_wiki_generator extends testing_module_generator {

    
    protected $pagecount = 0;

    
    public function reset() {
        $this->pagecount = 0;
        parent::reset();
    }

    public function create_instance($record = null, array $options = null) {
                $record = (array)$record + array(
            'wikimode' => 'collaborative',
            'firstpagetitle' => 'Front page for wiki '.($this->instancecount+1),
            'defaultformat' => 'html',
            'forceformat' => 0
        );

        return parent::create_instance($record, (array)$options);
    }

    public function create_content($wiki, $record = array()) {
        $record = (array)$record + array(
            'wikiid' => $wiki->id
        );
        return $this->create_page($wiki, $record);
    }

    public function create_first_page($wiki, $record = array()) {
        $record = (array)$record + array(
            'title' => $wiki->firstpagetitle,
        );
        return $this->create_page($wiki, $record);
    }

    
    public function create_page($wiki, $record = array()) {
        global $CFG, $USER;
        require_once($CFG->dirroot.'/mod/wiki/locallib.php');
        $this->pagecount++;
        $record = (array)$record + array(
            'title' => 'wiki page '.$this->pagecount,
            'wikiid' => $wiki->id,
            'subwikiid' => 0,
            'group' => 0,
            'content' => 'Wiki page content '.$this->pagecount,
            'format' => $wiki->defaultformat
        );
        if (empty($record['wikiid']) && empty($record['subwikiid'])) {
            throw new coding_exception('wiki page generator requires either wikiid or subwikiid');
        }
        if (!$record['subwikiid']) {
            if (!isset($record['userid'])) {
                $record['userid'] = ($wiki->wikimode == 'individual') ? $USER->id : 0;
            }
            if ($subwiki = wiki_get_subwiki_by_group($record['wikiid'], $record['group'], $record['userid'])) {
                $record['subwikiid'] = $subwiki->id;
            } else {
                $record['subwikiid'] = wiki_add_subwiki($record['wikiid'], $record['group'], $record['userid']);
            }
        }

        $wikipage = wiki_get_page_by_title($record['subwikiid'], $record['title']);
        if (!$wikipage) {
            $pageid = wiki_create_page($record['subwikiid'], $record['title'], $record['format'], $USER->id);
            $wikipage = wiki_get_page($pageid);
        }
        $rv = wiki_save_page($wikipage, $record['content'], $USER->id);

        if (array_key_exists('tags', $record)) {
            $tags = is_array($record['tags']) ? $record['tags'] : preg_split('/,/', $record['tags']);
            if (empty($wiki->cmid)) {
                $cm = get_coursemodule_from_instance('wiki', $wiki->id, isset($wiki->course) ? $wiki->course : 0);
                $wiki->cmid = $cm->id;
            }
            core_tag_tag::set_item_tags('mod_wiki', 'wiki_pages', $wikipage->id,
                    context_module::instance($wiki->cmid), $tags);
        }
        return $rv['page'];
    }
}
