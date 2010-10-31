<?php
class myUnitTestCase extends PHPUnit_Framework_TestCase
{
    protected $preserveGlobalState = false;

    protected $helper = null;


    /**
     * Returns database connection to wrap tests with transaction
     */
    protected function getConnection()
    {
        return Doctrine_Manager::getInstance()->getConnection('doctrine');
    }


    /**
     * Execute raw query and return result
     *
     * @param string $query
     * @return mixed
     */
    protected function rawQuery($query)
    {
        return $this->getConnection()->getDbh()->query($query)->fetchAll();
    }


    /**
     * Search in Doctrine DB using raw queries
     *
     * @param  string $model
     * @param  array  $params
     * @return mixed
     */
    protected function find($model, array $params = null)
    {
        $tableName = Doctrine::getTable($model)->getTableName();
        $query = sprintf("SELECT tbl.* FROM %s AS tbl", $tableName);

        $where = array();
        if ($params) {
            foreach ($params as $column => $value) {
                $where[] = "tbl.{$column} = '{$value}'";
            }

            $query .= " WHERE " . implode(' AND ', $where);
        }

        return $this->rawQuery($query);
    }


    /**
     * setUp method for PHPUnit
     */
    protected function setUp()
    {
        $dir = getcwd();
        chdir(sfConfig::get('sf_root_dir'));
        // Rebuild DB
        $task = new sfDoctrineBuildTask(new sfEventDispatcher, new sfFormatter);
        $task->run($args = array(), $options = array(
            'env' => 'test',
            'no-confirmation' => true,
            'all' => true,
        ));

        // Init concrete test config && autoload
        sfConfig::clear();
        $configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'test', true);
        sfContext::createInstance($configuration);

        // Object helper
        $this->helper = $this->makeHelper();

        // Begin transaction
        if ($conn = $this->getConnection()) {
            $conn->beginTransaction();
        }
    }


    /**
     * tearDown method for PHPUnit
     */
    protected function tearDown()
    {
        // Rollback transaction
        if ($conn = $this->getConnection()) {
            $conn->rollback();
        }

        $this->getConnection()->clear();

        if (function_exists('gc_collect_cycles')) {
            gc_collect_cycles();
        }
    }


    /**
     * Create helper object
     */
    protected function makeHelper()
    {
        return new myTestObjectHelper();
    }

}
