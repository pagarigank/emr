<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('claim_procedures', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->index();
            $table->unsignedBigInteger('philhealth_claim_id')->index();
            $table->integer('sequence')->default(1);
            $table->text('procedure_description')->nullable();
            $table->string('rvs_code', 10)->nullable()->index();
            $table->date('procedure_date')->nullable();
            $table->string('laterality', 20)->nullable()
                ->comment('Left, Right, Bilateral');
            $table->string('special_consideration', 50)->nullable()
                ->comment('cataract, zbenefit, maternal, etc.');
            $table->boolean('is_active')->default(true);
            $table->tinyInteger('history')->default(1);
            $table->unsignedBigInteger('created_by')->nullable();
            $table->timestamp('created_date')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamp('updated_date')->nullable();

            $table->foreign('philhealth_claim_id', 'fk_claim_proc_claim')
                ->references('id')->on('philhealth_claims')->onDelete('CASCADE');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('claim_procedures');
    }
};
