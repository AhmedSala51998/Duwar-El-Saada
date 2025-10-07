<?php
require __DIR__.'/config/config.php';
require_auth();

$from_date = $_GET['from_date'] ?? '';
$to_date   = $_GET['to_date'] ?? '';

$dateFilter = '';
$params = [];
if($from_date) { $dateFilter .= " AND DATE(created_at) >= ?"; $params[] = $from_date; }
if($to_date)   { $dateFilter .= " AND DATE(created_at) <= ?"; $params[] = $to_date; }

// ضريبة المشتريات
$stmt = $pdo->prepare("SELECT SUM(vat) as total_vat FROM orders_purchases WHERE 1=1 $dateFilter");
$stmt->execute($params);
$purchase_vat = $stmt->fetchColumn() ?: 0;

// ضريبة المصروفات
$stmt = $pdo->prepare("SELECT SUM(expense_amount*0.15) as total_vat FROM expenses WHERE 1=1 $dateFilter AND has_vat=1");
$stmt->execute($params);
$expenses_vat = $stmt->fetchColumn() ?: 0;

// ضريبة الأصول
$stmt = $pdo->prepare("SELECT SUM(price*quantity*0.15) as total_vat FROM assets WHERE 1=1 $dateFilter AND has_vat=1");
$stmt->execute($params);
$assets_vat = $stmt->fetchColumn() ?: 0;

$total_vat = $purchase_vat + $expenses_vat + $assets_vat;

require_once __DIR__.'/libs/SimpleXLSXGen.php';

$data = [
    ["المصدر", "مجموع الضريبة"],
    ["المشتريات", $purchase_vat],
    ["المصروفات", $expenses_vat],
    ["الأصول", $assets_vat],
    ["المجموع الكلي", $total_vat]
];

$xlsx = Shuchkin\SimpleXLSXGen::fromArray($data);
$xlsx->downloadAs('vat_report.xlsx');
