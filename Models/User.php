<?php
namespace Models;

class DataTimeStamp
{
    public function __construct(
        public ?string $createdAt = null,
        public ?string $updatedAt = null,
    ) {}
}

class User
{
    public function __construct(
        private string $username,
        private string $email,
        private ?int $id = null,
        private ?string $company = null,
        private ?DataTimeStamp $timeStamp = null,
    ) {}

    public function getId(): ?int { return $this->id; }
    public function setId(int $id): void { $this->id = $id; }

    public function getUsername(): string { return $this->username; }
    public function setUsername(string $v): void { $this->username = $v; }

    public function getEmail(): string { return $this->email; }
    public function setEmail(string $v): void { $this->email = $v; }

    public function getCompany(): ?string { return $this->company; }
    public function setCompany(?string $v): void { $this->company = $v; }

    public function getTimeStamp(): ?DataTimeStamp { return $this->timeStamp; }
    public function setTimeStamp(DataTimeStamp $ts): void { $this->timeStamp = $ts; }
}
