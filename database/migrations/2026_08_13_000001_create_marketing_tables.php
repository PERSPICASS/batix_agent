<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('marketing_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('channel')->default('facebook');
            $table->string('status')->default('draft');
            $table->text('objective');
            $table->text('audience');
            $table->text('offer')->nullable();
            $table->decimal('daily_budget', 12, 2)->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();
            $table->json('metrics')->nullable();
            $table->timestamps();
        });

        Schema::create('marketing_contents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('marketing_campaign_id')->nullable()->constrained()->nullOnDelete();
            $table->string('channel')->default('facebook');
            $table->string('format')->default('post');
            $table->string('status')->default('draft');
            $table->string('title')->nullable();
            $table->text('hook')->nullable();
            $table->longText('body');
            $table->string('cta')->nullable();
            $table->timestamp('scheduled_at')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->json('meta')->nullable();
            $table->timestamps();
        });

        Schema::create('marketing_leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('marketing_campaign_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('phone', 40);
            $table->string('company')->nullable();
            $table->string('business_type')->nullable();
            $table->string('source')->default('whatsapp');
            $table->string('status')->default('new');
            $table->unsignedTinyInteger('score')->default(0);
            $table->text('notes')->nullable();
            $table->text('ai_summary')->nullable();
            $table->text('ai_next_action')->nullable();
            $table->text('whatsapp_script')->nullable();
            $table->timestamp('last_contact_at')->nullable();
            $table->timestamp('scored_at')->nullable();
            $table->timestamps();
            $table->index(['status', 'score']);
            $table->index('phone');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_leads');
        Schema::dropIfExists('marketing_contents');
        Schema::dropIfExists('marketing_campaigns');
    }
};
