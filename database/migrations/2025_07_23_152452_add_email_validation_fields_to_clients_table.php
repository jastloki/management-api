<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table("clients", function (Blueprint $table) {
            // Add email validation tracking fields if they don't exist
            if (!Schema::hasColumn("clients", "email_validation_reason")) {
                $table
                    ->text("email_validation_reason")
                    ->nullable()
                    ->after("email_status");
            }

            if (!Schema::hasColumn("clients", "email_validation_details")) {
                $table
                    ->json("email_validation_details")
                    ->nullable()
                    ->after("email_validation_reason");
            }

            if (!Schema::hasColumn("clients", "email_last_validated_at")) {
                $table
                    ->timestamp("email_last_validated_at")
                    ->nullable()
                    ->after("email_validation_details");
            }

            if (!Schema::hasColumn("clients", "email_validation_attempts")) {
                $table
                    ->integer("email_validation_attempts")
                    ->default(0)
                    ->after("email_last_validated_at");
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table("clients", function (Blueprint $table) {
            $table->dropColumn([
                "email_validation_reason",
                "email_validation_details",
                "email_last_validated_at",
                "email_validation_attempts",
            ]);
        });
    }
};
