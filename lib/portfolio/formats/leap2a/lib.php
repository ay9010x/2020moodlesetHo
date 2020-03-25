<?php



defined('MOODLE_INTERNAL') || die();


class portfolio_format_leap2a_writer {

    
    private $dom;

    
    private $feed;

    
    private $user;

    
    private $id;

    
    private $entries = array();

    
    public function __construct(stdclass $user) {         global $CFG;
        $this->user = $user;
        $this->exporttime = time();
        $this->id = $CFG->wwwroot . '/portfolio/export/leap2a/' . $this->user->id . '/' . $this->exporttime;

        $this->dom = new DomDocument('1.0', 'utf-8');

        $this->feed = $this->dom->createElement('feed');
        $this->feed->setAttribute('xmlns', 'http://www.w3.org/2005/Atom');
        $this->feed->setAttribute('xmlns:rdf', 'http://www.w3.org/1999/02/22-rdf-syntax-ns#');
        $this->feed->setAttribute('xmlns:leap2', 'http://terms.leapspecs.org/');
        $this->feed->setAttribute('xmlns:categories', 'http://wiki.leapspecs.org/2A/categories');
        $this->feed->setAttribute('xmlns:portfolio', $this->id); 
        $this->dom->appendChild($this->feed);

        $this->feed->appendChild($this->dom->createElement('id', $this->id));
        $this->feed->appendChild($this->dom->createElement('title', get_string('leap2a_feedtitle', 'portfolio', fullname($this->user))));
        $this->feed->appendChild($this->dom->createElement('leap2:version', 'http://www.leapspecs.org/2010-07/2A/'));


        $generator = $this->dom->createElement('generator', 'Moodle');
        $generator->setAttribute('uri', $CFG->wwwroot);
        $generator->setAttribute('version', $CFG->version);

        $this->feed->appendChild($generator);

        $author = $this->dom->createElement('author');
        $author->appendChild($this->dom->createElement('name', fullname($this->user)));
        $author->appendChild($this->dom->createElement('email', $this->user->email));
        $author->appendChild($this->dom->CreateElement('uri', $CFG->wwwroot . '/user/view.php?id=' . $this->user->id));

        $this->feed->appendChild($author);
            }

    
    public function add_entry(portfolio_format_leap2a_entry $entry) {
        if (array_key_exists($entry->id, $this->entries)) {
            if (!($entry instanceof portfolio_format_leap2a_file)) {
                throw new portfolio_format_leap2a_exception('leap2a_entryalreadyexists', 'portfolio', '', $entry->id);
            }
        }
        $this->entries[$entry->id] =  $entry;
        return $entry;
    }

    
    public function make_selection($selectionentry, $ids, $selectiontype) {
        $selectionid = null;
        if ($selectionentry instanceof portfolio_format_leap2a_entry) {
            $selectionid = $selectionentry->id;
        } else if (is_string($selectionentry)) {
            $selectionid = $selectionentry;
        }
        if (!array_key_exists($selectionid, $this->entries)) {
            throw new portfolio_format_leap2a_exception('leap2a_invalidentryid', 'portfolio', '', $selectionid);
        }
        foreach ($ids as $entryid) {
            if (!array_key_exists($entryid, $this->entries)) {
                throw new portfolio_format_leap2a_exception('leap2a_invalidentryid', 'portfolio', '', $entryid);
            }
            $this->entries[$selectionid]->add_link($entryid, 'has_part');
            $this->entries[$entryid]->add_link($selectionid, 'is_part_of');
        }
        $this->entries[$selectionid]->add_category($selectiontype, 'selection_type');
        if ($this->entries[$selectionid]->type != 'selection') {
            debugging(get_string('leap2a_overwritingselection', 'portfolio', $this->entries[$selectionid]->type));
            $this->entries[$selectionid]->type = 'selection';
        }
    }

    
    public function link_files($entry, $files) {
        foreach ($files as $file) {
            $fileentry = new portfolio_format_leap2a_file($file->get_filename(), $file);
            $this->add_entry($fileentry);
            $entry->add_link($fileentry, 'related');
            $fileentry->add_link($entry, 'related');
        }
    }

    
    private function validate() {
        foreach ($this->entries as $entry) {
                                    $entry->validate();
                        foreach ($entry->links as $linkedid => $rel) {
                                if (!array_key_exists($linkedid, $this->entries)) {
                    $a = (object)array('rel' => $rel->type, 'to' => $linkedid, 'from' => $entry->id);
                    throw new portfolio_format_leap2a_exception('leap2a_nonexistantlink', 'portfolio', '', $a);
                }
                                if (!array_key_exists($entry->id, $this->entries[$linkedid]->links)) {

                }
                            }
        }
    }

    
    public function to_xml() {
        $this->validate();
        foreach ($this->entries as $entry) {
            $entry->id = 'portfolio:' . $entry->id;
            $this->feed->appendChild($entry->to_dom($this->dom, $this->user));
        }
        return $this->dom->saveXML();
    }
}


class portfolio_format_leap2a_entry {

    
    public $id;

    
    public $title;

    
    public $type;

    
    public $author;

    
    public $summary;

    
    public $content;

    
    public $updated;

    
    public $published;

    
    private $requiredfields = array( 'id', 'title', 'type');

    
    private $optionalfields = array('author', 'updated', 'published', 'content', 'summary');

    
    public $links       = array();

    
    public $attachments = array();

    
    private $categories = array();

    
    public function __construct($id, $title, $type, $content=null) {
        $this->id    = $id;
        $this->title = $title;
        $this->type  = $type;
        $this->content = $this->__set('content', $content);

    }

    
    public function __set($field, $value) {
                if ($field == 'content' && $value instanceof stored_file) {
            throw new portfolio_format_leap2a_exception('leap2a_filecontent', 'portfolio');
        }
        if (in_array($field, $this->requiredfields) || in_array($field, $this->optionalfields)) {
            return $this->{$field} = $value;
        }
        throw new portfolio_format_leap2a_exception('leap2a_invalidentryfield', 'portfolio', '', $field);
    }


    
    public function validate() {
        foreach ($this->requiredfields as $key) {
            if (empty($this->{$key})) {
                throw new portfolio_format_leap2a_exception('leap2a_missingfield', 'portfolio', '', $key);
            }
        }
        if ($this->type == 'selection') {
            if (count($this->links) == 0) {
                throw new portfolio_format_leap2a_exception('leap2a_emptyselection', 'portfolio');
            }
                    }
    }

    
    public function add_link($otherentry, $reltype, $displayorder=null) {
        if ($otherentry instanceof portfolio_format_leap2a_entry) {
            $otherentry = $otherentry->id;
        }
        if ($otherentry == $this->id) {
            throw new portfolio_format_leap2a_exception('leap2a_selflink', 'portfolio', '', (object)array('rel' => $reltype, 'id' => $this->id));
        }
                if (!in_array($reltype, array('related', 'alternate', 'enclosure'))) {
            $reltype = 'leap2:' . $reltype;
        }

        $this->links[$otherentry] = (object)array('rel' => $reltype, 'order' => $displayorder);

        return $this;
    }

    
    public function add_category($term, $scheme=null, $label=null) {
                        if (empty($scheme) && strpos($term, ' ') !== false) {
            $label = $term;
            $term = str_replace(' ', '-', $term);
        }
        $this->categories[] = (object)array(
            'term'   => $term,
            'scheme' => $scheme,
            'label'  => $label,
        );
    }

    
    public function to_dom(DomDocument $dom, $feedauthor) {
        $entry = $dom->createElement('entry');
        $entry->appendChild($dom->createElement('id', $this->id));
        $entry->appendChild($dom->createElement('title', $this->title));
        if ($this->author && $this->author->id != $feedauthor->id) {
            $author = $dom->createElement('author');
            $author->appendChild($dom->createElement('name', fullname($this->author)));
            $entry->appendChild($author);
        }
                foreach (array('updated', 'published') as $field) {
            if ($this->{$field}) {
                $date = date(DATE_ATOM, $this->{$field});
                $entry->appendChild($dom->createElement($field, $date));
            }
        }
        if (empty($this->content)) {
            $entry->appendChild($dom->createElement('content'));
        } else {
            $content = $this->create_xhtmlish_element($dom, 'content', $this->content);
            $entry->appendChild($content);
        }

        if (!empty($this->summary)) {
            $summary = $this->create_xhtmlish_element($dom, 'summary', $this->summary);
            $entry->appendChild($summary);
        }

        $type = $dom->createElement('rdf:type');
        $type->setAttribute('rdf:resource', 'leap2:' . $this->type);
        $entry->appendChild($type);

        foreach ($this->links as $otherentry => $l) {
            $link = $dom->createElement('link');
            $link->setAttribute('rel',  $l->rel);
            $link->setAttribute('href', 'portfolio:' . $otherentry);
            if ($l->order) {
                $link->setAttribute('leap2:display_order', $l->order);
            }
            $entry->appendChild($link);
        }

        $this->add_extra_links($dom, $entry); 
        foreach ($this->categories as $category) {
            $cat = $dom->createElement('category');
            $cat->setAttribute('term', $category->term);
            if ($category->scheme) {
                $cat->setAttribute('scheme', 'categories:' .$category->scheme . '#');
            }
            if ($category->label && $category->label != $category->term) {
                $cat->setAttribute('label', $category->label);
            }
            $entry->appendChild($cat);
        }
        return $entry;
    }

    
    private function create_xhtmlish_element(DomDocument $dom, $tagname, $content) {
        $topel = $dom->createElement($tagname);
        $maybexml = true;
        if (strpos($content, '<') === false && strpos($content, '>') === false) {
            $maybexml = false;
        }
                $tmp = new DomDocument();
        if ($maybexml && @$tmp->loadXML('<div>' . $content . '</div>')) {
            $topel->setAttribute('type', 'xhtml');
            $content = $dom->importNode($tmp->documentElement, true);
            $content->setAttribute('xmlns', 'http://www.w3.org/1999/xhtml');
            $topel->appendChild($content);
                } else if ($maybexml && @$tmp->loadHTML($content)) {
            $topel->setAttribute('type', 'html');
            $topel->nodeValue = $content;
                                                            } else {
            $topel->nodeValue = $content;
            $topel->setAttribute('type', 'text');
            return $topel;
        }
        return $topel;
    }

    
    protected function add_extra_links($dom, $entry) {}
}


class portfolio_format_leap2a_file extends portfolio_format_leap2a_entry {

    
    protected $referencedfile;

    
    public function __construct($title, stored_file $file) {
        $id = portfolio_format_leap2a::file_id_prefix() . $file->get_id();
        parent::__construct($id, $title, 'resource');
        $this->referencedfile = $file;
        $this->published = $this->referencedfile->get_timecreated();
        $this->updated = $this->referencedfile->get_timemodified();
        $this->add_category('offline', 'resource_type');
    }

    
    protected function add_extra_links($dom, $entry) {
        $link = $dom->createElement('link');
        $link->setAttribute('rel',  'enclosure');
        $link->setAttribute('href', portfolio_format_leap2a::get_file_directory() . $this->referencedfile->get_filename());
        $link->setAttribute('length', $this->referencedfile->get_filesize());
        $link->setAttribute('type', $this->referencedfile->get_mimetype());
        $entry->appendChild($link);
    }
}

