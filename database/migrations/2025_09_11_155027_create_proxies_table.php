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
        Schema::create("proxies", function (Blueprint $table) {
            $table->id();
            $table->string("name");
            $table->string("url");
            $table->string("type")->default("http"); // http, https, socks4, socks5
            $table->integer("port")->nullable();
            $table->string("username")->nullable();
            $table->string("password")->nullable();
            $table->string("country")->nullable();
            $table->string("city")->nullable();
            $table->boolean("is_active")->default(true);
            $table->text("description")->nullable();
            $table->json("extra_fields")->nullable();
            $table->timestamp("last_tested_at")->nullable();
            $table->string("status")->default("untested"); // untested, working, failed
            $table->integer("response_time")->nullable(); // in milliseconds
            $table->timestamps();

            $table->index(["is_active", "status"]);
            $table->index("type");
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists("proxies");
    }
};
