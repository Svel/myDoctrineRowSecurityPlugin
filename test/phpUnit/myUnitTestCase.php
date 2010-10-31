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
        //if (!sfContext::hasInstance('frontend')) {
            //$configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'test', true);
            //sfContext::createInstance($configuration);
        //}

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

    protected function makeHelper()
    {
        return new myTestObjectHelper();
    }

}
