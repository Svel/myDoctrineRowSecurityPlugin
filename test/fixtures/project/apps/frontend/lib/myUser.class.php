<?php

class myUser extends sfBasicSecurityUser
{
    /**
     *
     */
    public function getId()
    {
        return $this->getAttribute('user_id', false, 'sfGuardSecurityUser');
    }

}
