<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->foreign('head_id')
                  ->references('id')
                  ->on('employees')
                  ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('departments', function (Blueprint $table) {
            $table->dropForeign(['head_id']);
        });
    }
};
