<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use BezhanSalleh\FilamentShield\Support\Utils;
use Spatie\Permission\PermissionRegistrar;

class ShieldSeeder extends Seeder
{
    public function run(): void
    {
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        $rolesWithPermissions = '[{"name":"super_admin","guard_name":"web","permissions":["view_role","view_any_role","create_role","update_role","delete_role","delete_any_role","view_caregiver","view_any_caregiver","create_caregiver","update_caregiver","restore_caregiver","restore_any_caregiver","replicate_caregiver","reorder_caregiver","delete_caregiver","delete_any_caregiver","force_delete_caregiver","force_delete_any_caregiver","view_customer","view_any_customer","create_customer","update_customer","restore_customer","restore_any_customer","replicate_customer","reorder_customer","delete_customer","delete_any_customer","force_delete_customer","force_delete_any_customer","view_device","view_any_device","create_device","update_device","restore_device","restore_any_device","replicate_device","reorder_device","delete_device","delete_any_device","force_delete_device","force_delete_any_device","view_device::alarm","view_any_device::alarm","create_device::alarm","update_device::alarm","restore_device::alarm","restore_any_device::alarm","replicate_device::alarm","reorder_device::alarm","delete_device::alarm","delete_any_device::alarm","force_delete_device::alarm","force_delete_any_device::alarm","view_g::p::s::location","view_any_g::p::s::location","create_g::p::s::location","update_g::p::s::location","restore_g::p::s::location","restore_any_g::p::s::location","replicate_g::p::s::location","reorder_g::p::s::location","delete_g::p::s::location","delete_any_g::p::s::location","force_delete_g::p::s::location","force_delete_any_g::p::s::location","view_user","view_any_user","create_user","update_user","restore_user","restore_any_user","replicate_user","reorder_user","delete_user","delete_any_user","force_delete_user","force_delete_any_user","page_MyProfilePage","view_invite","view_any_invite","create_invite","update_invite","restore_invite","restore_any_invite","replicate_invite","reorder_invite","delete_invite","delete_any_invite","force_delete_invite","force_delete_any_invite","page_CustomerDashboard","widget_DeviceDashboardDetails","widget_DashboardDeviceMap","widget_PendingInvitesWidget"]},{"name":"customer","guard_name":"web","permissions":["view_caregiver","view_any_caregiver","create_caregiver","update_caregiver","reorder_caregiver","delete_caregiver","delete_any_caregiver","view_invite","view_any_invite","create_invite","update_invite","restore_invite","restore_any_invite","replicate_invite","reorder_invite","delete_invite","delete_any_invite","force_delete_invite","force_delete_any_invite","widget_DeviceDashboardDetails","widget_DashboardDeviceMap"]},{"name":"Meldkamer","guard_name":"web","permissions":["view_caregiver","view_any_caregiver","view_customer","view_any_customer","view_device","view_any_device","view_device::alarm","view_any_device::alarm","view_g::p::s::location","view_any_g::p::s::location","page_MyProfilePage","page_CustomerDashboard","widget_DeviceDashboardDetails","widget_DashboardDeviceMap","widget_PendingInvitesWidget"]}]';
        $directPermissions = '[]';

        static::makeRolesWithPermissions($rolesWithPermissions);
        static::makeDirectPermissions($directPermissions);

        $this->command->info('Shield Seeding Completed.');
    }

    protected static function makeRolesWithPermissions(string $rolesWithPermissions): void
    {
        if (! blank($rolePlusPermissions = json_decode($rolesWithPermissions, true))) {
            /** @var Model $roleModel */
            $roleModel = Utils::getRoleModel();
            /** @var Model $permissionModel */
            $permissionModel = Utils::getPermissionModel();

            foreach ($rolePlusPermissions as $rolePlusPermission) {
                $role = $roleModel::firstOrCreate([
                    'name' => $rolePlusPermission['name'],
                    'guard_name' => $rolePlusPermission['guard_name'],
                ]);

                if (! blank($rolePlusPermission['permissions'])) {
                    $permissionModels = collect($rolePlusPermission['permissions'])
                        ->map(fn ($permission) => $permissionModel::firstOrCreate([
                            'name' => $permission,
                            'guard_name' => $rolePlusPermission['guard_name'],
                        ]))
                        ->all();

                    $role->syncPermissions($permissionModels);
                }
            }
        }
    }

    public static function makeDirectPermissions(string $directPermissions): void
    {
        if (! blank($permissions = json_decode($directPermissions, true))) {
            /** @var Model $permissionModel */
            $permissionModel = Utils::getPermissionModel();

            foreach ($permissions as $permission) {
                if ($permissionModel::whereName($permission)->doesntExist()) {
                    $permissionModel::create([
                        'name' => $permission['name'],
                        'guard_name' => $permission['guard_name'],
                    ]);
                }
            }
        }
    }
}
