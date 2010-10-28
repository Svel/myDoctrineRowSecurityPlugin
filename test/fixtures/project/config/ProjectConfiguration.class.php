<?php

// only phpUnit test environment
if (!defined('SYMFONY_LIBS'))
{
    throw new RuntimeException('Could not find symfony core libraries.');
}

require_once SYMFONY_LIBS . '/autoload/sfCoreAutoload.class.php';
//require_once 'symfony/lib/autoload/sfCoreAutoload.class.php';
sfCoreAutoload::register();

class ProjectConfiguration extends sfProjectConfiguration
{
    /**
     * Setup
     */
    public function setup()
    {
        $this->setPlugins(array(
            'sfDoctrinePlugin',
            'myDoctrineRowSecurityPlugin',
        ));

        $this->setPluginPath('myDoctrineRowSecurityPlugin', dirname(__FILE__) . '/../../../..');
    }

    /**
     * Doctrine global configuration
     */
    public function configureDoctrine(Doctrine_Manager $manager)
    {
        $manager->setAttribute(Doctrine_Core::ATTR_DEFAULT_TABLE_CHARSET, 'utf8');
        $manager->setAttribute(Doctrine_Core::ATTR_DEFAULT_TABLE_COLLATE, 'utf8_general_ci');
        $manager->setAttribute(Doctrine_Core::ATTR_DEFAULT_TABLE_TYPE,    'INNODB');

        # Enable DQL callbacks
        $manager->setAttribute(Doctrine_Core::ATTR_USE_DQL_CALLBACKS, true);
    }

}
