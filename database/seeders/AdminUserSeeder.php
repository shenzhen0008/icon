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
        $defaults = $this->loadAdminDefaults();

        $adminEmail = (string) env('ADMIN_SEED_EMAIL', (string) ($defaults['email'] ?? 'admin@icon-market.local'));
        $adminUsername = (string) env('ADMIN_SEED_USERNAME', (string) ($defaults['username'] ?? 'admin'));
        $adminName = (string) env('ADMIN_SEED_NAME', (string) ($defaults['name'] ?? 'System Admin'));
        $rawPassword = env('ADMIN_SEED_PASSWORD');

        $user = User::query()
            ->where('email', $adminEmail)
            ->orWhere('username', $adminUsername)
            ->first();

        if (! $user instanceof User) {
            $passwordForCreate = is_string($rawPassword) && $rawPassword !== ''
                ? $rawPassword
                : 'ChangeMe_123456';

            User::query()->create([
                'username' => $adminUsername,
                'name' => $adminName,
                'email' => $adminEmail,
                'password' => Hash::make($passwordForCreate),
                'is_admin' => true,
            ]);

            return;
        }

        $user->forceFill([
            'username' => $adminUsername,
            'name' => $adminName,
            'email' => $adminEmail,
            'is_admin' => true,
        ]);

        if (is_string($rawPassword) && $rawPassword !== '') {
            $user->password = Hash::make($rawPassword);
        }

        $user->save();
    }

    /**
     * @return array<string, mixed>
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

        return is_array($decoded) ? $decoded : [];
    }
}
