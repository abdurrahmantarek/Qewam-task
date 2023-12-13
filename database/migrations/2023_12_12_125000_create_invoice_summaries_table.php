<?php

use App\Models\Invoice;
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
        Schema::create('invoice_summaries', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(Invoice::class);
            $table->foreignIdFor(User::class);
            $table->string('number_of_registration');
            $table->string('number_of_activation');
            $table->string('number_of_appointment');
            $table->decimal('cost', 10, 2);
            $table->string('highest_cost_event');
            $table->string('reason_for_invoice');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invoice_summaries');
    }
};
