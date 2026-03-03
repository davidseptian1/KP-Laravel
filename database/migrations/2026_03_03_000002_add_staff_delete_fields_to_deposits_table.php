<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('deposits', function (Blueprint $table) {
            $table->boolean('is_deleted_by_staff')->default(false)->after('status');
            $table->text('staff_deleted_note')->nullable()->after('is_deleted_by_staff');
            $table->timestamp('staff_deleted_at')->nullable()->after('staff_deleted_note');
        });
    }

    public function down(): void
    {
        Schema::table('deposits', function (Blueprint $table) {
            $table->dropColumn([
                'is_deleted_by_staff',
                'staff_deleted_note',
                'staff_deleted_at',
            ]);
        });
    }
};
