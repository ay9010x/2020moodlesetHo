<?php



use Behat\Behat\Context\BehatContext,
    Behat\MinkExtension\Context\MinkContext,
    Moodle\BehatExtension\Context\MoodleContext;


class behat_init_context extends BehatContext {

    
    public function __construct(array $parameters) {
        $this->useContext('moodle', new MoodleContext($parameters));
    }

}
