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
        Schema::create("clients", function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->string("email")->unique();
            $table->string("phone")->nullable();
            $table->string("company")->nullable();
            $table->text("address")->nullable();
            $table->unsignedBigInteger("status_id")->nullable()->index();
            $table->unsignedBigInteger("user_id")->nullable()->index();
            $table->boolean("is_email_valid")->default(false);
            $table->string("email_status")->nullable();
            $table->dateTime("email_sent_at")->nullable();
            $table->boolean("converted")->default(false);
            $table->string("imported_from")->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("clients");
    }
};
