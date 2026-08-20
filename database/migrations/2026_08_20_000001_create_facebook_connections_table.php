<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('facebook_connections', function (Blueprint $table) {
            $table->id();
            $table->string('page_id')->unique();
            $table->string('page_name');
            $table->text('access_token');
            $table->foreignId('connected_by')->nullable()->constrained('admin_users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('facebook_connections');
    }
};
