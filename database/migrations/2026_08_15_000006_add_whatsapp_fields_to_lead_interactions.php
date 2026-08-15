<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marketing_lead_interactions', function (Blueprint $table) {
            $table->string('direction')->default('internal');
            $table->string('external_id')->nullable()->unique();
            $table->json('meta')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('marketing_lead_interactions', function (Blueprint $table) {
            $table->dropUnique(['external_id']);
            $table->dropColumn(['direction', 'external_id', 'meta']);
        });
    }
};
