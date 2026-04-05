<?php

namespace App\Modules\User\Services;

use App\Models\User;
use App\Modules\User\Support\UsernameGenerator;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class AccountActivationService
{
    public function __construct(private readonly UsernameGenerator $usernameGenerator)
    {
    }

    public function createUserFromTemporaryUsername(string $temporaryUsername, string $plainPassword): User
    {
        $maxRetries = 5;
        $username = $temporaryUsername;

        for ($attempt = 0; $attempt < $maxRetries; $attempt++) {
            try {
                return DB::transaction(function () use ($username, $plainPassword): User {
                    return User::query()->create([
                        'username' => $username,
                        'name' => $username,
                        'email' => strtolower($username).'@local.icon-market',
                        'password' => $plainPassword,
                    ]);
                });
            } catch (QueryException $exception) {
                if (! $this->isUniqueConstraintViolation($exception)) {
                    throw $exception;
                }

                $username = $this->usernameGenerator->generate((int) config('user.username_length'));
            }
        }

        throw new \RuntimeException('Unable to allocate unique username after retries.');
    }

    private function isUniqueConstraintViolation(QueryException $exception): bool
    {
        return (string) $exception->getCode() === '23000';
    }
}
