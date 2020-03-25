<?php



require_once(__DIR__ . '/../../classes/event/base.php');


abstract class phpunit_event_mock extends \core\event\base {

    
    public static function testable_get_legacy_eventdata($event) {
        return $event->get_legacy_eventdata();
    }

    
    public static function testable_get_legacy_logdata($event) {
        return $event->get_legacy_logdata();
    }

    
    public static function testable_get_event_context($event) {
        return $event->context;
    }

    
    public static function testable_set_event_context($event, $context) {
        $event->context = $context;
    }
}
