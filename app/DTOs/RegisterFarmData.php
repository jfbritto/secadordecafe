<?php

namespace App\DTOs;

final class RegisterFarmData
{
    public function __construct(
        public readonly string $farmName,
        public readonly string $userName,
        public readonly string $email,
        public readonly string $password,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            farmName: $data['farm_name'],
            userName: $data['name'],
            email: $data['email'],
            password: $data['password'],
        );
    }
}
