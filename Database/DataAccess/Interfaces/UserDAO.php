<?php
namespace Database\DataAccess\Interfaces;

use Models\User;

interface UserDAO
{
    /** Create a new user with a PLAINTEXT password (this method will hash it). */
    public function create(User $user, string $password): bool;

    /** Fetch a user (without the password hash) by id. */
    public function getById(int $id): ?User;

    /** Fetch a user (without the password hash) by email (used to check duplicates). */
    public function getByEmail(string $email): ?User;

    /** Get ONLY the stored hashed password by user id. */
    public function getHashedPasswordById(int $id): ?string;
}
