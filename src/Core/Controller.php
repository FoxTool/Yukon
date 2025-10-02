<?php

namespace FoxTool\Yukon\Core;

class Controller
{
    protected object $authUser;

    public function __construct() {}

    public function getAuthUser()
    {
        if (!empty($this->authUser)) {
            return $this->authUser;
        }

        return null;
    }

    public function setAuthUser($user)
    {
        if (!empty($user)) {
            $this->authUser = $user;
        }
    }
}
