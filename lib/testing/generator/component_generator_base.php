<?php



defined('MOODLE_INTERNAL') || die();


abstract class component_generator_base {

    
    protected $datagenerator;

    
    public function __construct(testing_data_generator $datagenerator) {
        $this->datagenerator = $datagenerator;
    }

    
    public function reset() {
    }
}
