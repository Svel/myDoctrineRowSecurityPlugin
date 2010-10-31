<?php
require_once dirname(__FILE__) . '/../../../../bootstrap/unit.php';
/**
 * Doctrine Template RowSecurity test
 *
 * @author Svel <svel.sontz@gmail.com>
 */
class Doctrine_Template_RowSecurityTest extends myUnitTestCase
{
    /**
     * Create user and set user id to session
     */
    protected function getUser($isAdmin = false, $isAuthenticated = true)
    {
        $session = sfContext::getInstance()
            ->getUser();

        $user = $this->helper->makeUser();
        $session->setAttribute('user_id', $user->getId(), 'sfGuardSecurityUser');
        $session->setAuthenticated($isAuthenticated);

        if ($isAdmin) {
            $session->addCredential('admin');
        }

        return $user;
    }


    /**
     * isSecure method
     */
    public function testIsSecureMethodWorksOnObjects()
    {
        $ob1 = new Article();
        $ob2 = new Article();

        $this->assertTrue($ob1->isSecure(), 'Security defaults to TRUE');
        $this->assertEquals($ob1->isSecure(), $ob2->isSecure(), 'Security settings equals');

        $ob1->isSecure(false);

        $this->assertFalse($ob1->isSecure(), 'Security obj#1 turned off');
        $this->assertTrue($ob2->isSecure(), 'Security obj#2 is not changed');
    }


    /**
     * Insert: new object has my (owner) user_id
     */
    public function testNew()
    {
        $user = $this->getUser();
        $article = $this->helper->makeArticle(array('user_id' => false));

        $this->assertEquals($user->getId(), $article->getUserId(), 'Article has User identifier');
    }


    /**
     * Insert: Save own object (set mine ID)
     */
    public function testNewWithId()
    {
        $user = $this->getUser();
        $article = $this->helper->makeArticle(array('user_id' => $user->getId()), false);
        $article->save();

        $this->assertEquals($user->getId(), $article->getUserId(), 'User allowed to set his id to the new record');
        $this->assertEquals(1, count($this->find('Article', array('user_id' => $user->getId(), 'id' => $article->getId()))));
    }


    /**
     * Insert: Save foreign object (set foreign ID)
     */
    public function testNewWithForeignId()
    {
        $me = $this->getUser();
        $user = $this->helper->makeUser();
        $article = $this->helper->makeArticle(array('user_id' => $user->getId()), false);

        ob_start();
        try {
            $article->save();
        } catch (sfStopException $e) {
            $this->assertEquals($article->state(), Doctrine_Record::STATE_TDIRTY, 'Record has not been saved. Record ID != User ID');
            $trash = ob_get_clean();
            return;
        }
        $trash = ob_get_clean();

        $this->fail('No expected Exception');
    }


    /**
     * Insert: Save object by table administrator (set mine ID)
     */
    public function testNewByAdministrator()
    {
        $user = $this->getUser(true);
        $article = $this->helper->makeArticle(array('user_id' => false));

        $this->assertEquals($user->getId(), $article->getUserId(), 'Article has User identifier');
    }


    /**
     * Insert: Save object by table administrator (set foreign ID)
     */
    public function testNewByAdministratorWithForeign()
    {
        $me = $this->getUser(true);
        $user = $this->helper->makeUser();
        $article = $this->helper->makeArticle(array('user_id' => $user->getId()), false);

        ob_start();
        try {
            $article->save();
        } catch (sfStopException $e) {
            $this->fail('Admin can create foreign user records');

            $trash = ob_get_clean();
            return;
        }
        $trash = ob_get_clean();

        $this->assertEquals($user->getId(), $article->getUserId(), 'Article has User identifier');
    }


    /**
     * Insert: Save own object with security turned off (set mine ID)
     */
    public function testNewWithoutSecurity()
    {
        $user = $this->getUser();
        $article = $this->helper->makeArticle(array('user_id' => $user->getId()), false);
        $article->isSecure(false);
        $article->save();

        $this->assertEquals($user->getId(), $article->getUserId(), 'Article has User identifier');
    }


    /**
     * Insert: Save foreign object with security turned off
     */
    public function testNewWithForeignWithoutSecurity()
    {
        $user = $this->getUser();
        $article = $this->helper->makeArticle(array('user_id' => false), false);
        $article->isSecure(false);
        $article->save();

        $this->assertNotEquals($user->getId(), $article->getUserId(), 'Article has foreign User identifier');
        $this->assertEquals(1, count($this->find('Article')));
    }


    /**
     * Insert: Save foreign object with security turned off by table-administrator
     */
    public function testNewByAdministratorWithForeignWithoutSecurity()
    {
        $user = $this->getUser(true);
        $article = $this->helper->makeArticle(array('user_id' => false), false);
        $article->isSecure(false);
        $article->save();

        $this->assertNotEquals($user->getId(), $article->getUserId(), 'Article has foreign User identifier');
        $this->assertEquals(1, count($this->find('Article')));
    }


    /**
     * Select: filtering by user_id
     */
    public function testSelect()
    {
        $user = $this->getUser();
        $article = $this->helper->makeArticle(array('user_id' => $user->getId()));
        # Noise
        $a1 = $this->helper->makeArticle(array('user_id' => false), false);
        $a2 = $this->helper->makeArticle(array('user_id' => false), false);

        $a1->isSecure(false);
        $a1->save();
        $a2->isSecure(false);
        $a2->save();

        $result = Doctrine::getTable('Article')->createQuery()->execute();

        $this->assertEquals(3, count($this->find('Article')));
        $this->assertEquals(1, $result->count(), 'Auto-filtering on simple DQL queries');
    }

}
