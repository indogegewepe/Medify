<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('master_items', function (Blueprint $table) {
            $table->foreignId('kategori_id')
                ->nullable()
                ->constrained('kategori_items')
                ->restrictOnDelete();
        });
    }

    public function down()
    {
        if (Schema::getConnection()->getDriverName() === 'sqlite') {
            Schema::table('master_items', function (Blueprint $table) {
                $table->dropColumn('kategori_id');
            });

            return;
        }

        Schema::table('master_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('kategori_id');
        });
    }
};
