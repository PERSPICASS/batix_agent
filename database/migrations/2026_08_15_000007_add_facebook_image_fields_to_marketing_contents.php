<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('marketing_contents', function (Blueprint $table) {
            $table->string('image_path')->nullable();
            $table->text('image_prompt')->nullable();
            $table->string('image_generation_status')->default('idle');
            $table->unsignedTinyInteger('image_generation_attempts')->default(0);
            $table->text('image_generation_error')->nullable();
            $table->timestamp('image_generated_at')->nullable();
            $table->string('facebook_post_id')->nullable();
            $table->text('facebook_publish_error')->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('marketing_contents', function (Blueprint $table) {
            $table->dropColumn([
                'image_path',
                'image_prompt',
                'image_generation_status',
                'image_generation_attempts',
                'image_generation_error',
                'image_generated_at',
                'facebook_post_id',
                'facebook_publish_error',
            ]);
        });
    }
};
