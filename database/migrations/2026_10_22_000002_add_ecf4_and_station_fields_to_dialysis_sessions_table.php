<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dialysis_sessions', function (Blueprint $table) {
            if (!Schema::hasColumn('dialysis_sessions', 'dialysis_machine_id')) {
                $table->unsignedBigInteger('dialysis_machine_id')->nullable()->after('dialysis_type_id');
                $table->index('dialysis_machine_id');
            }
            if (!Schema::hasColumn('dialysis_sessions', 'shift')) {
                $table->string('shift', 30)->nullable()->after('dialysis_machine_id')->comment('Shift 1 (AM), Shift 2 (Mid), Shift 3 (PM), Emergency');
            }
            if (!Schema::hasColumn('dialysis_sessions', 'session_number')) {
                $table->integer('session_number')->nullable()->after('shift')->comment('Session # of 156 statutory PhilHealth allowance');
            }
            if (!Schema::hasColumn('dialysis_sessions', 'attending_physician_id')) {
                $table->unsignedBigInteger('attending_physician_id')->nullable()->after('session_number');
                $table->index('attending_physician_id');
            }
            if (!Schema::hasColumn('dialysis_sessions', 'dialysis_nurse_id')) {
                $table->unsignedBigInteger('dialysis_nurse_id')->nullable()->after('attending_physician_id');
            }
            if (!Schema::hasColumn('dialysis_sessions', 'admission_id')) {
                $table->unsignedBigInteger('admission_id')->nullable()->after('dialysis_nurse_id');
                $table->index('admission_id');
            }
            if (!Schema::hasColumn('dialysis_sessions', 'er_admission_id')) {
                $table->unsignedBigInteger('er_admission_id')->nullable()->after('admission_id');
                $table->index('er_admission_id');
            }
            if (!Schema::hasColumn('dialysis_sessions', 'procedure_request_id')) {
                $table->unsignedBigInteger('procedure_request_id')->nullable()->after('er_admission_id');
                $table->index('procedure_request_id');
            }
            if (!Schema::hasColumn('dialysis_sessions', 'access_site')) {
                $table->string('access_site', 100)->nullable()->after('access_type');
            }
            if (!Schema::hasColumn('dialysis_sessions', 'dry_weight')) {
                $table->decimal('dry_weight', 6, 2)->nullable()->after('post_weight');
            }
            if (!Schema::hasColumn('dialysis_sessions', 'uf_goal_liters')) {
                $table->decimal('uf_goal_liters', 6, 2)->nullable()->after('dry_weight');
            }
            if (!Schema::hasColumn('dialysis_sessions', 'uf_actual_liters')) {
                $table->decimal('uf_actual_liters', 6, 2)->nullable()->after('uf_goal_liters');
            }
            if (!Schema::hasColumn('dialysis_sessions', 'blood_flow_rate_qb')) {
                $table->integer('blood_flow_rate_qb')->nullable()->after('uf_actual_liters')->comment('QB in mL/min');
            }
            if (!Schema::hasColumn('dialysis_sessions', 'dialysate_flow_rate_qd')) {
                $table->integer('dialysate_flow_rate_qd')->nullable()->after('blood_flow_rate_qb')->comment('QD in mL/min');
            }
            if (!Schema::hasColumn('dialysis_sessions', 'dialysate_bath')) {
                $table->string('dialysate_bath', 100)->nullable()->after('dialysate_flow_rate_qd');
            }
            if (!Schema::hasColumn('dialysis_sessions', 'anticoagulation_type')) {
                $table->string('anticoagulation_type', 50)->nullable()->after('dialysate_bath');
            }
            if (!Schema::hasColumn('dialysis_sessions', 'heparin_dose_units')) {
                $table->integer('heparin_dose_units')->nullable()->after('anticoagulation_type');
            }
            if (!Schema::hasColumn('dialysis_sessions', 'dialyzer_model')) {
                $table->string('dialyzer_model', 100)->nullable()->after('heparin_dose_units');
            }
            if (!Schema::hasColumn('dialysis_sessions', 'dialyzer_reuse_number')) {
                $table->integer('dialyzer_reuse_number')->default(0)->nullable()->after('dialyzer_model');
            }
            if (!Schema::hasColumn('dialysis_sessions', 'pre_temp')) {
                $table->decimal('pre_temp', 4, 1)->nullable()->after('post_bp');
            }
            if (!Schema::hasColumn('dialysis_sessions', 'post_temp')) {
                $table->decimal('post_temp', 4, 1)->nullable()->after('pre_temp');
            }
            if (!Schema::hasColumn('dialysis_sessions', 'pre_rr')) {
                $table->integer('pre_rr')->nullable()->after('post_temp');
            }
            if (!Schema::hasColumn('dialysis_sessions', 'post_rr')) {
                $table->integer('post_rr')->nullable()->after('pre_rr');
            }
            if (!Schema::hasColumn('dialysis_sessions', 'complications')) {
                $table->string('complications', 255)->nullable()->after('post_rr');
            }
        });
    }

    public function down(): void
    {
        Schema::table('dialysis_sessions', function (Blueprint $table) {
            $cols = [
                'dialysis_machine_id', 'shift', 'session_number', 'attending_physician_id', 'dialysis_nurse_id',
                'admission_id', 'er_admission_id', 'procedure_request_id', 'access_site', 'dry_weight',
                'uf_goal_liters', 'uf_actual_liters', 'blood_flow_rate_qb', 'dialysate_flow_rate_qd',
                'dialysate_bath', 'anticoagulation_type', 'heparin_dose_units', 'dialyzer_model',
                'dialyzer_reuse_number', 'pre_temp', 'post_temp', 'pre_rr', 'post_rr', 'complications'
            ];
            foreach ($cols as $col) {
                if (Schema::hasColumn('dialysis_sessions', $col)) {
                    $table->dropColumn($col);
                }
            }
        });
    }
};
