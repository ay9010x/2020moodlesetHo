<?php



namespace tool_lpmigrate;
defined('MOODLE_INTERNAL') || die();

use core_competency\api;


class framework_mapper {

    
    protected $from;
    
    protected $to;
    
    protected $collectionfrom;
    
    protected $collectionto;
    
    protected $mappings = array();

    
    public function __construct($from, $to) {
        $this->from = $from;
        $this->to = $to;
    }

    
    public function add_mapping($idfrom, $idto) {
        $this->mappings[$idfrom] = $idto;
    }

    
    public function automap() {
        $map = array();

                $collectionfrom = $this->get_collection_from();
        $collectionto = $this->get_collection_to();

                foreach ($collectionfrom as $keyfrom => $compfrom) {
            foreach ($collectionto as $keyto => $compto) {
                if ($compfrom->get_idnumber() == $compto->get_idnumber()) {
                    $map[$compfrom->get_id()] = $compto->get_id();
                    unset($collectionfrom[$keyfrom]);
                    unset($collectionto[$keyto]);
                    break;
                }
            }
        }

        $this->mappings = $map;
    }

    
    public function get_all_from() {
        return array_keys($this->get_collection_from());
    }

    
    public function get_all_to() {
        return array_keys($this->get_collection_to());
    }

    
    protected function get_collection_from() {
        if ($this->collectionfrom === null) {
            $this->collectionfrom = api::search_competencies('', $this->from);
        }
        return $this->collectionfrom;
    }

    
    protected function get_collection_to() {
        if ($this->collectionto === null) {
            $this->collectionto = api::search_competencies('', $this->to);
        }
        return $this->collectionto;
    }

    
    public function get_mappings() {
        return $this->mappings;
    }

    
    public function get_unmapped_from() {
        return array_keys(array_diff_key($this->get_collection_from(), $this->mappings));
    }

    
    public function get_unmapped_objects_from() {
        return array_diff_key($this->get_collection_from(), $this->mappings);
    }

    
    public function get_unmapped_to() {
        return array_keys(array_diff_key($this->get_collection_to(), array_flip($this->mappings)));
    }

    
    public function get_unmapped_objects_to() {
        return array_diff_key($this->get_collection_to(), array_flip($this->mappings));
    }

    
    public function has_mappings() {
        return !empty($this->mappings);
    }

    
    public function reset_mappings() {
        $this->mappings = array();
    }

}
