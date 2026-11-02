<?php
// Extract all attributeLabels from backend/models and common/models
$modelsDir = [
    __DIR__ . '/../common/models',
    __DIR__ . '/../backend/models'
];

$allLabels = []; // [table_name => [col_name => label]]

foreach ($modelsDir as $dir) {
    if (!is_dir($dir)) continue;
    $files = glob($dir . '/*.php');
    foreach ($files as $file) {
        $content = file_get_contents($file);
        
        // Find tableName
        $tableName = null;
        if (preg_match('/function\s+tableName\s*\(\)\s*:\s*string|\bfunction\s+tableName\s*\(\)/', $content)) {
            if (preg_match('/tableName\s*\(\)[^{]*\{\s*return\s*[\'"](?:\%\{\%|\{\%|)?([a-zA-Z0-9_]+)(?:\%\}|\})?[\'"]\s*;/s', $content, $m)) {
                $tableName = $m[1];
            }
        }
        
        // Find attributeLabels()
        if ($tableName && preg_match('/function\s+attributeLabels\s*\(\)[^{]*\{(.*?)\}/s', $content, $m)) {
            $body = $m[1];
            if (preg_match_all('/[\'"]([a-zA-Z0-9_]+)[\'"]\s*=>\s*(?:Yii::t\([^)]+\)|[\'"]([^\'"]+)[\'"])/u', $body, $matches, PREG_SET_ORDER)) {
                foreach ($matches as $match) {
                    $col = $match[1];
                    $lbl = isset($match[2]) ? $match[2] : '';
                    if (!isset($allLabels[$tableName][$col]) || !empty($lbl)) {
                        $allLabels[$tableName][$col] = $lbl;
                    }
                }
            }
        }
    }
}

echo "Found attribute labels for " . count($allLabels) . " tables.\n";
file_put_contents(__DIR__ . '/model_labels.json', json_encode($allLabels, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
echo "Saved to model_labels.json\n";
