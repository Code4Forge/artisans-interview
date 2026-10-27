<?php

namespace App\Exceptions;

use Exception;

class RoleAssignedToUsersException extends Exception
{
    public function __construct(string $roleName, int $userCount)
    {
        parent::__construct("Cannot delete role '{$roleName}' because {$userCount} user(s) are assigned to it.");
    }
}
