<?php

namespace core;



defined('MOODLE_INTERNAL') || die();

class notification {
    
    const SUCCESS = 'success';

    
    const WARNING = 'warning';

    
    const INFO = 'info';

    
    const ERROR = 'error';

    
    public static function add($message, $level = null) {
        global $PAGE, $SESSION;

        if ($PAGE && $PAGE->state === \moodle_page::STATE_IN_BODY) {
                                    $id = uniqid();
            echo \html_writer::span(
                $PAGE->get_renderer('core')->render(new \core\output\notification($message, $level)),
                '', array('id' => $id));

                                    echo \html_writer::script(
                    "(function() {" .
                        "var notificationHolder = document.getElementById('user-notifications');" .
                        "if (!notificationHolder) { return; }" .
                        "var thisNotification = document.getElementById('{$id}');" .
                        "if (!thisNotification) { return; }" .
                        "notificationHolder.appendChild(thisNotification.firstChild);" .
                        "thisNotification.remove();" .
                    "})();"
                );
            return;
        }

                        if (!isset($SESSION->notifications) || !array($SESSION->notifications)) {
            $SESSION->notifications = [];
        }
        $SESSION->notifications[] = (object) array(
            'message'   => $message,
            'type'      => $level,
        );
    }

    
    public static function fetch() {
        global $SESSION;

        if (!isset($SESSION) || !isset($SESSION->notifications)) {
            return [];
        }

        $notifications = $SESSION->notifications;
        unset($SESSION->notifications);

        $renderables = [];
        foreach ($notifications as $notification) {
            $renderable = new \core\output\notification($notification->message, $notification->type);
            $renderables[] = $renderable;
        }

        return $renderables;
    }

    
    public static function fetch_as_array(\renderer_base $renderer) {
        $notifications = [];
        foreach (self::fetch() as $notification) {
            $notifications[] = [
                'template'  => $notification->get_template_name(),
                'variables' => $notification->export_for_template($renderer),
            ];
        }
        return $notifications;
    }

    
    public static function success($message) {
        return self::add($message, self::SUCCESS);
    }

    
    public static function info($message) {
        return self::add($message, self::INFO);
    }

    
    public static function warning($message) {
        return self::add($message, self::WARNING);
    }

    
    public static function error($message) {
        return self::add($message, self::ERROR);
    }
}
