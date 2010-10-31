<?php
/**
 *
 */
class myTestObjectHelper
{
    /**
     * Create model object
     *
     * @param  string $modelName - model class name
     * @param  array  $props     - model props
     * @param  bool   $save      - save object
     *
     * @return Doctrine_Record
     */
    public function makeModel($modelName, array $props = array(), $save = true)
    {
        $model = new $modelName;
        $model->fromArray($props);

        if ($save) {
            $model->save();
        }

        return $model;
    }


    /**
     * Create User
     */
    public function makeUser(array $props = array(), $save = true)
    {
        $defaultProps = array(
            'name'     => 'Имя пользователя',
            'password' => sha1(1),
        );
        $props = array_merge($defaultProps, $props);

        $ob = $this->makeModel('User', $props, $save);

        return $ob;
    }

    /**
     * Create Article
     */
    public function makeArticle(User $user = null, array $props = array(), $save = true)
    {
        $defaultProps = array(
            'title'     => 'Имя пользователя',
            'content' => str_repeat(sha1(mt_rand(10,99)), 50),
        );
        $props = array_merge($defaultProps, $props);

        $ob = $this->makeModel('Article', $props, false);

        if (!$user) {
            $ob->setUser($this->makeUser(array(), $save));
        }

        if ($save) {
            $ob->save();
        }

        return $ob;
    }

    /**
     * Create Comment
     */
    public function makeComment(Article $article = null, array $props = array(), $save = true)
    {
        $defaultProps = array(
            'content' => str_repeat(sha1(mt_rand(10,99)), 50),
        );
        $props = array_merge($defaultProps, $props);

        $ob = $this->makeModel('Comment', $props, false);

        if (!$article) {
            $ob->setArticle($this->makeArticle(null, array(), $save));
        }

        if ($save) {
            $ob->save();
        }

        return $ob;
    }

}
