<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('prefects', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->text('full_description');
            $table->string('location');
            $table->string('status')->default('pending');
            $table->integer('esi_level')->nullable();
            $table->string('reported_by');
            $table->string('contact_info');
            $table->string('evidence_image')->nullable();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->boolean('archived')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('prefects');
    }
};