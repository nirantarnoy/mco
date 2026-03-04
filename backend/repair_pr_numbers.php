<?php
// Script to fix PR number jump
// This script will find records with PR numbers starting from 400 and renumber them
// sequentially starting from the correct next number.

$dsn = 'mysql:host=localhost;dbname=mco_db';
$username = 'root';
$password = '';

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // 1. Find the highest correct number < 400
    $sql = "SELECT MAX(CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(purch_req_no, '-', 2), '-', -1) AS UNSIGNED)) 
            FROM purch_req 
            WHERE CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(purch_req_no, '-', 2), '-', -1) AS UNSIGNED) < 400";
    $stmt = $pdo->query($sql);
    $last_correct = $stmt->fetchColumn();
    
    $next_num = ($last_correct ?: 0) + 1;
    echo "Last correct number: $last_correct\n";
    echo "Renumbering will start from: $next_num\n\n";

    // 2. Find jumped records
    $sql = "SELECT id, purch_req_no, purch_id FROM purch_req 
            WHERE CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(purch_req_no, '-', 2), '-', -1) AS UNSIGNED) >= 400 
            ORDER BY id ASC";
    $stmt = $pdo->query($sql);
    $jumped = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (empty($jumped)) {
        echo "No jumped records found (>= 400).\n";
        exit;
    }

    $pdo->beginTransaction();
    foreach ($jumped as $row) {
        $old_no = $row['purch_req_no'];
        $parts = explode('-', $old_no);
        if (count($parts) < 2) continue;

        $old_prefix = $parts[0] . '-' . $parts[1];
        $new_prefix = 'PR-' . sprintf('%05d', $next_num);
        $new_no = str_replace($old_prefix, $new_prefix, $old_no);

        echo "Updating PR ID {$row['id']}: $old_no -> $new_no\n";
        
        $updatePr = $pdo->prepare("UPDATE purch_req SET purch_req_no = ? WHERE id = ?");
        $updatePr->execute([$new_no, $row['id']]);

        if ($row['purch_id']) {
            $old_po_prefix = str_replace('PR', 'PO', $old_prefix);
            $new_po_prefix = str_replace('PR', 'PO', $new_prefix);
            
            // Find PO
            $stmtPo = $pdo->prepare("SELECT id, purch_no FROM purch WHERE id = ?");
            $stmtPo->execute([$row['purch_id']]);
            $po = $stmtPo->fetch(PDO::FETCH_ASSOC);
            
            if ($po) {
                $new_po = str_replace($old_po_prefix, $new_po_prefix, $po['purch_no']);
                echo "  Updating PO ID {$po['id']}: {$po['purch_no']} -> $new_po\n";
                $updatePo = $pdo->prepare("UPDATE purch SET purch_no = ? WHERE id = ?");
                $updatePo->execute([$new_po, $po['id']]);
            }
        }
        $next_num++;
    }
    $pdo->commit();
    echo "\nFinished successfully.\n";

} catch (Exception $e) {
    if (isset($pdo)) $pdo->rollBack();
    echo "Error: " . $e->getMessage() . "\n";
}
