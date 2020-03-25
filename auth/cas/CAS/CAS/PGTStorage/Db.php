<?php



define('CAS_PGT_STORAGE_DB_DEFAULT_TABLE', 'cas_pgts');



class CAS_PGTStorage_Db extends CAS_PGTStorage_AbstractStorage
{
    

    
    private $_pdo;

    
    private function _getPdo()
    {
        return $this->_pdo;
    }

    
    private $_dsn;
    private $_username;
    private $_password;
    private $_table_options;

    
    private $_table;

    
    private function _getTable()
    {
        return $this->_table;
    }

            
    
    public function getStorageType()
    {
        return "db";
    }

    
    public function getStorageInfo()
    {
        return 'table=`'.$this->_getTable().'\'';
    }

            
    
    public function __construct(
        $cas_parent, $dsn_or_pdo, $username='', $password='', $table='',
        $driver_options=null
    ) {
        phpCAS::traceBegin();
                parent::__construct($cas_parent);

                if ( empty($table) ) {
            $table = CAS_PGT_STORAGE_DB_DEFAULT_TABLE;
        }
        if ( !is_array($driver_options) ) {
            $driver_options = array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION);
        }

                if ($dsn_or_pdo instanceof PDO) {
            $this->_pdo = $dsn_or_pdo;
        } else {
            $this->_dsn = $dsn_or_pdo;
            $this->_username = $username;
            $this->_password = $password;
            $this->_driver_options = $driver_options;
        }

                $this->_table = $table;

        phpCAS::traceEnd();
    }

            
    
    public function init()
    {
        phpCAS::traceBegin();
                if ($this->isInitialized()) {
            return;
        }

                parent::init();

                if (!($this->_pdo instanceof PDO)) {
            try {
                $this->_pdo = new PDO(
                    $this->_dsn, $this->_username, $this->_password,
                    $this->_driver_options
                );
            }
            catch(PDOException $e) {
                phpCAS::error('Database connection error: ' . $e->getMessage());
            }
        }

        phpCAS::traceEnd();
    }

            
    
    private $_errMode;

    
    private function _setErrorMode()
    {
                $pdo = $this->_getPdo();
        $this->_errMode = $pdo->getAttribute(PDO::ATTR_ERRMODE);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    
    private function _resetErrorMode()
    {
                $pdo = $this->_getPdo();
        $pdo->setAttribute(PDO::ATTR_ERRMODE, $this->_errMode);
    }

                        
    
    protected function createTableSql()
    {
        return 'CREATE TABLE ' . $this->_getTable()
            . ' (pgt_iou VARCHAR(255) NOT NULL PRIMARY KEY, pgt VARCHAR(255) NOT NULL)';
    }

    
    protected function storePgtSql()
    {
        return 'INSERT INTO ' . $this->_getTable()
            . ' (pgt_iou, pgt) VALUES (:pgt_iou, :pgt)';
    }

    
    protected function retrievePgtSql()
    {
        return 'SELECT pgt FROM ' . $this->_getTable() . ' WHERE pgt_iou = :pgt_iou';
    }

    
    protected function deletePgtSql()
    {
        return 'DELETE FROM ' . $this->_getTable() . ' WHERE pgt_iou = :pgt_iou';
    }

            
    
    public function createTable()
    {
        phpCAS::traceBegin();

                if ( !$this->isInitialized() ) {
            $this->init();
        }

                $pdo = $this->_getPdo();
        $this->_setErrorMode();

        try {
            $pdo->beginTransaction();

            $query = $pdo->query($this->createTableSQL());
            $query->closeCursor();

            $pdo->commit();
        }
        catch(PDOException $e) {
                        try {
                $pdo->rollBack();
            }
            catch(PDOException $e) {
            }
            phpCAS::error('error creating PGT storage table: ' . $e->getMessage());
        }

                $this->_resetErrorMode();

        phpCAS::traceEnd();
    }

    
    public function write($pgt, $pgt_iou)
    {
        phpCAS::traceBegin();

                $pdo = $this->_getPdo();
        $this->_setErrorMode();

        try {
            $pdo->beginTransaction();

            $query = $pdo->prepare($this->storePgtSql());
            $query->bindValue(':pgt', $pgt, PDO::PARAM_STR);
            $query->bindValue(':pgt_iou', $pgt_iou, PDO::PARAM_STR);
            $query->execute();
            $query->closeCursor();

            $pdo->commit();
        }
        catch(PDOException $e) {
                        try {
                $pdo->rollBack();
            }
            catch(PDOException $e) {
            }
            phpCAS::error('error writing PGT to database: ' . $e->getMessage());
        }

                $this->_resetErrorMode();

        phpCAS::traceEnd();
    }

    
    public function read($pgt_iou)
    {
        phpCAS::traceBegin();
        $pgt = false;

                $pdo = $this->_getPdo();
        $this->_setErrorMode();

        try {
            $pdo->beginTransaction();

                        $query = $pdo->prepare($this->retrievePgtSql());
            $query->bindValue(':pgt_iou', $pgt_iou, PDO::PARAM_STR);
            $query->execute();
            $pgt = $query->fetchColumn(0);
            $query->closeCursor();

                        $query = $pdo->prepare($this->deletePgtSql());
            $query->bindValue(':pgt_iou', $pgt_iou, PDO::PARAM_STR);
            $query->execute();
            $query->closeCursor();

            $pdo->commit();
        }
        catch(PDOException $e) {
                        try {
                $pdo->rollBack();
            }
            catch(PDOException $e) {
            }
            phpCAS::trace('error reading PGT from database: ' . $e->getMessage());
        }

                $this->_resetErrorMode();

        phpCAS::traceEnd();
        return $pgt;
    }

    

}

?>
