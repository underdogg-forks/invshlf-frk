<?php

use App\Enums\PaymentMethodType;
use App\Models\PaymentMethod;
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
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->string('driver')->nullable();
            $table->enum('type', ['GENERAL', 'MODULE'])->default(PaymentMethodType::General->value);
            $table->json('settings')->nullable();
            $table->boolean('active')->default(false);
            $table->boolean('use_test_env')->default(false);
        });

        $paymentMethods = PaymentMethod::all();

        if ($paymentMethods) {
            foreach ($paymentMethods as $paymentMethod) {
                $paymentMethod->type = PaymentMethodType::General;
                $paymentMethod->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payment_methods', function (Blueprint $table) {
            $table->dropColumn([
                'driver',
                'type',
                'settings',
                'active',
            ]);
        });
    }
};
