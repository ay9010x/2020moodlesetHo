<?php



namespace core\output;


class mustache_uniqid_helper {

    
    private $uniqid = null;

    
    public function __toString() {
        if ($this->uniqid === null) {
            $this->uniqid = \html_writer::random_id(uniqid());
        }
        return $this->uniqid;
    }
}
