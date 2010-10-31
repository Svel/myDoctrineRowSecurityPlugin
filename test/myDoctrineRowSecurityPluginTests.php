<?php
require_once dirname(__FILE__) . '/bootstrap/unit.php';

/**
 * myDoctrineRowSecurityPluginTests
 */
class myDoctrineRowSecurityPluginTests extends PHPUnit_Framework_TestSuite
{
    /**
     * SetUp
     */
    public function setUp()
    {
        // Clear logs
        sfToolkit::clearDirectory(sfConfig::get('sf_log_dir'));

        // Remove cache
        sfToolkit::clearDirectory(sfConfig::get('sf_cache_dir'));
    }

    /**
     * myDoctrineRowSecurityPlugin Test Suite
     */
    public static function suite()
    {
        $suite = new myDoctrineRowSecurityPluginTests();

        $base  = __DIR__;
        $files = sfFinder::type('file')->name('*Test.php')->in(array(
            $base . '/unit',
        ));

        foreach ($files as $file) {
            $suite->addTestFile($file);
        }

        return $suite;
    }

    /**
     * Tear Down
     */
    public function tearDown()
    {
        // Remove cache
        sfToolkit::clearDirectory(sfConfig::get('sf_cache_dir'));
        // Remove generated model files
        sfToolkit::clearDirectory(sfConfig::get('sf_lib_dir') . '/model/doctrine/');
        // Remove generated sql
        sfToolkit::clearDirectory(sfConfig::get('sf_root_dir') . '/data/sql/');
    }

}
