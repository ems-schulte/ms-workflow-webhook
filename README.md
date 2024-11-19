# MS Workflow Webhook

This Composer package allows integration of workflows in Microsoft Teams and triggers webhooks. With this component, users in a Microsoft Teams environment can perform automated actions by sending webhooks that are embedded in workflows. Ideal for connecting external systems or triggering processes directly from Microsoft Teams.

## Installation

To install this package, use Composer:

```sh
composer require ems-schulte/ms-workflow-webhook
```

## Usage

Here is an example of how to use the TeamsWebhook class to send an Adaptive Card to a Microsoft Teams channel:

```php
require 'vendor/autoload.php';

use EmsSchulte\MsWorkflowWebhook\TeamsWebhook;

// Replace this with your actual webhook URL
$webhookUrl = 'https://your-webhook-url';

$teamsWebhook = new TeamsWebhook($webhookUrl);

$description = 'Test description';
$properties = [
    ['key' => 'Property1', 'value' => 'Value1'],
    ['key' => 'Property2', 'value' => 'Value2']
];

$response = $teamsWebhook->sendCard($description, $properties);

echo "Response: " . $response;
```