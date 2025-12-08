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
        Schema::table('users', function (Blueprint $table) {
            // Align FK types with existing provinces/cities `increments` (unsigned int)
            if (!Schema::hasColumn('users', 'province_id')) {
                $table->unsignedInteger('province_id')->nullable();
                $table->foreign('province_id')
                    ->references('id')->on('provinces')
                    ->cascadeOnUpdate()->cascadeOnDelete();
            }

            if (!Schema::hasColumn('users', 'city_id')) {
                $table->unsignedInteger('city_id')->nullable();
                $table->foreign('city_id')
                    ->references('id')->on('cities')
                    ->cascadeOnUpdate()->cascadeOnDelete();
            }

            if (!Schema::hasColumn('users', 'address')) {
                $table->string('address')->nullable();
            }

            if (!Schema::hasColumn('users', 'postal_code')) {
                $table->string('postal_code')->nullable();
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'province_id')) {
                $table->dropForeign(['province_id']);
                $table->dropColumn('province_id');
            }

            if (Schema::hasColumn('users', 'city_id')) {
                $table->dropForeign(['city_id']);
                $table->dropColumn('city_id');
            }

            if (Schema::hasColumn('users', 'address')) {
                $table->dropColumn('address');
            }

            if (Schema::hasColumn('users', 'postal_code')) {
                $table->dropColumn('postal_code');
            }
        });
    }
};
