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
        Schema::table('identity_documents', function (Blueprint $table) {
            $table->timestamp('approved_at')->nullable()->after('status'); // Add approved_at column
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('identity_documents', function (Blueprint $table) {
            $table->dropColumn('approved_at'); // Drop approved_at column
        });
    }
};
