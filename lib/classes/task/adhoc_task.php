<?php



namespace core\task;


abstract class adhoc_task extends task_base {

    
    private $customdata = '';

    
    private $id = null;

    
    public function set_id($id) {
        $this->id = $id;
    }

    
    public function get_id() {
        return $this->id;
    }

    
    public function set_custom_data($customdata) {
        $this->customdata = json_encode($customdata);
    }

    
    public function set_custom_data_as_string($customdata) {
        $this->customdata = $customdata;
    }

    
    public function get_custom_data() {
        return json_decode($this->customdata);
    }

    
    public function get_custom_data_as_string() {
        return $this->customdata;
    }


}
