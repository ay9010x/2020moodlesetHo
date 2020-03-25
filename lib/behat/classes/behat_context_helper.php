<?php




use Behat\Testwork\Environment\Environment;


class behat_context_helper {

    
    protected static $environment = null;


    
    protected static $escaper;

    
    public static function set_session(Environment $environment) {
        self::$environment = $environment;
    }

    
    public static function get($classname) {

        if (!$subcontext = self::$environment->getContext($classname)) {
            throw coding_exception('The required "' . $classname . '" class does not exist');
        }

        return $subcontext;
    }

    
    public static function escape($label) {
        if (empty(self::$escaper)) {
            self::$escaper = new \Behat\Mink\Selector\Xpath\Escaper();
        }
        return self::$escaper->escapeLiteral($label);
    }
}
