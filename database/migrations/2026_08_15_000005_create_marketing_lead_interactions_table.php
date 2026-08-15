<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('marketing_lead_interactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('marketing_lead_id')->constrained()->cascadeOnDelete();
            $table->foreignId('admin_user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type')->default('note');
            $table->text('body');
            $table->timestamp('occurred_at')->useCurrent();
            $table->timestamps();
            $table->index(['marketing_lead_id', 'occurred_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('marketing_lead_interactions');
    }
};
