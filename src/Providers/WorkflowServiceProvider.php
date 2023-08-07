<?php

declare(strict_types=1);

namespace Workflow\Providers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\ServiceProvider;
use Laravel\SerializableClosure\SerializableClosure;

final class WorkflowServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        if (! class_exists('Workflow\Models\Basemodel')) {
            class_alias(config('workflows.base_model', Model::class), 'Workflow\Models\Basemodel');
        }

        \Illuminate\Support\Facades\Event::listen(
            \Workflow\Events\WorkflowActivityProcessedEvent::class,
            \Workflow\Listeners\WorkflowActivityProcessedListener::class,
        );

        $this->publishes([
            __DIR__ . '/../config/workflows.php' => config_path('workflows.php'),
        ], 'config');

        $this->publishes([
            __DIR__ . '/../migrations/' => database_path('/migrations'),
        ], 'migrations');
    }

    public function register(): void
    {
        //
    }
}
