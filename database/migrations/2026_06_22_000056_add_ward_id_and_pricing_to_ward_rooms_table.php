<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('ward_rooms', function (Blueprint $table) {
            $table->unsignedBigInteger('ward_id')->nullable()->after('name');
            $table->decimal('room_rate', 12, 2)->default(0)->after('capacity');
            $table->decimal('extra_bed_rate', 12, 2)->nullable()->after('room_rate');
            $table->decimal('discount_rate', 5, 2)->nullable()->after('extra_bed_rate');
            $table->foreign('ward_id', 'fk_ward_rooms_ward')->references('id')->on('wards')->onDelete('set null');
            $table->index('ward_id', 'idx_ward_rooms_ward');
        });
    }

    public function down(): void
    {
        Schema::table('ward_rooms', function (Blueprint $table) {
            $table->dropForeign('fk_ward_rooms_ward');
            $table->dropIndex('idx_ward_rooms_ward');
            $table->dropColumn(['ward_id', 'room_rate', 'extra_bed_rate', 'discount_rate']);
        });
    }
};
