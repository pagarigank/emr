<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PermissionSeeder extends Seeder
{
    private array $permissionDefs = [];

    private array $roleMap = [];

    public function run(): void
    {
        $this->definePermissions();
        $this->defineRoleMappings();

        $now = now();
        $uid = 1;

        // Upsert permissions
        $createdIds = [];
        foreach ($this->permissionDefs as $name => $def) {
            $perm = Permission::updateOrCreate(
                ['name' => $name],
                [
                    'display_name' => $def[0],
                    'module' => $def[1],
                    'guard_name' => 'web',
                    'description' => $def[2] ?? null,
                    'is_active' => true,
                    'history' => 1,
                    'created_by' => $uid,
                    'created_date' => $now,
                    'updated_by' => $uid,
                    'updated_date' => $now,
                ]
            );
            $createdIds[$name] = $perm->id;
        }

        // Look up role IDs from the roles table
        $roleIds = DB::table('roles')->pluck('id', 'name');

        // Clear existing role_permissions
        DB::table('role_permissions')->truncate();

        // Insert role→permission mappings
        $inserts = [];
        foreach ($this->roleMap as $role => $permNames) {
            $roleId = $roleIds[$role] ?? null;
            foreach ($permNames as $permName) {
                if (isset($createdIds[$permName])) {
                    $inserts[] = [
                        'role'          => $role,
                        'role_id'       => $roleId,
                        'permission_id' => $createdIds[$permName],
                        'tenant_id'     => null,
                        'created_date'  => $now,
                    ];
                }
            }
        }

        DB::table('role_permissions')->insert($inserts);

        $this->command->info('Seeded ' . count($createdIds) . ' permissions and ' . count($inserts) . ' role-permission assignments.');
    }

    private function definePermissions(): void
    {
        $this->permissionDefs = [
            // Dashboard
            'dashboard.view' => ['View Dashboard', 'Dashboard'],

            // Patients
            'patient.view' => ['View Patients', 'Patient'],
            'patient.create' => ['Create Patients', 'Patient'],
            'patient.edit' => ['Edit Patients', 'Patient'],

            // Medical Records
            'medical-records.view' => ['View Medical Records', 'Medical Records'],

            // OPD
            'opd.view' => ['View OPD Consultations', 'OPD'],
            'opd.create' => ['Create OPD Consultations', 'OPD'],
            'opd.edit' => ['Edit OPD Consultations', 'OPD'],

            // ER
            'er.triage' => ['Perform ER Triage', 'ER'],
            'er.manage' => ['Manage ER Admissions', 'ER'],
            'er.disposition' => ['Set ER Disposition', 'ER'],

            // Admission
            'admission.view' => ['View Admissions', 'Admission'],
            'admission.create' => ['Create Admissions', 'Admission'],
            'admission.discharge' => ['Discharge Patients', 'Admission'],

            // Nursing
            'nursing.view' => ['View Nursing Notes', 'Nursing'],
            'nursing.manage' => ['Manage Nursing Care', 'Nursing'],

            // OB
            'ob.view' => ['View OB Consultations', 'OB'],
            'ob.create' => ['Create OB Consultations', 'OB'],

            // Delivery
            'delivery.view' => ['View Delivery Records', 'Delivery'],
            'delivery.manage' => ['Manage Deliveries', 'Delivery'],

            // OR
            'or.view' => ['View OR Schedules', 'OR'],
            'or.schedule' => ['Schedule OR Procedures', 'OR'],

            // Renal Care
            'renal.view' => ['View Dialysis Sessions', 'Renal Care'],
            'renal.manage' => ['Manage Dialysis Sessions', 'Renal Care'],

            // Laboratory
            'lab.view' => ['View Lab Requests', 'Laboratory'],
            'lab.request' => ['Request Lab Tests', 'Laboratory'],
            'lab.dispense' => ['Dispense Lab Tests', 'Laboratory'],
            'lab.result' => ['Enter Lab Results', 'Laboratory'],
            'lab.manage' => ['Manage Lab Master Data', 'Laboratory'],

            // Radiology
            'radiology.view' => ['View Radiology Requests', 'Radiology'],
            'radiology.request' => ['Request Radiology Exams', 'Radiology'],
            'radiology.dispense' => ['Dispense Radiology Exams', 'Radiology'],
            'radiology.result' => ['Enter Radiology Results', 'Radiology'],
            'radiology.manage' => ['Manage Radiology Master Data', 'Radiology'],

            // Pharmacy
            'pharmacy.view' => ['View Pharmacy Requests', 'Pharmacy'],
            'pharmacy.request' => ['Request Medications', 'Pharmacy'],
            'pharmacy.dispense' => ['Dispense Medications', 'Pharmacy'],
            'pharmacy.manage' => ['Manage Pharmacy Master Data', 'Pharmacy'],

            // Procedures
            'procedure.view' => ['View Procedure Requests', 'Procedures'],
            'procedure.request' => ['Request Procedures', 'Procedures'],
            'procedure.dispense' => ['Perform Procedures', 'Procedures'],
            'procedure.manage' => ['Manage Procedure Master Data', 'Procedures'],

            // CSR / Supply
            'csr.view' => ['View CSR Requests', 'CSR/Supply'],
            'csr.request' => ['Request Supplies', 'CSR/Supply'],
            'csr.dispense' => ['Dispense Supplies', 'CSR/Supply'],
            'csr.manage' => ['Manage CSR Master Data', 'CSR/Supply'],

            // Billing
            'billing.view' => ['View Billing Records', 'Billing'],
            'billing.create' => ['Create Invoices', 'Billing'],
            'billing.payment' => ['Record Payments', 'Billing'],
            'billing.cancel' => ['Cancel Invoices', 'Billing'],
            'billing.manage' => ['Manage Billing Master Data', 'Billing'],

            // PhilHealth
            'philhealth.view' => ['View PhilHealth Records', 'PhilHealth'],
            'philhealth.manage' => ['Manage PhilHealth Claims', 'PhilHealth'],

            // YAKAP
            'yakap.view' => ['View YAKAP Records', 'YAKAP'],
            'yakap.manage' => ['Manage YAKAP', 'YAKAP'],

            // Reports
            'reports.view' => ['View Reports', 'Reports'],
            'reports.manage' => ['Manage Reports', 'Reports'],

            // Administration
            'admin.users' => ['Manage Users', 'Administration'],
            'admin.departments' => ['Manage Departments', 'Administration'],
            'admin.tenants' => ['Manage Tenants', 'Administration'],
            'admin.masters' => ['Manage Master Data', 'Administration'],
            'admin.settings' => ['Manage Settings', 'Administration'],

            // Doctors Portal
            'doctors.dashboard' => ['View Doctors Dashboard', 'Doctors Portal'],
            'doctors.chart' => ['View Patient Chart', 'Doctors Portal'],
            'doctors.orders' => ['Place Orders', 'Doctors Portal'],
            'doctors.schedule' => ['Manage Appointment Schedule', 'Doctors Portal'],
        ];
    }

    private function defineRoleMappings(): void
    {
        $all = array_keys($this->permissionDefs);

        $clinical = [
            'patient.view', 'patient.create', 'patient.edit',
            'medical-records.view',
            'opd.view', 'opd.create', 'opd.edit',
            'er.triage', 'er.manage', 'er.disposition',
            'admission.view', 'admission.create', 'admission.discharge',
            'nursing.view', 'nursing.manage',
            'ob.view', 'ob.create',
            'delivery.view', 'delivery.manage',
            'or.view', 'or.schedule',
            'renal.view', 'renal.manage',
            'lab.view', 'lab.request',
            'radiology.view', 'radiology.request',
            'pharmacy.view', 'pharmacy.request',
            'procedure.view', 'procedure.request',
            'csr.view', 'csr.request',
        ];

        $labTech = [
            'patient.view',
            'lab.view', 'lab.dispense', 'lab.result',
        ];

        $radTech = [
            'patient.view',
            'radiology.view', 'radiology.dispense', 'radiology.result',
        ];

        $pharmTech = [
            'patient.view',
            'pharmacy.view', 'pharmacy.dispense',
        ];

        $billingStaff = [
            'patient.view',
            'billing.view', 'billing.create', 'billing.payment',
        ];

        $doctor = [
            'dashboard.view',
            'patient.view', 'patient.create',
            'medical-records.view',
            'opd.view', 'opd.create',
            'er.triage', 'er.manage', 'er.disposition',
            'admission.view', 'admission.create', 'admission.discharge',
            'nursing.view',
            'ob.view', 'ob.create',
            'or.view', 'or.schedule',
            'renal.view',
            'lab.view', 'lab.request',
            'radiology.view', 'radiology.request',
            'pharmacy.view', 'pharmacy.request',
            'procedure.view', 'procedure.request',
            'csr.view', 'csr.request',
            'doctors.dashboard', 'doctors.chart', 'doctors.orders', 'doctors.schedule',
        ];

        $this->roleMap = [
            'superadmin' => $all,
            'tenant_admin' => $all,
            'staff' => array_values(array_unique(array_merge(
                ['dashboard.view'],
                $clinical,
                $labTech,
                $radTech,
                $pharmTech,
                $billingStaff,
                ['billing.cancel', 'billing.manage'],
                ['philhealth.view', 'philhealth.manage'],
                ['yakap.view', 'yakap.manage'],
                ['reports.view', 'reports.manage'],
            ))),
            'doctor' => $doctor,
        ];
    }
}
