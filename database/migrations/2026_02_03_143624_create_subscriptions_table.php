<?php

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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('plan_type', ['physical', 'virtual'])->comment('Tipo de assinatura: física ou virtual');
            $table->enum('status', ['pending', 'active', 'expired', 'cancelled', 'suspended'])->default('pending');
            $table->date('purchase_date')->comment('Data da compra');
            $table->date('start_date')->nullable()->comment('Data de início da assinatura');
            $table->date('end_date')->nullable()->comment('Data de término/expiração da assinatura');
            $table->date('renewal_date')->nullable()->comment('Data de renovação automática');
            $table->string('pagbank_transaction_id')->nullable()->comment('ID da transação no PagBank');
            $table->string('pagbank_subscription_id')->nullable()->comment('ID da assinatura no PagBank');
            $table->string('payment_method')->nullable()->comment('Método de pagamento: credit_card, pix, boleto');
            $table->decimal('amount', 10, 2)->comment('Valor pago');
            $table->text('notes')->nullable()->comment('Observações adicionais');
            $table->timestamps();
            
            // Índices para melhor performance
            $table->index('user_id');
            $table->index('status');
            $table->index('plan_type');
            $table->index('end_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
