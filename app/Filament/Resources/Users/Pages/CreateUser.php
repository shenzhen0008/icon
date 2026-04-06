<?php

namespace App\Filament\Resources\Users\Pages;

use App\Filament\Resources\Users\UserResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UserResource::class;

    /**
     * @param array<string, mixed> $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $username = (string) ($data['username'] ?? '');

        if (! array_key_exists('name', $data) || blank($data['name'])) {
            $data['name'] = $username;
        }

        if (! array_key_exists('email', $data) || blank($data['email'])) {
            $data['email'] = strtolower($username).'@local.invalid';
        }

        return $data;
    }
}
