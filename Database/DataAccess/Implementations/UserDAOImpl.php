<?php
namespace Database\DataAccess\Implementations;

use Database\DataAccess\Interfaces\UserDAO;
use Database\DatabaseManager;
use Models\DataTimeStamp;
use Models\User;

class UserDAOImpl implements UserDAO
{
    public function create(User $user, string $password): bool
    {
        if ($user->getId() !== null) {
            throw new \Exception('Cannot create a user with an existing ID. id: ' . $user->getId());
        }

        $mysqli = DatabaseManager::getMysqliConnection();

        $query = "INSERT INTO users (username, email, password, company)
                  VALUES (?, ?, ?, ?)";

        $ok = $mysqli->prepareAndExecute(
            $query,
            'ssss',
            [
                $user->getUsername(),
                $user->getEmail(),
                password_hash($password, PASSWORD_DEFAULT), // bcrypt on PHP 8.2+
                $user->getCompany()
            ]
        );

        if (!$ok) return false;

        $user->setId($mysqli->insert_id);
        return true;
    }

    private function getRawById(int $id): ?array
    {
        $mysqli = DatabaseManager::getMysqliConnection();
        $rows = $mysqli->prepareAndFetchAll(
            "SELECT * FROM users WHERE id = ?",
            'i',
            [$id]
        );
        return $rows[0] ?? null;
    }

    private function getRawByEmail(string $email): ?array
    {
        $mysqli = DatabaseManager::getMysqliConnection();
        $rows = $mysqli->prepareAndFetchAll(
            "SELECT * FROM users WHERE email = ?",
            's',
            [$email]
        );
        return $rows[0] ?? null;
    }

    private function rawDataToUser(array $raw): User
    {
        return new User(
            username: $raw['username'],
            email: $raw['email'],
            id: (int)$raw['id'],
            company: $raw['company'] ?? null,
            timeStamp: new DataTimeStamp($raw['created_at'] ?? null, $raw['updated_at'] ?? null)
        );
    }

    public function getById(int $id): ?User
    {
        $raw = $this->getRawById($id);
        return $raw ? $this->rawDataToUser($raw) : null;
    }

    public function getByEmail(string $email): ?User
    {
        $raw = $this->getRawByEmail($email);
        return $raw ? $this->rawDataToUser($raw) : null;
    }

    public function getHashedPasswordById(int $id): ?string
    {
        $raw = $this->getRawById($id);
        return $raw['password'] ?? null;
    }
}
