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
    public function makeArticle(array $props = array(), $save = true)
    {
        $defaultProps = array(
            'title'   => 'Имя пользователя',
            'content' => str_repeat(sha1(mt_rand(10,99)), 50),
            'user_id' => false,
        );
        $props = array_merge($defaultProps, $props);

        $ob = $this->makeModel('Article', $props, false);

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
            'user_id' => false,
        );
        $props = array_merge($defaultProps, $props);

        $ob = $this->makeModel('Comment', $props, false);

        if (!$article) {
            $article = $this->makeArticle(null, array(), $save);
        }
        $ob->setArticle($article);

        if ($save) {
            $ob->save();
        }

        return $ob;
    }

}
