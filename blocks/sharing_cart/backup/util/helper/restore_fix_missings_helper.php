<?php


defined('MOODLE_INTERNAL') || die();

require_once __DIR__.'/../../moodle2/restore_root_task_fix_missings.php';


final class restore_fix_missings_helper
{
    
    public static function fix_plan(restore_plan $plan)
    {
                $tasks = $plan->get_tasks();
        foreach ($tasks as $i => $task) {
            if ($task instanceof restore_root_task) {
                $task = new restore_root_task_fix_missings('root_task');
                                                                self::set_protected_property($task, 'plan', $plan);
                $tasks[$i] = $task;
                break;
            }
        }
        self::set_protected_property($plan, 'tasks', $tasks);
    }

    
    private static function set_protected_property($obj, $prop, $value)
    {
        $reflector = new ReflectionProperty(get_class($obj), $prop);
        $reflector->setAccessible(true);
        $reflector->setValue($obj, $value);
    }
}
