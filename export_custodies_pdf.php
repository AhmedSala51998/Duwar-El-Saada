<?php
require __DIR__.'/config/config.php';
require_auth();
require_once __DIR__.'/libs/fpdf.php'; // أو Dompdf لو تحب HTML كامل

$kw = trim($_GET['kw'] ?? '');
$params = [];
$q = "SELECT * FROM custodies WHERE 1";
if($kw !== ''){
  $q .= " AND person_name LIKE ?";
  $params[] = "%$kw%";
}
$q .= " ORDER BY id ASC";
$stmt = $pdo->prepare($q);
$stmt->execute($params);
$rows = $stmt->fetchAll();

$transactions_stmt = $pdo->prepare("SELECT * FROM custody_transactions WHERE custody_id=? ORDER BY created_at ASC");

// إنشاء كائن PDF
$pdf = new FPDF('P','mm','A4');
$pdf->AddPage();
$pdf->SetFont('Arial','B',14);
$pdf->Cell(0,10,'تقرير العُهد',0,1,'C');
$pdf->Ln(3);

$pdf->SetFont('Arial','',11);
if($kw!==''){
  $pdf->Cell(0,8,"بحث عن: $kw",0,1,'C');
}

$pdf->Ln(4);
$pdf->SetFont('Arial','B',10);
$pdf->SetFillColor(240,240,240);
$pdf->Cell(10,8,'#',1,0,'C',true);
$pdf->Cell(40,8,'الشخص',1,0,'C',true);
$pdf->Cell(30,8,'الوارد',1,0,'C',true);
$pdf->Cell(30,8,'الصادر',1,0,'C',true);
$pdf->Cell(30,8,'الرصيد',1,0,'C',true);
$pdf->Cell(25,8,'تاريخ',1,0,'C',true);
$pdf->Cell(35,8,'ملاحظات',1,1,'C',true);

$pdf->SetFont('Arial','',9);

$total_in = 0;
$total_out = 0;
$balance = 0;
foreach($rows as $r){
    $in = (float)$r['main_amount'];
    $out = $in - (float)$r['amount'];
    if($out < 0) $out = 0;
    $balance += $in - $out;
    $current_balance = $balance;

    $total_in += $in;
    $total_out += $out;

    $pdf->SetFillColor(208,240,255);
    $pdf->Cell(10,8,$r['id'],1,0,'C',true);
    $pdf->Cell(40,8,iconv('UTF-8','windows-1256',$r['person_name']),1,0,'C',true);
    $pdf->Cell(30,8,number_format($in,2),1,0,'C',true);
    $pdf->Cell(30,8,number_format($out,2),1,0,'C',true);
    $pdf->Cell(30,8,number_format($current_balance,2),1,0,'C',true);
    $pdf->Cell(25,8,$r['taken_at'],1,0,'C',true);
    $pdf->Cell(35,8,iconv('UTF-8','windows-1256', $r['notes'] ?: '-'),1,1,'C',true);

    // الحركات
    $transactions_stmt->execute([$r['id']]);
    $transactions = $transactions_stmt->fetchAll();
    foreach($transactions as $t){
        $trans_amount = (float)$t['amount'];
        $balance -= $trans_amount;
        $current_balance = $balance;

        switch($t['type']){
          case 'asset': $type_ar='أصول'; break;
          case 'expense': $type_ar='مصروفات'; break;
          case 'purchase': $type_ar='مشتريات'; break;
          default: $type_ar=$t['type'];
        }

        $pdf->Cell(10,8,'',1,0,'C');
        $pdf->Cell(40,8,iconv('UTF-8','windows-1256',"-- $type_ar"),1,0,'C');
        $pdf->Cell(30,8,'',1,0,'C');
        $pdf->Cell(30,8,number_format($trans_amount,2),1,0,'C');
        $pdf->Cell(30,8,number_format($current_balance,2),1,0,'C');
        $pdf->Cell(25,8,$t['created_at'],1,0,'C');
        $pdf->Cell(35,8,iconv('UTF-8','windows-1256',$t['notes'] ?: ''),1,1,'C');
    }
}

$pdf->SetFont('Arial','B',10);
$pdf->SetFillColor(230,230,230);
$pdf->Cell(50,8,iconv('UTF-8','windows-1256','الإجماليات'),1,0,'C',true);
$pdf->Cell(30,8,number_format($total_in,2),1,0,'C',true);
$pdf->Cell(30,8,number_format($total_out,2),1,0,'C',true);
$pdf->Cell(30,8,number_format($balance,2),1,0,'C',true);
$pdf->Cell(85,8,'',1,1,'C',true);

// عرض أو تحميل
$pdf->Output('I','تقرير_العهد.pdf');
