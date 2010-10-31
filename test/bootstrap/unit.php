<?php
require_once(dirname(__FILE__) . '/../phpUnit/myUnitTestCase.php');
require_once(dirname(__FILE__) . '/../phpUnit/myTestObjectHelper.php');

define('SYMFONY_LIBS', dirname(__FILE__) . '/../../lib/vendor/symfony/lib');

require_once(dirname(__FILE__).'/../fixtures/project/config/ProjectConfiguration.class.php');

$configuration = ProjectConfiguration::getApplicationConfiguration('frontend', 'test', $debug = true);
sfContext::createInstance($configuration);

sfSimpleAutoload::getInstance()->reload();

new sfPluginConfigurationGeneric($configuration, dirname(__FILE__) . '/../..', 'myDoctrineRowSecurityPlugin');
