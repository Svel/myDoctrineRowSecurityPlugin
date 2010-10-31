<?php
/**
 * Listener for the RowSecurity behavior which automatically:
 * - sets the filtered_field columns when a record is inserted and updated;
 * - filters retrived rows using filtered_field
 */
class Doctrine_Template_Listener_RowSecurity extends Doctrine_Record_Listener
{
    /**
     * Array of timestampable options
     */
    protected $_options = array();

    /**
     * __construct
     *
     * @param string $options
     * @return void
     */
    public function __construct(array $options)
    {
        $this->_options = $options;
    }

    /**
     * Перенаправление пользователя на страницу о недостаточности прав для выполнения операции.
     * Эксепшин системы безопасности.
     *
     * @param unknown_type $_event
     */
    protected function throwException($_event)
    {
        sfContext::getInstance()->getController()->forward(sfConfig::get('sf_secure_module'), sfConfig::get('sf_secure_action'));
        throw new sfStopException();
    }

    /**
     * Проверка допустимости записи объекта.
     * Добавление идентификатора пользователя, если он еще не установлен
     *
     * @param Doctrine_Event $event
     */
    public function preInsert(Doctrine_Event $event)
    {
        // Если операцию выполняет не администратор таблицы, и операция должна быть проверена системой безопасности
        $invoker = $event->getInvoker();
        if ($this->hasUser() && !$this->isAdmin() && $invoker->isSecure()) {
            $fieldName = $invoker->getTable()->getFieldName($this->_options['filtered_field']);
            if (!$invoker[$fieldName]) {
                $invoker->$fieldName = $this->getUserId();
            } elseif ($this->getUserId() != $invoker[$fieldName]) {
                // Пользователь пытается изменить чужие данные
                $this->throwException($event);
            }
        }
    }

    public function preUpdate(Doctrine_Event $event)
    {
        if ($this->isNotAllowed($event->getInvoker())) {
            $this->throwException($event);
        }
    }

    public function preDelete(Doctrine_Event $event)
    {
        if ($this->isNotAllowed($event->getInvoker())) {
            $this->throwException($event);
        }
    }

    public function preDqlSelect(Doctrine_Event $event)
    {
        $this->addDqlSecurityFilter($event);
    }

    public function preDqlUpdate(Doctrine_Event $event)
    {
        $this->addDqlSecurityFilter($event);
    }

    public function preDqlDelete(Doctrine_Event $event)
    {
        $this->addDqlSecurityFilter($event);
    }


    protected function hasUser()
    {
        return (sfContext::hasInstance() && sfContext::getInstance()->getUser());
    }

    /**
     * Get current user identifier from context
     *
     * @return int
     * @throws sfConfigurationException
     */
    protected function getUserId()
    {
        if ($this->hasUser()) {
            $user = sfContext::getInstance()->getUser();

            if (($user instanceof sfGuardSecurityUser) || method_exists($user, 'getUserId')) {
                return $user->getUserId();
            } elseif (method_exists($user, 'getId')) {
                return $user->getId();
            } elseif (isset($this->_options['id_method']) && method_exists($user, $this->_options['id_method'])) {
                return $user->$$this->_options['id_method']();
            }
        }

        throw new sfConfigurationException('Specify method to get owner identifier');
    }

    /**
     * Проверка у пользователя прав администратора таблицы
     *
     * @return boolean  True если пользователь является администратором таблицы, иначе false
     */
    protected function isAdmin()
    {
        $ret = false;
        if (!is_null($this->_options['admin_credential'])) {
            $ret = sfContext::getInstance()->getUser()->hasCredential($this->_options['admin_credential']);
        }

        return $ret;
    }

    /**
     * Проверка на допустимость операции при действиях над объектом (т.е. одной строкой таблицы)
     *
     * @param unknown_type $_invoker
     * @return boolean  True если операция может быть выполнена, иначе false
     */
    protected function isNotAllowed($_invoker)
    {
        $fieldName = $_invoker->getTable()->getFieldName($this->_options['filtered_field']);
        return ($this->hasUser() && !(($this->getUserId() == $_invoker[$fieldName]) || !$_invoker->isSecure() || $this->isAdmin()));
    }

    /**
     * Добавление к DQL-запросу фильтра по пользователю
     *
     * @param unknown_type $_event
     */
    protected function addDqlSecurityFilter($_event)
    {
        $params = $_event->getParams();
        $field = $params['alias'] . '.' . $this->_options['filtered_field'];
        $query = $_event->getQuery();
        // Если создан объект пользователя, при этом пользователь не суперадмин, но авторизован
        if ($this->hasUser() && !$this->isAdmin() && $this->getUserId()) {
            $userId = $this->getUserId();
            if (!$this->containsWhere($query, $field, $userId)) {
                $query->addWhere($field . ' = ? ', array($this->getUserId()));
            }
        }
    }

    /**
     * Проверка на наличие условия по пользователю в запросе
     *
     * @param Doctrine_Query $_query
     * @param string $_field
     * @param mixed $_value
     * @return boolean  True если условие по пользователю уже есть в DQL-запросе, иначе false
     */
    protected function containsWhere(Doctrine_Query $_query, $_field, $_value)
    {
        $ret = false;
        $params = $_query->getParams();
        $whereParams = $params['where'];
        if (count($whereParams)) {
            $condition = $_field . ' = ?';
            $i = 0;
            foreach ($_query->getDqlPart('where') as $where) {
                // Условие и параметры условия совпали с условием фильтрации по текущему пользователю
                if ((false !== strpos($where, $condition)) && ($_value == $whereParams[$i])) {
                    $ret = true;
                    break;
                }
            $i += substr_count($where, '?');
            }
        }

        return $ret;
    }

}
