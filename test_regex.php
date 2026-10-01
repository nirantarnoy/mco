<?php
$displayDesc = 'เลขที่: NPR202608120001 (อ้างอิง QT: 128)';
$refInfo = ['qt_no' => 'RY-QT26-000084'];
if (preg_match('/(อ้างอิง\s*QT:\s*)([A-Za-z0-9-.\/_]+)/u', $displayDesc, $qtMatch)) {
    if ($qtMatch[2] !== $refInfo['qt_no']) {
        $displayDesc = preg_replace('/(อ้างอิง\s*QT:\s*)([A-Za-z0-9-.\/_]+)/u', '${1}' . $refInfo['qt_no'], $displayDesc);
    }
}
echo $displayDesc . "\n";
