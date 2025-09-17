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
        Schema::create("client_comments", function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger("client_id")->nullable()->index();
            $table->unsignedBigInteger("user_id")->nullable()->index();
            $table->text("comment");
            $table->string("title")->nullable();
            $table->string("status")->default("pending");
            $table->string("type")->default("comment");
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("client_comments");
    }
};
