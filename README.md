# Laravel Workflow Jobs Package

The Laravel Workflow Jobs package allows you to effortlessly create workflows that run as jobs with robust logging capabilities. Use this package to design, execute, and monitor workflow processes using Laravel jobs and enjoy a seamless experience with dynamic parameter passing and custom logic evaluations.

## Installation

1. Install the package via composer:
   ```
   composer require laravel-workflow-jobs-package
   ```

2. Publish the config file:
   ```
   php artisan vendor:publish --provider="Workflow\WorkflowServiceProvider"
   ```

3. Run migrations (if needed) to set up the required tables for logging and management:
   ```
   php artisan migrate
   ```

## Usage

### Creating a Workflow

To create a workflow, you can define it as shown in the example:

```php
namespace  App\Workflow;

use App\Jobs\WorkflowStepJob;
use Workflow\Workflow;

class ExampleWorkflow extends Workflow {

    public $name = "TestWorkflow";
    public $version = "1.0.0";

    public function execute(){
        // Adding steps to the workflow
        $this->addStep("step1", WorkflowStepJob::class, ["param1" => "some value"]);

        // Setting the logic for the workflow
        $this->addLogic("step1", "step2");
    }
}
```

### Running a Workflow

After defining your workflow, you can start and fetch its JSON representation:

```php
$workflow = new ExampleWorkflow();
$workflow->start("Hello Test");
$workflowjson = $workflow->getJson();
return response("<pre><code>".$workflowjson."</code></pre>", 200);
```

### Creating a Workflow Step (Job)

Each step in a workflow corresponds to a Laravel job. To create a custom workflow job:

```php
namespace App\Jobs;

use Workflow\Jobs\WorkflowJob;

class WorkflowStepJob extends WorkflowJob {

    public $name = "Example Step Name"; // Keep this unique
    public $version = "1.0.0";

    public function execute() {
        $content = "Hello Custom Workflow, " . now() . "\n";
        return ['content' => $content];
    }
}
```

## Dynamic Parameter Passing

You can pass parameters from one step's output to another's input dynamically using the format `%stepName.parameterName%`:

```php
$this->addStep("step2", WorkflowStepJob::class, ["param1" => "%step1.randomNumber%"]);
```

## Custom Logic Evaluation

You can also define custom logic evaluations to determine which step to execute next based on the output of the previous steps:
Defining own logic for the workflow as well as adding logic at all is optional. If no logic is defined, the workflow will execute all steps in the order they were added.
```php
$this->addLogic("step3", null, [
["evaluate" => "%step2.outputParameterName%", "comparison" => "<50", "next" => "step5"],
["evaluate" => "%step2.outputParameterName%", "comparison" => ">=50", "next" => "step4"]
]);
```

## Contributing

If you'd like to contribute to this project, please submit a PR or open an issue. We appreciate any feedback or improvements!

## License

This package is open-source software licensed under the MIT license.

## Notes
<small>"Laravel" is a registered trademark of Taylor Otwell. This project is not affiliated, associated, endorsed, or sponsored by Taylor Otwell, nor has it been reviewed, tested, or certified by Taylor Otwell. The use of the trademark "Laravel" is for informational and descriptive purposes only. Laravel Workflow is not officially related to the Laravel trademark or Taylor Otwell.</small>
