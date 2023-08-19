<?php

use App\Models\Elevator;
use App\Models\User;
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

        Schema::create('elevator_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Elevator::class)->constrained()->onDelete('cascade');
            $table->foreignIdFor(User::class)->nullable()->constrained();
            $table->bigInteger('current_floor');
            $table->string('state', 100)->default('idle');
            $table->string('direction', 100)->nullable();
            $table->string('action', 100)->nullable();
            $table->json('details')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('elevator_logs');
    }
};