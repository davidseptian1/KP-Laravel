<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('persediaan_stoks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('owner_name');
            $table->unsignedBigInteger('bank_id')->nullable();
            $table->string('account_number')->nullable();
            $table->string('account_name')->nullable();
            $table->timestamp('purchase_date')->nullable();
            $table->timestamp('receive_date')->nullable();
            $table->json('items'); // [{name, qty, price, subtotal}, ...]
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->string('on_behalf')->nullable();
            $table->string('transfer_proof_path')->nullable();
            $table->text('invoice_text')->nullable();
            $table->string('invoice_path')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('persediaan_stoks');
    }
};
