<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolesTableSeeder extends Seeder
{
    public function run()
    {
        // Create roles if they don't already exist
        $author = Role::firstOrCreate(['name' => 'author']);
        $editor = Role::firstOrCreate(['name' => 'editor']);
        $associateEditor = Role::firstOrCreate(['name' => 'associate_editor']);
        $referee = Role::firstOrCreate(['name' => 'referee']);

        // Assign permissions
        $permissions = [
            'submit paper',
            'assign editor',
            'evaluate paper',
            'review paper'
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        // Assign the existing permissions to the roles
        $author->givePermissionTo('submit paper');
        $editor->givePermissionTo('assign editor');
        $associateEditor->givePermissionTo('evaluate paper');
        $referee->givePermissionTo('review paper');
    }
}
