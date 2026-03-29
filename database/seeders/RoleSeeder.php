<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Define initial roles and their modules
        $roles = [
            [
                'name' => 'Admin',
                'modules' => [
                    'dashboard',
                    'service_requests',
                    'scheduling',
                    'service_records',
                    'collections',
                    'donations',
                    'services_fees',
                    'reports',
                    'system_settings',
                    'system_roles',
                    'user_accounts',
                    'audit_trail'
                ]
            ],
            [
                'name' => 'Treasurer',
                'modules' => [
                    'dashboard',
                    'collections',
                    'donations',
                    'services_fees',
                    'reports'
                ]
            ],
            [
                'name' => 'Priest',
                'modules' => [
                    'dashboard',
                    'scheduling',
                    'service_records',
                    'reports'
                ]
            ],
            [
                'name' => 'Secretary',
                'modules' => [
                    'dashboard',
                    'service_requests',
                    'scheduling',
                    'service_records'
                ]
            ]
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(
                ['name' => $role['name']], // Update if exists based on name
                ['modules' => $role['modules']]
            );
        }
    }
}
