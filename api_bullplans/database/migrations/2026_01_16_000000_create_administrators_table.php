<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('administrator', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name', 100);
            $table->string('password', 100);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('administrator');
    }
};
