<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('redirect_data', function (Blueprint $table) {
            $table->id();
            $table->foreignId('short_url_id')->constrained()->onDelete('cascade');
            $table->string('ip_address', 45);
            $table->timestamp('clicked_at')->useCurrent()->index();
            $table->string('geo_location')->nullable()->index();
            $table->string('user_agent', 255)->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('redirect_data');
    }
};
