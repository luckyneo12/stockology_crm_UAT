<?php
try {
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=crm", "root", "");
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $out = "Matching Custom Fields:\n";
    $stmt = $pdo->query("SELECT id, name, pipeline_id, section_id, visible_stages, required_stages FROM lead_custom_fields WHERE name LIKE '%BANK%' OR name LIKE '%TRADE%' OR name LIKE '%IFSC%' OR name LIKE '%DEALER%' OR name LIKE '%RM%'");
    $fields = $stmt->fetchAll(PDO::FETCH_ASSOC);
    foreach ($fields as $f) {
        $out .= "ID: " . $f['id'] . " | Name: " . $f['name'] . " | Pipeline ID: " . $f['pipeline_id'] . " | Section ID: " . ($f['section_id'] ?? 'NULL') . " | Visible: " . $f['visible_stages'] . "\n";
    }
    
    file_put_contents(__DIR__ . '/output.txt', $out);
    echo "Saved to output.txt";
} catch (Throwable $e) {
    file_put_contents(__DIR__ . '/output.txt', "Error: " . $e->getMessage());
    echo "Error: " . $e->getMessage();
}
