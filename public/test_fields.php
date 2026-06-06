<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

// Our custom debug logic
$field = \Workdo\Lead\Entities\LeadCustomField::find(118);
echo "<h3>Custom Field 118 (ftd):</h3>";
if ($field) {
    echo "ID: " . $field->id . "<br>";
    echo "Name: " . $field->name . "<br>";
    echo "Pipeline ID: " . $field->pipeline_id . "<br>";
    echo "Workspace ID: " . $field->workspace_id . "<br>";
    echo "Visible Stages: " . json_encode($field->visible_stages) . "<br>";
    echo "Required Stages: " . json_encode($field->required_stages) . "<br>";
} else {
    echo "Field 118 not found!<br>";
}

echo "<h3>All lead custom fields:</h3>";
$fields = \Workdo\Lead\Entities\LeadCustomField::all();
foreach ($fields as $f) {
    echo "Field ID: {$f->id}, Name: {$f->name}, Pipeline: {$f->pipeline_id}, Visible Stages: " . json_encode($f->visible_stages) . "<br>";
}

echo "<h3>Lead Sections:</h3>";
$sections = \Workdo\Lead\Entities\LeadSection::all();
foreach ($sections as $s) {
    echo "Section ID: {$s->id}, Name: {$s->name}, Pipeline: {$s->pipeline_id}<br>";
}

echo "<h3>Lead Info (AJEET):</h3>";
$lead = \Workdo\Lead\Entities\Lead::where('name', 'like', '%AJEET%')->first();
if ($lead) {
    echo "Lead ID: " . $lead->id . "<br>";
    echo "Name: " . $lead->name . "<br>";
    echo "Pipeline ID: " . $lead->pipeline_id . "<br>";
    echo "Stage ID: " . $lead->stage_id . "<br>";
} else {
    echo "Lead Ajeet not found!<br>";
}
