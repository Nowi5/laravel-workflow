<?php

declare(strict_types=1);

namespace Tests;

use Dotenv\Dotenv;
use Orchestra\Testbench\TestCase as BaseTestCase;
use Symfony\Component\Process\Process;
use \Workflow\Providers\WorkflowServiceProvider;

abstract class TestCase extends BaseTestCase
{
    protected $enablesPackageDiscoveries = true;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public static function setUpBeforeClass(): void
    {

    }

    public static function tearDownAfterClass(): void
    {

    }

    protected function defineDatabaseMigrations()
    {
        $this->artisan('migrate:fresh', [
            '--path' => dirname(__DIR__) . '/src/migrations',
            '--realpath' => true,
        ]);

        $this->loadLaravelMigrations();
    }

    protected function getPackageProviders($app)
    {
        return [WorkflowServiceProvider::class];
    }

}
