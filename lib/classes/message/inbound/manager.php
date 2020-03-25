<?php



namespace core\message\inbound;

defined('MOODLE_INTERNAL') || die();


class manager {

    
    public static function is_enabled() {
        global $CFG;

                if (!isset($CFG->messageinbound_enabled) || !$CFG->messageinbound_enabled) {
            return false;
        }

                if (!isset($CFG->messageinbound_mailbox) || empty($CFG->messageinbound_mailbox)) {
            return false;
        }

        if (!isset($CFG->messageinbound_domain) || empty($CFG->messageinbound_domain)) {
            return false;
        }

        return true;
    }

    
    public static function update_handlers_for_component($componentname) {
        global $DB;

        $componentname = \core_component::normalize_componentname($componentname);
        $existinghandlers = $DB->get_recordset('messageinbound_handlers', array('component' => $componentname));
        foreach ($existinghandlers as $handler) {
            if (!class_exists($handler->classname)) {
                self::remove_messageinbound_handler($handler);
            }
        }

        self::create_missing_messageinbound_handlers_for_component($componentname);
    }

    
    public static function load_default_handlers_for_component($componentname) {
        $componentname = \core_component::normalize_componentname($componentname);
        $dir = \core_component::get_component_directory($componentname);

        if (!$dir) {
            return array();
        }

        $file = $dir . '/db/messageinbound_handlers.php';
        if (!file_exists($file)) {
            return array();
        }

        $handlers = null;
        require_once($file);

        if (!isset($handlers)) {
            return array();
        }

        $handlerinstances = array();

        foreach ($handlers as $handler) {
            $record = (object) $handler;
            $record->component = $componentname;
            if ($handlerinstance = self::handler_from_record($record)) {
                $handlerinstances[] = $handlerinstance;
            } else {
                throw new \coding_exception("Inbound Message Handler not found for '{$componentname}'.");
            }
        }

        return $handlerinstances;
    }

    
    public static function create_missing_messageinbound_handlers_for_component($componentname) {
        global $DB;
        $componentname = \core_component::normalize_componentname($componentname);

        $expectedhandlers = self::load_default_handlers_for_component($componentname);
        foreach ($expectedhandlers as $handler) {
            $recordexists = $DB->record_exists('messageinbound_handlers', array(
                'component' => $componentname,
                'classname' => $handler->classname,
            ));

            if (!$recordexists) {
                $record = self::record_from_handler($handler);
                $record->component = $componentname;
                $DB->insert_record('messageinbound_handlers', $record);
            }
        }
    }

    
    public static function remove_messageinbound_handler($handler) {
        global $DB;

                $DB->delete_records('messageinbound_datakeys', array('handler' => $handler->id));

                $DB->delete_records('messageinbound_handlers', array('id' => $handler->id));
    }

    
    public static function record_from_handler($handler) {
        $record = new \stdClass();
        $record->id = $handler->id;
        $record->component = $handler->component;
        $record->classname = get_class($handler);
        if (strpos($record->classname, '\\') !== 0) {
            $record->classname = '\\' . $record->classname;
        }
        $record->defaultexpiration = $handler->defaultexpiration;
        $record->validateaddress = $handler->validateaddress;
        $record->enabled = $handler->enabled;

        return $record;
    }

    
    protected static function handler_from_record($record) {
        $classname = $record->classname;
        if (strpos($classname, '\\') !== 0) {
            $classname = '\\' . $classname;
        }
        if (!class_exists($classname)) {
            return false;
        }

        $handler = new $classname;
        if (isset($record->id)) {
            $handler->set_id($record->id);
        }
        $handler->set_component($record->component);

                if (isset($record->defaultexpiration)) {
            $handler->set_defaultexpiration($record->defaultexpiration);
        }

        if (isset($record->validateaddress)) {
            $handler->set_validateaddress($record->validateaddress);
        }

        if (isset($record->enabled)) {
            $handler->set_enabled($record->enabled);
        }

        return $handler;
    }

    
    public static function get_handler($classname) {
        global $DB;

        if (strpos($classname, '\\') !== 0) {
            $classname = '\\' . $classname;
        }

        $record = $DB->get_record('messageinbound_handlers', array('classname' => $classname), '*', IGNORE_MISSING);
        if (!$record) {
            return false;
        }
        return self::handler_from_record($record);
    }

    
    public static function get_handler_from_id($id) {
        global $DB;

        $record = $DB->get_record('messageinbound_handlers', array('id' => $id), '*', IGNORE_MISSING);
        if (!$record) {
            return false;
        }
        return self::handler_from_record($record);
    }

}
