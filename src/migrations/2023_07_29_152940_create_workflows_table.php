<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('workflows', static function (Blueprint $blueprint) : void {
            $blueprint->id();
            $blueprint->text('name');
            $blueprint->text('version');
            $blueprint->text('class');
            $blueprint->text('steps')
                ->nullable();
            $blueprint->text('logics')
                ->nullable();
            $blueprint->text('arguments')
                ->nullable();
            $blueprint->text('output')
                ->nullable();
            $blueprint->timestamp('start_at', 6)
                ->nullable();
            $blueprint->timestamp('stop_at', 6)
                ->nullable();
            $blueprint->integer('duration')->nullable();
            $blueprint->string('state')
                ->default('created')
                ->index();
            $blueprint->timestamps();
        });

        Schema::create('workflow_activities', static function (Blueprint $blueprint) : void {
            $blueprint->id();
            $blueprint->unsignedBigInteger('workflow_id');
            $blueprint->integer('index');
            $blueprint->text('stepidentifier');
            $blueprint->text('name');
            $blueprint->text('version');
            $blueprint->text('class');
            $blueprint->text('arguments')
                ->nullable();
            $blueprint->text('output')
                ->nullable();
            $blueprint->string('state')
                ->default('created')
                ->index();
            $blueprint->timestamp('start_at', 6)
                ->nullable();
            $blueprint->timestamp('stop_at', 6)
                ->nullable();
            $blueprint->integer('duration')->nullable();
            $blueprint->timestamps();
            $blueprint->foreign('workflow_id')->references('id')->on('workflows');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workflows');
        Schema::dropIfExists('workflow_activities');
    }
};
