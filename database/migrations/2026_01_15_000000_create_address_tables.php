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
        // Create provinces table
        Schema::create('address_provinces', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->timestamps();
        });

        // Create cities table
        Schema::create('address_cities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('province_id')->constrained('address_provinces')->onDelete('cascade');
            $table->string('name');
            $table->string('zip_code')->nullable();
            $table->unique(['province_id', 'name']);
            $table->timestamps();
        });

        // Create barangays table
        Schema::create('address_barangays', function (Blueprint $table) {
            $table->id();
            $table->foreignId('city_id')->constrained('address_cities')->onDelete('cascade');
            $table->string('name');
            $table->unique(['city_id', 'name']);
            $table->timestamps();
        });

        // Create puroks table
        Schema::create('address_puroks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('barangay_id')->constrained('address_barangays')->onDelete('cascade');
            $table->string('name');
            $table->unique(['barangay_id', 'name']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('address_puroks');
        Schema::dropIfExists('address_barangays');
        Schema::dropIfExists('address_cities');
        Schema::dropIfExists('address_provinces');
    }
};
