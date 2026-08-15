<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marketing_campaigns', function (Blueprint $table) {
            $table->string('content_generation_status')->default('idle');
            $table->unsignedTinyInteger('content_generation_attempts')->default(0);
            $table->text('content_generation_error')->nullable();
            $table->timestamp('content_generation_started_at')->nullable();
            $table->timestamp('content_generation_completed_at')->nullable();
        });

        Schema::table('marketing_leads', function (Blueprint $table) {
            $table->string('scoring_status')->default('idle');
            $table->unsignedTinyInteger('scoring_attempts')->default(0);
            $table->text('scoring_error')->nullable();
            $table->timestamp('scoring_started_at')->nullable();
            $table->timestamp('scoring_completed_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('marketing_leads', function (Blueprint $table) {
            $table->dropColumn([
                'scoring_status',
                'scoring_attempts',
                'scoring_error',
                'scoring_started_at',
                'scoring_completed_at',
            ]);
        });

        Schema::table('marketing_campaigns', function (Blueprint $table) {
            $table->dropColumn([
                'content_generation_status',
                'content_generation_attempts',
                'content_generation_error',
                'content_generation_started_at',
                'content_generation_completed_at',
            ]);
        });
    }
};
