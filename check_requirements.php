<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Login as the first company user
$user = \App\Models\User::where('type', 'company')->first();
if ($user) {
    \Auth::login($user);
}

$targetStageId = 149;
$targetStage = \Workdo\Lead\Entities\LeadStage::find($targetStageId);
$getActiveWorkSpace = getActiveWorkSpace();

echo "Target Stage: " . $targetStage->name . " (Order: " . $targetStage->order . ")\n";

$stages = \Workdo\Lead\Entities\LeadStage::where('pipeline_id', $targetStage->pipeline_id)
    ->where('workspace_id', $getActiveWorkSpace)
    ->orderBy('order')
    ->get();

echo "All Stages in Pipeline:\n";
foreach ($stages as $s) {
    echo "ID: " . $s->id . " | Name: " . $s->name . " | Order: " . $s->order . "\n";
}

$relevantStageIds = \Workdo\Lead\Entities\LeadStage::where('pipeline_id', $targetStage->pipeline_id)
    ->where('workspace_id', $getActiveWorkSpace)
    ->where('order', '<=', $targetStage->order)
    ->pluck('id')
    ->toArray();

echo "Relevant Stage IDs (<= target order): " . json_encode($relevantStageIds) . "\n";

$fields = \Workdo\Lead\Entities\LeadCustomField::where('workspace_id', $getActiveWorkSpace)
    ->where('pipeline_id', $targetStage->pipeline_id)
    ->get();

echo "Checking fields:\n";
foreach ($fields as $field) {
    $intersect = array_intersect($relevantStageIds, $field->required_stages ?? []);
    echo "Field ID: " . $field->id . " | Name: " . $field->name . " | Required Stages: " . json_encode($field->required_stages) . " | Visible Stages: " . json_encode($field->visible_stages) . " | Intersect: " . json_encode($intersect) . "\n";
}
