<?php





abstract class basic_testcase extends base_testcase {

    
    final public function __construct($name = null, array $data = array(), $dataName = '') {
        parent::__construct($name, $data, $dataName);

        $this->setBackupGlobals(false);
        $this->setBackupStaticAttributes(false);
        $this->setRunTestInSeparateProcess(false);
    }

    
    final public function runBare() {
        global $DB;

        try {
            parent::runBare();

        } catch (Exception $ex) {
            $e = $ex;
        } catch (Throwable $ex) {
                        $e = $ex;
        }

        if (isset($e)) {
                        phpunit_util::reset_all_data();
            throw $e;
        }

        if ($DB->is_transaction_started()) {
            phpunit_util::reset_all_data();
            throw new coding_exception('basic_testcase '.$this->getName().' is not supposed to use database transactions!');
        }

        phpunit_util::reset_all_data(true);
    }
}
