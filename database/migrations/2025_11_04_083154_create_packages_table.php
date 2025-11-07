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
        Schema::create('packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('description')->nullable();
            $table->float('price', 22, 2);
            $table->integer('location_count')->default(0);
            $table->integer('user_count')->default(0);
            $table->integer('product_count')->default(0);
            $table->integer('invoice_count')->default(0);
            $table->enum('interval', ['days', 'months', 'years'])->default('years');
            $table->integer('trial_days')->default(0);
            $table->integer('created_by')->nullable();
            $table->boolean('is_active')->default(false);
            $table->boolean('mark_package_as_popular')->default(false);
            $table->boolean('is_private')->default(false);
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('packages');
    }
};
