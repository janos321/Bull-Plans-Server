<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('Motivations', function (Blueprint $table) {
            $table->increments('id');
            $table->date('dateTime');
            $table->longText('motivation');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Motivations');
    }
};
