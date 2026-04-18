<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Throwable;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        foreach ($this->loadAdminDefaults() as $admin) {
            if (! is_array($admin)) {
                continue;
            }

            $adminEmail = (string) ($admin['email'] ?? '');
            $adminUsername = (string) ($admin['username'] ?? '');
            $adminName = (string) ($admin['name'] ?? $adminUsername);
            $seedPassword = (string) ($admin['password'] ?? 'ChangeMe_123456');
            $rawPassword = (string) env('ADMIN_SEED_PASSWORD', $seedPassword);

            if ($adminEmail === '' || $adminUsername === '') {
                continue;
            }

            $user = User::query()
                ->where('email', $adminEmail)
                ->orWhere('username', $adminUsername)
                ->first();

            if (! $user instanceof User) {
                User::query()->create([
                    'username' => $adminUsername,
                    'name' => $adminName,
                    'email' => $adminEmail,
                    'password' => Hash::make($rawPassword !== '' ? $rawPassword : 'ChangeMe_123456'),
                    'is_admin' => true,
                ]);

                continue;
            }

            $user->forceFill([
                'username' => $adminUsername,
                'name' => $adminName,
                'email' => $adminEmail,
                'is_admin' => true,
            ]);

            $user->password = Hash::make($rawPassword !== '' ? $rawPassword : 'ChangeMe_123456');

            $user->save();
        }
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function loadAdminDefaults(): array
    {
        $file = database_path('seeders/data/admin_user.json');

        if (! is_file($file)) {
            return [];
        }

        try {
            $decoded = json_decode((string) file_get_contents($file), true, 512, JSON_THROW_ON_ERROR);
        } catch (Throwable) {
            return [];
        }

        if (! is_array($decoded)) {
            return [];
        }

        if (array_is_list($decoded)) {
            return $decoded;
        }

        /** @var array<string, mixed> $decoded */
        return [$decoded];
    }
}
