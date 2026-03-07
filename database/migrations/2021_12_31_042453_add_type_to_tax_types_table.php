<?php

use App\Enums\TaxTypeType;
use App\Models\TaxType;
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
        Schema::table('tax_types', function (Blueprint $table) {
            $table->enum('type', ['GENERAL', 'MODULE'])->default(TaxTypeType::General->value);
        });

        $taxTypes = TaxType::all();

        if ($taxTypes) {
            foreach ($taxTypes as $taxType) {
                $taxType->type = TaxTypeType::General;
                $taxType->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tax_types', function (Blueprint $table) {
            $table->dropColumn('type');
        });
    }
};
