<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->boolean('held_in_escrow')->default(false)->after('bundle_id');
            $table->timestamp('escrow_released_at')->nullable()->after('held_in_escrow');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['held_in_escrow', 'escrow_released_at']);
        });
    }
};
