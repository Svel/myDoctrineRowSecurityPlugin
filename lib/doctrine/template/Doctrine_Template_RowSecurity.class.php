<?php
/**
 * Doctrine_Template_RowSecurity
 *
 * Row securited doctrine records
 */
class Doctrine_Template_RowSecurity extends Doctrine_Template
{
    protected $_options = array(
        'filtered_field'    => 'user_id',
        'admin_credential'  => null,
        'id_method'         => null,
        'alias'             => null,
        'type'              => 'integer',
        'length'            => 4,
        'options'           => array(),
        'isSecure'          => true,
    );


    /**
     * __construct
     *
     * @param string $array
     * @return void
     */
    public function __construct(array $_options = array())
    {
        $this->_options = Doctrine_Lib::arrayDeepMerge($this->_options, $_options);
    }

    /**
     * Set table definition for RowSecurity behavior
     *
     * @return void
     */
    public function setTableDefinition()
    {
        $name = $this->_options['filtered_field'];

        if (!$this->getTable()->hasColumn($name)) {
            if ($this->_options['alias']) {
                $name .= ' AS ' . $this->_options['alias'];
            }

            $this->hasColumn($name, $this->_options['type'], $this->_options['length'], $this->_options['options']);
            $this->index($this->_options['filtered_field'], array('fields' => $this->_options['filtered_field']));
        }

        $this->addListener(new Doctrine_Template_Listener_RowSecurity($this->_options));
    }


    /**
     * Checks the record against `secure` flag
     *
     * @param  boolean $bool set current security
     * @return boolean
     */
    public function isSecure($bool = null)
    {
        $invoker = $this->getInvoker();

        if (!$invoker->hasMappedValue('isSecure')) {
            $invoker->mapValue('isSecure', $this->_options['isSecure']);
        }

        if (!is_null($bool)) {
            $invoker->set('isSecure', (bool) $bool);
        }

        return $invoker->get('isSecure');
    }

}
