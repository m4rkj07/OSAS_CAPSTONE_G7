<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('reports', function (Blueprint $table) {
            $table->id();
            $table->string('description');
            $table->text('full_description');
            $table->string('location');
            $table->string('status')->default('pending')->change();
            $table->string('reported_by');
            $table->string('contact_info');
            $table->string('evidence_image')->nullable();
            $table->integer('esi_level')->nullable();
            $table->boolean('archived')->default(false);
            $table->timestamp('created_at')->default(DB::raw('CURRENT_TIMESTAMP'))->useCurrent();
            $table->timestamp('updated_at')->default(DB::raw('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'))->useCurrentOnUpdate();

        });
    }

    public function down(): void
    {
        Schema::table('reports', function (Blueprint $table) {
            $table->dropColumn('evidence_image');
        });
        Schema::dropIfExists('reports');
    }
};
