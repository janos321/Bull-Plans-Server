<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('Customers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('trainer_email', 255);
            $table->string('customer_email', 255);
            $table->longText('customer_data');
            $table->timestamp('update_time')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('Customers');
    }
};
