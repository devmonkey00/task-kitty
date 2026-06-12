<?php

namespace Database\Seeders;

use App\Enums\EnterpriseRole;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

/**
 * Maps legacy MVP roles (Admin, Manager, Employee) to enterprise roles when upgrading.
 */
class MigrateLegacyRolesSeeder extends Seeder
{
    /** @var array<string, string> */
    private const MAP = [
        'Admin' => 'Super Admin',
        'Manager' => 'Project Manager',
        'Employee' => 'Employee',
    ];

    public function run(): void
    {
        $this->call(RoleSeeder::class);

        foreach (self::MAP as $legacy => $enterprise) {
            $legacyRole = Role::where('name', $legacy)->where('guard_name', 'web')->first();
            if (! $legacyRole) {
                continue;
            }

            $users = User::role($legacy)->get();
            foreach ($users as $user) {
                $user->syncRoles([$enterprise]);
            }
        }
    }
}
