<?php

use App\Models\Elevator;
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
        Schema::create('pending_elevator_calls', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Elevator::class)->constrained()->onDelete('cascade');
            $table->integer('target_floor');
            $table->boolean('executed')->default(false);
            $table->bigInteger('execution_duration')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pending_elevator_calls');
    }
};