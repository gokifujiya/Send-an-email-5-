<?php
namespace Database\DataAccess;

use Database\DataAccess\Implementations\ComputerPartDAOImpl;
use Database\DataAccess\Implementations\UserDAOImpl;

class DAOFactory
{
    public static function getUserDAO(): \Database\DataAccess\Interfaces\UserDAO
    {
        return new UserDAOImpl();
    }

    public static function getComputerPartDAO(): \Database\DataAccess\Interfaces\ComputerPartDAO
    {
        return new ComputerPartDAOImpl();
    }
}

