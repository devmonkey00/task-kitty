<?php

namespace Database\Seeders;

use App\Enums\EnterpriseRole;
use App\Models\OfficeLocation;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        $this->call([
            RoleSeeder::class,
            MigrateLegacyRolesSeeder::class,
            OfficeLocationSeeder::class,
            TagSeeder::class,
        ]);

        $office = OfficeLocation::first();

        $superAdmin = User::factory()->create([
            'name' => 'Super Admin',
            'email' => 'superadmin@taskkitty.test',
            'office_location_id' => $office?->id,
        ]);
        $superAdmin->assignRole(EnterpriseRole::SuperAdmin->value);

        $hrAdmin = User::factory()->create([
            'name' => 'HR Admin',
            'email' => 'hr@taskkitty.test',
            'office_location_id' => $office?->id,
        ]);
        $hrAdmin->assignRole(EnterpriseRole::HrAdmin->value);

        $projectManager = User::factory()->create([
            'name' => 'Project Manager',
            'email' => 'pm@taskkitty.test',
            'office_location_id' => $office?->id,
        ]);
        $projectManager->assignRole(EnterpriseRole::ProjectManager->value);

        $teamLead = User::factory()->create([
            'name' => 'Team Lead',
            'email' => 'lead@taskkitty.test',
            'office_location_id' => $office?->id,
            'team_lead_id' => $projectManager->id,
        ]);
        $teamLead->assignRole(EnterpriseRole::TeamLead->value);

        $employee = User::factory()->create([
            'name' => 'Employee User',
            'email' => 'employee@taskkitty.test',
            'office_location_id' => $office?->id,
            'team_lead_id' => $teamLead->id,
        ]);
        $employee->assignRole(EnterpriseRole::Employee->value);

        $this->call(EnterpriseDemoSeeder::class);
    }
}
