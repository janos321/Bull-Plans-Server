<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('Offers', function (Blueprint $table) {
            $table->increments('id');
            $table->longText('offers');
            $table->string('email', 255)->unique();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Offers');
    }
};
