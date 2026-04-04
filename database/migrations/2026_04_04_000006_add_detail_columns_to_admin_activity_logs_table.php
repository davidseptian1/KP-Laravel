<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('admin_activity_logs', function (Blueprint $table) {
            $table->string('action_type', 30)->default('request')->after('actor_role');
            $table->string('target_model', 180)->nullable()->after('path');
            $table->unsignedBigInteger('target_id')->nullable()->after('target_model');
            $table->text('change_summary')->nullable()->after('status_code');
            $table->json('before_data')->nullable()->after('request_data');
            $table->json('after_data')->nullable()->after('before_data');

            $table->index(['action_type', 'created_at']);
            $table->index(['target_model', 'target_id']);
        });
    }

    public function down(): void
    {
        Schema::table('admin_activity_logs', function (Blueprint $table) {
            $table->dropIndex('admin_activity_logs_action_type_created_at_index');
            $table->dropIndex('admin_activity_logs_target_model_target_id_index');

            $table->dropColumn([
                'action_type',
                'target_model',
                'target_id',
                'change_summary',
                'before_data',
                'after_data',
            ]);
        });
    }
};
