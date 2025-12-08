<?php
namespace backend\controllers;

use Yii;
use yii\web\Controller;

class LineController extends BaseController
{
public $enableCsrfValidation = false; // ปิด CSRF สำหรับ Webhook
public function actionWebhook()
{
$body = file_get_contents('php://input');
$signature = $_SERVER['HTTP_X_LINE_SIGNATURE'] ?? '';

$channelSecret = "YOUR_CHANNEL_SECRET"; // 👉 เอามาจาก LINE Developer
$hash = base64_encode(hash_hmac('sha256', $body, $channelSecret, true));

// ตรวจสอบว่า payload มาจาก LINE จริง
if ($signature !== $hash) {
Yii::error("Invalid signature from LINE");
return 'Invalid signature';
}

$data = json_decode($body, true);

if (isset($data['events'])) {
foreach ($data['events'] as $event) {
if ($event['type'] === 'message') {
$replyToken = $event['replyToken'];
$text = $event['message']['text'];

$this->handleMessage($replyToken, $text);
}
}
}

return 'OK';
}

private function handleMessage($replyToken, $text)
{
$message = "พิมพ์: 'เช็คออเดอร์ [id]' หรือ 'สต๊อก [ชื่อสินค้า]'";

// ✅ ตรวจสอบข้อความที่ผู้ใช้พิมพ์
if (preg_match('/เช็คออเดอร์ (\d+)/u', $text, $matches)) {
$orderId = $matches[1];

$order = (new \yii\db\Query())
->from('orders')
->where(['id' => $orderId])
->one();

if ($order) {
$message = "📦 ออเดอร์ #{$orderId}\n"
. "ลูกค้า: {$order['customer_name']}\n"
. "ยอดรวม: {$order['total']} บาท\n"
. "สถานะ: {$order['status']}";
} else {
$message = "❌ ไม่พบออเดอร์ #{$orderId}";
}
} elseif (preg_match('/สต๊อก (.+)/u', $text, $matches)) {
$productName = $matches[1];

$product = (new \yii\db\Query())
->from('products')
->where(['like', 'name', $productName])
->one();

if ($product) {
$message = "📦 สินค้า: {$product['name']}\n"
. "คงเหลือ: {$product['stock']} ชิ้น";
} else {
$message = "❌ ไม่พบสินค้า {$productName}";
}
}

$this->replyMessage($replyToken, $message);
}

private function replyMessage($replyToken, $text)
{
$accessToken = "YOUR_CHANNEL_ACCESS_TOKEN"; // 👉 เอามาจาก LINE Developer

$url = "https://api.line.me/v2/bot/message/reply";
$headers = [
"Content-Type: application/json",
"Authorization: Bearer {$accessToken}"
];
$postData = [
"replyToken" => $replyToken,
"messages" => [
["type" => "text", "text" => $text]
]
];

$ch = curl_init($url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData, JSON_UNESCAPED_UNICODE));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
$result = curl_exec($ch);
curl_close($ch);

return $result;
}
}
