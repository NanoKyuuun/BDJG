<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Catalog
            'services.view', 'services.create', 'services.update', 'services.delete',
            'packages.view', 'packages.create', 'packages.update', 'packages.delete',
            'add_ons.view', 'add_ons.create', 'add_ons.update', 'add_ons.delete',
            // Client
            'clients.view', 'clients.create', 'clients.update', 'clients.delete',
            // Inquiry
            'inquiries.view', 'inquiries.create', 'inquiries.update', 'inquiries.delete', 'inquiries.assign',
            // Quotation
            'quotations.view', 'quotations.create', 'quotations.update', 'quotations.delete', 'quotations.send',
            // Invoice
            'invoices.view', 'invoices.create', 'invoices.update', 'invoices.delete', 'invoices.issue', 'invoices.void',
            // Payment
            'payments.view', 'payments.create', 'payments.refund',
            // Project
            'projects.view', 'projects.create', 'projects.update', 'projects.delete', 'projects.status',
            // Worker
            'workers.view', 'workers.create', 'workers.update', 'workers.delete',
            // Assignment
            'assignments.view', 'assignments.create', 'assignments.delete',
            // Task
            'tasks.view', 'tasks.create', 'tasks.update', 'tasks.delete', 'tasks.status',
            // Schedule
            'schedules.view', 'schedules.create', 'schedules.update', 'schedules.delete',
            // Media
            'media.view', 'media.create', 'media.update', 'media.delete', 'media.release',
            // Audit
            'audit.view',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $rolePermissions = [
            'OWNER' => $permissions,
            'ADMIN' => $permissions,
            'WORKER' => [
                'projects.view',
                'tasks.view', 'tasks.update', 'tasks.status',
                'schedules.view',
                'assignments.view',
                'media.view', 'media.create',
            ],
            'CLIENT' => [
                'clients.view', 'clients.update',
                'quotations.view',
                'invoices.view',
                'projects.view',
                'schedules.view',
                'media.view',
            ],
        ];

        foreach ($rolePermissions as $roleName => $perms) {
            $role = Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
            $role->syncPermissions($perms);
        }
    }
}
