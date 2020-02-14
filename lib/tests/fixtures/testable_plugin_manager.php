<?php



defined('MOODLE_INTERNAL') || die();

require_once(__DIR__.'/testable_update_api.php');


class testable_core_plugin_manager extends core_plugin_manager {

    
    protected static $singletoninstance;

    
    public function inject_testable_plugininfo($type, $name, \core\plugininfo\base $plugininfo) {

                parent::get_plugins();

                $this->pluginsinfo[$type][$name] = $plugininfo;
    }

    
    protected function get_update_api_client() {
        return \core\update\testable_api::client();
    }

    
    public function load_available_updates_for_plugin($component) {

        if ($component === 'foo_bar') {
            $updates = array();

            $updates[] = new \core\update\info($component, array(
                'version' => '2015093000',
                'release' => 'Foo bar 15.09.30 beta',
                'maturity' => MATURITY_BETA,
            ));

            $updates[] = new \core\update\info($component, array(
                'version' => '2015100400',
                'release' => 'Foo bar 15.10.04',
                'maturity' => MATURITY_STABLE,
            ));

            $updates[] = new \core\update\info($component, array(
                'version' => '2015100500',
                'release' => 'Foo bar 15.10.05 beta',
                'maturity' => MATURITY_BETA,
            ));

            return $updates;
        }

        return null;
    }
}
