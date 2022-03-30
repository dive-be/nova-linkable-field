<?php declare(strict_types=1);

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class() extends Migration {
    public function up()
    {
        Schema::create('nav_items', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->string('url')->nullable();
        });
    }
};
