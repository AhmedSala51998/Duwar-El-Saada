<?php
require __DIR__.'/../config/config.php'; 
require_auth();

// تحديد اسم الصفحة الحالية
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!doctype html>
<html lang="ar" dir="rtl">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title><?= esc(APP_NAME) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@400;600;700&display=swap" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css" rel="stylesheet">
  <link href="<?= BASE_URL ?>/assets/css/theme.css" rel="stylesheet">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
  <style>
    .loader{
      position:fixed;
      inset:0;
      display:flex;
      justify-content:center;
      align-items:center;
      flex-direction:column;
      z-index:9999;
      background:#fff;
      transition:opacity .8s ease, visibility .8s ease;
    }
    .loader.hidden{opacity:0;visibility:hidden;}

    /* الدائرة */
    .circle{
      position:relative;
      width:160px;
      height:160px;
      border-radius:50%;
      border:4px solid rgba(255,127,50,0.2);
      display:flex;
      justify-content:center;
      align-items:center;
      animation:spin 3s linear infinite;
    }

    /* النص */
    .loader-text{
      color:#ff7f32;
      font-size:24px;
      font-weight:bold;
      text-align:center;
      text-shadow:0 0 10px rgba(255,127,50,0.8),
                  0 0 20px rgba(255,127,50,0.6);
      animation:pulse_loader 2s ease-in-out infinite;
      z-index:2;
    }

    /* البولز (نبضات قلب) */
    .pulse-dot{
      position:absolute;
      width:10px; height:10px;
      border-radius:50%;
      background:#ff7f32;
      opacity:0.8;
      transform:scale(0);
      animation:dotPulse 1.5s infinite ease-in-out;
    }

    /* توزيع البولز حول حافة الدائرة */
    .pulse-dot:nth-child(1){top:0; left:50%; animation-delay:0s;}
    .pulse-dot:nth-child(2){top:15%; right:0; animation-delay:0.1s;}
    .pulse-dot:nth-child(3){bottom:15%; right:0; animation-delay:0.2s;}
    .pulse-dot:nth-child(4){bottom:0; left:50%; animation-delay:0.3s;}
    .pulse-dot:nth-child(5){bottom:15%; left:0; animation-delay:0.4s;}
    .pulse-dot:nth-child(6){top:15%; left:0; animation-delay:0.5s;}

    /* الحركات */
    @keyframes spin{0%{transform:rotate(0deg);}100%{transform:rotate(360deg);}}
    @keyframes pulse_loader{
      0%,100%{transform:scale(1); filter:blur(0);}
      50%{transform:scale(1.1); filter:blur(1.5px);}
    }
    @keyframes dotPulse{
      0%{transform:scale(0); opacity:0;}
      50%{transform:scale(1); opacity:1;}
      100%{transform:scale(0); opacity:0;}
    }

    body.loading > *:not(.loader){
      opacity:0;
      pointer-events:none;
    }

    /* تمييز الصفحة النشطة */
    .sidebar-link.active,
    .nav-link.active {
      background-color: #ff6600; /* لون الهوفر بتاعك */
      color: #fff !important;
      border-radius: 6px;
    }
    .custom-navbar {
      background: rgba(255,255,255,0.85);
      backdrop-filter: blur(10px);
      border-bottom: 1px solid rgba(0,0,0,0.08); /* ✅ أسود خفيف جدًا */
      padding: .7rem 1rem;
    }
    /* لون البرتقالي */
    .text-orange { color: #ff6a00 !important; }

    /* الدور */
    .role-badge {
      background: #fff3e6;
      color: #ff6a00;
      font-weight: 600;
      border-radius: 50px;
      padding: .5rem 1rem;
      box-shadow: 0 2px 6px rgba(255,106,0,.2);
    }

    /* روابط */
    .navbar .nav-link {
      font-weight: 500;
      padding: .6rem 1.2rem;
      border-radius: 12px;
      color: #555 !important;
      transition: all .2s ease;
    }
    .navbar .nav-link:hover {
      background: rgba(255,106,0,.08);
      color: #ff6a00 !important;
    }
    .navbar .nav-link.active {
      background: rgba(255,106,0,.15);
      color: #ff6a00 !important;
      font-weight: 600;
    }

    /* زر خروج */
    .btn-logout {
      background: linear-gradient(135deg,#ff6a00,#ff944d);
      color: #fff;
      font-weight: 600;
      padding: .6rem 1.4rem;
      border-radius: 50px;
      box-shadow: 0 4px 12px rgba(255,106,0,.3);
      transition: all .3s ease;
    }
    .btn-logout:hover {
      background: linear-gradient(135deg,#e65a00,#ff7a1f);
      transform: translateY(-2px);
      box-shadow: 0 6px 15px rgba(255,106,0,.4);
      color: #fff !important;
    }

    .custom-navbar {
      padding-top: 0.05rem !important;   /* أقل ارتفاع ممكن بدون تشويه */
      padding-bottom: 0.05rem !important;
    }

    .custom-navbar .navbar-brand img {
      height:50px !important;  /* تصغير اللوجو ليكون أنسب مع الارتفاع الجديد */
      width:50px !important;
    }

    .custom-navbar .navbar-brand span {
      font-size: 1.15rem !important; /* أصغر شوية للتوازن */
      line-height: 1;
    }

    @media (max-width: 768px) {
      .custom-navbar {
        padding-top: 0.1rem !important;
        padding-bottom: 0.1rem !important;
      }
    }

    .stat-icon {
      width: 50px;
      height: 50px;
      background: rgba(255, 106, 0, 0.1);
      color: #ff6a00;
      display: flex;
      align-items: center;
      justify-content: center;
      border-radius: 50%;
      font-size: 1.6rem;
      margin-right: 10px;
      position: relative;
      transition: transform 0.6s ease; /* لتدوير الأيقونة عند hover */
    }

    /* حركة التدوير عند hover */
    .stat-icon:hover {
      transform: rotate(360deg);
    }

    /* النبض المستمر */
    .stat-icon::after {
      content: '';
      position: absolute;
      width: 100%;
      height: 100%;
      border-radius: 50%;
      background: rgba(255, 106, 0, 0.2);
      animation: pulse 1.5s infinite;
      top: 0;
      left: 0;
      z-index: -1;
    }

    /* تعريف النبض */
    @keyframes pulse {
      0% {
        transform: scale(1);
        opacity: 0.6;
      }
      50% {
        transform: scale(1.4);
        opacity: 0;
      }
      100% {
        transform: scale(1);
        opacity: 0.6;
      }
    }

    /* ترتيب العنوان مع الدائرة */
    .page-title {
      font-weight: 700;
      color: #2c3e50;
      display: flex;
      align-items: center;
      gap: 10px;
    }



    /* تصميم الدور */
    .role-badge {
      background-color: #fff3e0; /* خلفية فاتحة */
      color: #ff8800; /* نص برتقالي */
      border: 1px solid #ff8800;
      border-radius: 50px;
      font-size: 0.9rem;
      transition: all 0.3s ease;
    }

    .role-badge:hover {
      background-color: #ff8800;
      color: #fff;
      box-shadow: 0 0 10px rgba(255,136,0,0.6);
    }

    /* النقطة البوليتية */
    .role-bullet {
      display: inline-block;
      width: 10px;
      height: 10px;
      background-color: #ff8800;
      border-radius: 50%;
      margin-right: 8px;
      animation: pulse_bullet 1.5s infinite;
    }

    @keyframes pulse_bullet {
      0% { transform: scale(1); opacity: 1; }
      50% { transform: scale(1.4); opacity: 0.7; }
      100% { transform: scale(1); opacity: 1; }
    }

    /* إضافة bullet قبل النص */
    .role-badge .bullet {
      width: 10px;
      height: 10px;
      background-color: #ff8800;
      border-radius: 50%;
      margin-right: 8px;
      display: inline-block;
      animation: pulse 1.5s infinite;
    }

    /*******************/

    /* 🌐 تحسين الجدول ليصبح ريسبونسف بشكل جذاب */

    /* الوضع العادي - الأجهزة الكبيرة */
    @media (min-width: 768px) {
      .custom-table td,
      .custom-table th {
        white-space: nowrap;
      }
    }

    /* 📱 الموبايل */
    @media (max-width: 767px) {
      /* إخفاء رؤوس الأعمدة في الموبايل */
      .custom-table thead {
        display: none;
      }

      /* تحويل الصفوف إلى كروت */
      .custom-table tbody tr {
        display: block;
        background: #fff;
        border: 1px solid #eee;
        border-radius: 12px;
        margin-bottom: 15px;
        padding: 10px 12px;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        text-align: right;
      }

      .custom-table tbody tr td {
        display: flex;
        justify-content: space-between;
        align-items: center;
        border: none !important;
        padding: 6px 5px;
        font-size: 0.9rem;
      }

      /* عرض اسم العمود قبل القيمة */
      .custom-table tbody tr td::before {
        content: attr(data-label);
        font-weight: 600;
        color: #555;
      }

      /* أيقونات وأزرار العمليات */
      .custom-table td .btn {
        width: 100%;
        margin-top: 5px;
      }

      /* إزالة التوسيط الإجباري */
      .custom-table .text-center {
        text-align: right !important;
      }

      /* تحسين الشارات */
      .badge {
        font-size: 0.8rem;
        padding: 4px 6px;
      }
    }

    @media (max-width: 768px) {
      .responsive-table table,
      .responsive-table thead,
      .responsive-table tbody,
      .responsive-table th,
      .responsive-table td,
      .responsive-table tr {
        display: block;
        width: 100%;
      }

      .responsive-table thead {
        display: none;
      }

      .responsive-table tr {
        background: #fff;
        margin-bottom: 1rem;
        border: 1px solid #eee;
        border-radius: 0.75rem;
        box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
        padding: 0.75rem 1rem;
      }

      .responsive-table td {
        display: grid;
        grid-template-columns: 40% 60%;
        gap: 6px;
        text-align: left;
        padding: 6px 0;
        border: none;
        border-bottom: 1px dashed #ddd;
        word-break: break-word; /* يمنع التداخل */
      }

      .responsive-table td:last-child {
        border-bottom: none;
      }

      .responsive-table td::before {
        content: attr(data-label);
        font-weight: 600;
        color: #555;
        text-align: right;
        padding-left: 5px;
      }

      .responsive-table td span,
      .responsive-table td div,
      .responsive-table td small,
      .responsive-table td button {
        text-align: left;
        white-space: normal;
      }

      /* علشان العمليات تبقى في سطر منفصل */
      .responsive-table td[data-label="عمليات"] {
        grid-template-columns: 1fr;
        text-align: center;
      }

      /* السعر الطويل ما يخترق الكارت */
      .responsive-table td[data-label="السعر"] {
        word-break: break-all;
      }
    }
    @media (max-width: 768px) {
      /* منع تطبيق قياسات الأعمدة في الوضع البلوكي */
      .custom-table th,
      .custom-table td {
        width: auto !important;
        white-space: normal !important;
        text-align: start !important;
      }

      /* تعديل بسيط لمحاذاة البيانات */
      .responsive-table td {
        text-align: right; /* لأن اللغة عربية */
        direction: rtl;
      }

      .responsive-table td::before {
        text-align: left;
        direction: rtl;
      }
    }

    .sidebar-link {
      color: #444;
      padding: 8px 10px;
      border-radius: 8px;
      transition: 0.2s ease;
    }
    .sidebar-link:hover,
    .sidebar-link.active {
      background-color: #ffe5cc;
      color: #d35400 !important;
      text-decoration: none;
    }

    .btn-logout {
      background-color: #ff7b00;
      color: #fff;
      border-radius: 8px;
      transition: 0.2s;
    }
    .btn-logout:hover {
      background-color: #e76f00;
      color: #fff;
    }
    .btn-orange {
      background-color: #ff7b00;
      transition: all 0.3s ease;
    }
    .btn-orange:hover {
      background-color: #e46e00;
      transform: scale(1.08);
    }

    .logo-modern {
      height: 45px !important;      /* خليه صغير شوية قبل التكبير */
      width: auto;
      box-shadow: none !important;
      transition: transform 0.3s ease, filter 0.3s ease;
      object-fit: contain;
      transform: scale(3.4);
      transform-origin: right center;  /* يخليه يكبر باتجاه اليمين فقط */
      position: relative;
      z-index: 1;
      pointer-events: none;           /* يمنع تغطيته للزر اللي تحته */
    }

    @media screen and (max-width: 768px) {

      /* يحتوي الجدول داخل المودال على تمرير أفقي سلس */
      #addM .modal-body {
        padding: 10px;
      }

      #addM table {
        min-width: 800px; /* يخلي الجدول أوسع من الشاشة لتفعيل الاسكرول */
        font-size: 12px; /* تصغير النص شوية للموبايل */
      }

      #addM .table-responsive {
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch; /* تمرير سلس على الموبايل */
      }

      #addM th, #addM td {
        white-space: nowrap; /* يمنع النص من النزول لسطرين */
        padding: 6px 8px;
        vertical-align: middle;
      }

      /* تحسين شكل الأزرار */
      #addM .btn {
        font-size: 12px;
        padding: 5px 10px;
      }
    }
    /* 📱 تحسين عرض جدول صلاحيات المجموعات في الموبايل */
    @media screen and (max-width: 768px) {
      #addPermGroup .modal-dialog {
        margin: 10px;
      }

      #addPermGroup .modal-body {
        padding: 10px;
      }

      /* جعل الجدول قابل للتمرير */
      #addPermGroup .table-responsive {
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch; /* تمرير سلس */
      }

      #addPermGroup table {
        min-width: 700px; /* يجعل الجدول أعرض لتفعيل الاسكرول */
        font-size: 12px; /* تصغير النص قليلاً للموبايل */
      }

      #addPermGroup th,
      #addPermGroup td {
        white-space: nowrap;
        padding: 6px 8px;
        vertical-align: middle;
      }

      #addPermGroup .btn {
        font-size: 12px;
        padding: 5px 10px;
      }

      /* تحسين المسافات حول زر إضافة صف */
      #addPermGroup .text-end.mt-3 {
        text-align: center !important;
        margin-top: 15px !important;
      }
    }
    @media screen and (max-width: 768px) {
      #addAsset .modal-dialog {
        margin: 10px;
      }

      #addAsset .modal-body {
        padding: 10px;
      }

      /* جعل الجدول قابل للتمرير */
      #addAsset .table-responsive {
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch; /* تمرير سلس */
      }

      #addAsset table {
        min-width: 950px; /* يجعل الجدول أعرض لتفعيل الاسكرول */
        font-size: 12px; /* تصغير النص قليلاً للموبايل */
      }

      #addAsset th,
      #addAsset td {
        white-space: nowrap;
        padding: 6px 8px;
        vertical-align: middle;
      }

      #addAsset .btn {
        font-size: 12px;
        padding: 5px 10px;
      }

      /* تحسين المسافات حول زر إضافة صف */
      #addAsset .text-end.mt-3 {
        text-align: center !important;
        margin-top: 15px !important;
      }
    }
    @media screen and (max-width: 768px) {
      #addMultipleExpenses .modal-dialog {
        margin: 10px;
      }

      #addMultipleExpenses .modal-body {
        padding: 10px;
      }

      /* جعل الجدول قابل للتمرير */
      #addMultipleExpenses .table-responsive {
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch; /* تمرير سلس */
      }

      #addMultipleExpenses table {
        min-width: 1100px; /* يجعل الجدول أعرض لتفعيل الاسكرول */
        font-size: 12px; /* تصغير النص قليلاً للموبايل */
      }

      #addMultipleExpenses th,
      #addMultipleExpenses td {
        white-space: nowrap;
        padding: 6px 8px;
        vertical-align: middle;
      }

      #addMultipleExpenses .btn {
        font-size: 12px;
        padding: 5px 10px;
      }

      /* تحسين المسافات حول زر إضافة صف */
      #addMultipleExpenses .text-end.mt-3 {
        text-align: center !important;
        margin-top: 15px !important;
      }
    }
    @media screen and (max-width: 768px) {
      #addMultipleOrders .modal-dialog {
        margin: 10px;
      }

      #addMultipleOrders .modal-body {
        padding: 10px;
      }

      /* جعل الجدول قابل للتمرير */
      #addMultipleOrders .table-responsive {
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch; /* تمرير سلس */
      }

      #addMultipleOrders table {
        min-width: 1100px; /* يجعل الجدول أعرض لتفعيل الاسكرول */
        font-size: 12px; /* تصغير النص قليلاً للموبايل */
      }

      #addMultipleOrders th,
      #addMultipleOrders td {
        white-space: nowrap;
        padding: 6px 8px;
        vertical-align: middle;
      }

      #addMultipleOrders .btn {
        font-size: 12px;
        padding: 5px 10px;
      }

      /* تحسين المسافات حول زر إضافة صف */
      #addMultipleOrders .text-end.mt-3 {
        text-align: center !important;
        margin-top: 15px !important;
      }
    }
    @media screen and (max-width: 768px) {
      #addMultipleCustodies .modal-dialog {
        margin: 10px;
      }

      #addMultipleCustodies .modal-body {
        padding: 10px;
      }

      /* جعل الجدول قابل للتمرير */
      #addMultipleCustodies .table-responsive {
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch; /* تمرير سلس */
      }

      #addMultipleCustodies table {
        min-width: 1100px; /* يجعل الجدول أعرض لتفعيل الاسكرول */
        font-size: 12px; /* تصغير النص قليلاً للموبايل */
      }

      #addMultipleCustodies th,
      #addMultipleCustodies td {
        white-space: nowrap;
        padding: 6px 8px;
        vertical-align: middle;
      }

      #addMultipleCustodies .btn {
        font-size: 12px;
        padding: 5px 10px;
      }

      /* تحسين المسافات حول زر إضافة صف */
      #addMultipleCustodies .text-end.mt-3 {
        text-align: center !important;
        margin-top: 15px !important;
      }
    }
    @media screen and (max-width: 768px) {
      #addMultipleRoles .modal-dialog {
        margin: 10px;
      }

      #addMultipleRoles .modal-body {
        padding: 10px;
      }

      /* جعل الجدول قابل للتمرير */
      #addMultipleRoles .table-responsive {
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch; /* تمرير سلس */
      }

      #addMultipleRoles table {
        min-width: 1100px; /* يجعل الجدول أعرض لتفعيل الاسكرول */
        font-size: 12px; /* تصغير النص قليلاً للموبايل */
      }

      #addMultipleRoles th,
      #addMultipleRoles td {
        white-space: nowrap;
        padding: 6px 8px;
        vertical-align: middle;
      }

      #addMultipleRoles .btn {
        font-size: 12px;
        padding: 5px 10px;
      }

      /* تحسين المسافات حول زر إضافة صف */
      #addMultipleRoles .text-end.mt-3 {
        text-align: center !important;
        margin-top: 15px !important;
      }
    }




      /* Theme variables (light default) */
  :root {
    --bg: #f6f7fb;
    --surface: #ffffff;
    --muted: #6c757d;
    --text: #2c3e50;
    --accent: #ff6a00;        /* اللون البرتقالي الأساسي */
    --accent-600: #e85d00;
    --card-shadow: 0 6px 18px rgba(44,62,80,0.06);
    --border: rgba(0,0,0,0.06);
    --glass: rgba(255,255,255,0.6);
    --inverse-text: #ffffff;
    --toast-bg: #fff8f0;
    --loader-bg: #fff;
  }

  /* Dark theme overrides */
  [data-theme="dark"] {
    --bg: #0f1720;
    --surface: #0b1220;
    --muted: #9aa6b2;
    --text: #e6eef6;
    --accent: #ff8f3b;
    --accent-600: #ff7a00;
    --card-shadow: 0 8px 24px rgba(0,0,0,0.6);
    --border: rgba(255,255,255,0.06);
    --glass: rgba(10,14,20,0.5);
    --inverse-text: #0b1220;
    --toast-bg: #1a2a37;
    --loader-bg: #071018;
  }

  /* apply */
  html, body {
    background: var(--bg);
    color: var(--text);
    transition: background-color .25s ease, color .25s ease;
  }

  .container-fluid, .page-wrapper, main {
    background: transparent;
  }

  .table-responsive, .custom-table, .table {
    background: transparent;
  }

  .card, .table-responsive, .modal-content {
    background: var(--surface) !important;
    box-shadow: var(--card-shadow) !important;
    border: 1px solid var(--border) !important;
    transition: background .25s ease, box-shadow .25s ease, border-color .25s ease;
  }

  /* Navbar */
  .custom-navbar {
    background: linear-gradient(to right, rgba(255,255,255,0.6), rgba(255,255,255,0.35));
    backdrop-filter: blur(6px);
  }
  [data-theme="dark"] .custom-navbar {
    background: linear-gradient(180deg, rgba(11,18,32,0.6), rgba(11,18,32,0.45));
  }

  .navbar .nav-link, .sidebar-link {
    color: var(--text) !important;
  }
  [data-theme="dark"] .navbar .nav-link, [data-theme="dark"] .sidebar-link {
    color: var(--muted) !important;
  }

  /* Buttons */
  .btn-orange {
    background: linear-gradient(135deg, var(--accent), var(--accent-600));
    color: var(--inverse-text);
    border: none;
    box-shadow: 0 6px 16px rgba(255,106,0,0.14);
  }
  .btn-orange:hover { transform: translateY(-2px); }

  /* Role badge */
  .role-badge {
    background: linear-gradient(90deg, rgba(255,240,230,0.9), rgba(255,244,236,0.85));
    color: var(--accent);
    border: 1px solid rgba(255,106,0,0.12);
  }
  [data-theme="dark"] .role-badge {
    background: rgba(255,108,40,0.08);
    color: var(--accent);
    border: 1px solid rgba(255,106,0,0.15);
  }

  /* Table text and muted */
  .custom-table thead th {
    background: transparent;
    color: var(--muted);
    border-bottom: 1px solid var(--border);
  }
  .custom-table td {
    color: var(--text);
  }
  [data-theme="dark"] .custom-table td { color: var(--text); }

  /* Loader */
  .loader { background: var(--loader-bg); }
  .loader-text { color: var(--accent); text-shadow: none; }

  /* Toast */
  .toast.align-items-center.text-bg-warning {
    background: var(--toast-bg);
    color: var(--text);
    border: 1px solid rgba(255,106,0,0.06);
  }
  [data-theme="dark"] .toast.align-items-center.text-bg-warning {
    background: rgba(255,120,60,0.08);
    color: var(--text);
    border: 1px solid rgba(255,120,60,0.12);
  }

  /* Pagination */
  .pagination .page-link {
    color: var(--accent) !important;
    border-color: transparent !important;
    background: transparent !important;
    padding: .35rem .6rem;
    border-radius: .5rem;
  }
  .pagination .page-item.active .page-link {
    background: var(--accent) !important;
    color: var(--inverse-text) !important;
    box-shadow: 0 6px 14px rgba(0,0,0,0.08);
  }

  /* Make sure modals and offcanvas match */
  .offcanvas, .modal-content { background: var(--surface) !important; color: var(--text) !important; }

  /* small helpers */
  .theme-toggle-btn {
    border-radius: 10px;
    padding: .45rem .6rem;
    min-width: 44px;
    display:inline-flex;
    justify-content:center;
    align-items:center;
    background: transparent;
    border: 1px solid rgba(0,0,0,0.04);
  }
  [data-theme="dark"] .theme-toggle-btn { border-color: rgba(255,255,255,0.06); }

  /* ensure links visible on dark */
  a { color: var(--accent); }
  [data-theme="dark"] a { color: var(--accent); }

  /* transitions */
  * { transition: background-color .18s ease, color .18s ease, border-color .18s ease; }

  /* ============================================
   TABLE — NEW DARK MODE STYLING
   ============================================ */

/* Header */
[data-theme="dark"] .custom-table thead th {
  background: #18222f !important;
  color: #cbd5e1 !important;
  border-bottom: 1px solid rgba(255,255,255,0.07) !important;
}

/* Body rows */
[data-theme="dark"] .custom-table tbody tr {
  background: #0f1720 !important;
  border-bottom: 1px solid rgba(255,255,255,0.04) !important;
}

/* Hover */
[data-theme="dark"] .custom-table tbody tr:hover {
  background: #1a2533 !important;
}

/* Cells */
[data-theme="dark"] .custom-table tbody td {
  color: #e2e8f0 !important;
}

/* Muted text */
[data-theme="dark"] .text-muted,
[data-theme="dark"] .table .text-muted {
  color: #94a3b8 !important;
}

/* Borders fix */
[data-theme="dark"] .table-responsive,
[data-theme="dark"] .custom-table {
  border-color: rgba(255,255,255,0.05) !important;
}

/* Pagination */
[data-theme="dark"] .pagination .page-link {
  color: #ff8f3b !important;
  background: transparent !important;
}

[data-theme="dark"] .pagination .page-item.active .page-link {
  background: #ff8f3b !important;
  color: #0e141b !important;
}

/* Action buttons inside the table */
[data-theme="dark"] .btn-outline-secondary,
[data-theme="dark"] .btn-outline-danger,
[data-theme="dark"] .btn-outline-warning {
  color: #cbd5e1 !important;
  border-color: rgba(255,255,255,0.15) !important;
}

[data-theme="dark"] .btn-outline-secondary:hover,
[data-theme="dark"] .btn-outline-danger:hover,
[data-theme="dark"] .btn-outline-warning:hover {
  background: rgba(255,255,255,0.07) !important;
  color: #fff !important;
}
/* ============================================================
   🔥 DARK MODE FULL FIX — Readable Tables, Cards, Charts, Modal
   ============================================================ */

/* ========== Global text ========== */
[data-theme="dark"] body,
[data-theme="dark"] .text,
[data-theme="dark"] .card,
[data-theme="dark"] .modal-content {
  color: #f1f5f9 !important; /* نص واضح */
}

/* ========== Cards ========== */
[data-theme="dark"] .card {
  background: #111827 !important; 
  border-color: rgba(255,255,255,0.08) !important;
}

[data-theme="dark"] .card-header {
  background: #1f2937 !important;
  color: #f8fafc !important;
  border-bottom-color: rgba(255,255,255,0.08) !important;
}

/* ========== Charts containers ========== */
[data-theme="dark"] .chart-container,
[data-theme="dark"] canvas {
  background: #111827 !important;
  color: #f1f5f9 !important;
}

/* ========== Tables ========== */

/* table header */
[data-theme="dark"] table thead th {
  background: #1e293b !important;
  color: #e2e8f0 !important;
  border-color: rgba(255,255,255,0.08) !important;
}

/* table body */
[data-theme="dark"] table tbody tr {
  background: #0f172a !important;
}

[data-theme="dark"] table tbody tr:hover {
  background: #1e293b !important;
}

/* table cells */
[data-theme="dark"] table tbody td {
  color: #f1f5f9 !important;
  border-color: rgba(255,255,255,0.05) !important;
}

/* muted text */
[data-theme="dark"] .text-muted {
  color: #94a3b8 !important;
}

/* ========== Pagination ========== */
[data-theme="dark"] .pagination .page-link {
  background: transparent !important;
  color: #ff8c3b !important;
}

[data-theme="dark"] .pagination .page-item.active .page-link {
  background: #ff8c3b !important;
  color: #111 !important;
}

/* ========== Modal Close Button (X) ========== */
[data-theme="dark"] .btn-close {
  filter: invert(1) brightness(200%) !important; /* يخلي الـ X بيضاء وواضحة */
  opacity: 1 !important;
}

/* ========== Forms input text ========== */
[data-theme="dark"] input,
[data-theme="dark"] select,
[data-theme="dark"] textarea {
  background: #0f172a !important;
  color: #f1f5f9 !important;
  border-color: rgba(255,255,255,0.1) !important;
}

[data-theme="dark"] input::placeholder,
[data-theme="dark"] textarea::placeholder {
  color: #64748b !important;
}

/* ========== Buttons ========== */
[data-theme="dark"] .btn,
[data-theme="dark"] button {
  color: #f1f5f9 !important;
}

[data-theme="dark"] .btn-outline-secondary,
[data-theme="dark"] .btn-outline-danger,
[data-theme="dark"] .btn-outline-warning {
  border-color: rgba(255,255,255,0.15) !important;
  color: #e2e8f0 !important;
}

[data-theme="dark"] .btn-outline-secondary:hover,
[data-theme="dark"] .btn-outline-danger:hover,
[data-theme="dark"] .btn-outline-warning:hover {
  background: rgba(255,255,255,0.1) !important;
}
/* --- الجدول بالكامل في الدارك مود --- */
[data-theme="dark"] .custom-table,
[data-theme="dark"] .table {
    background-color: #1e1e1e !important;   /* رمادي غامق خفيف */
    color: #ffffff !important;              /* نص أبيض */
}

/* خلفية الـ tbody */
[data-theme="dark"] .custom-table tbody tr {
    background-color: #1e1e1e !important;
    border-color: #2a2a2a !important;
}

/* خلفية الهدر */
[data-theme="dark"] .custom-table thead th {
    background-color: #2a2a2a !important;   /* أغمق شويه */
    color: #ffffff !important;              
    border-bottom: 1px solid #3a3a3a !important;
}

/* نص الجدول */
[data-theme="dark"] .custom-table td,
[data-theme="dark"] .custom-table th {
    color: #ffffff !important;
    opacity: 1 !important;  /* يمنع البهتان */
}

/* Hover */
[data-theme="dark"] .custom-table tbody tr:hover {
    background-color: #333333 !important;
}

/* الحدود */
[data-theme="dark"] .custom-table td,
[data-theme="dark"] .custom-table th {
    border-color: #3a3a3a !important;
}

/* شغل داخل table-responsive */
[data-theme="dark"] .table-responsive {
    background-color: #1e1e1e !important;
    border: 1px solid #2a2a2a !important;
}
/* --- Dark Mode: Fix Table Full Styling --- */
[data-theme="dark"] table,
[data-theme="dark"] .table {
    background-color: #1c1c1c !important; /* خلفية الجدول */
    color: #fff !important;
}

/* جسم الجدول */
[data-theme="dark"] .table tbody tr {
    background-color: #1c1c1c !important;
    color: #fff !important;
}

/* خلايا الجدول */
[data-theme="dark"] .table td,
[data-theme="dark"] .table th {
    color: #ffffff !important;
    opacity: 1 !important;        /* يمنع البهتان */
    background-color: inherit !important;
    border-color: #333 !important;
}

/* الهيدر */
[data-theme="dark"] .table thead th {
    background-color: #262626 !important;
    color: #fff !important;
    border-color: #444 !important;
}

/* Hover */
[data-theme="dark"] .table tbody tr:hover {
    background-color: #333333 !important;
}

/* لو الجدول جوه table-responsive */
[data-theme="dark"] .table-responsive {
    background-color: #1c1c1c !important;
}
/* ---------------------------
       Dropdown / Offcanvas Header
----------------------------*/
.dark-mode .dropdown-menu,
.dark-mode .popover,
.dark-mode .offcanvas,
.dark-mode .dropdown-item,
.dark-mode .modal-content {
    background-color: #0f1621 !important; /* داكن نفس باقي النظام */
    color: #fff !important;
}

/* هيدر الـ dropdown أو offcanvas */
.dark-mode .dropdown-header,
.dark-mode .offcanvas-header,
.dark-mode .modal-header {
    background-color: #0f1621 !important;
    color: #fff !important;
    border-bottom: 1px solid #222 !important;
}

/* زر الإغلاق داخل الهيدر */
.dark-mode .btn-close {
    filter: invert(1); /* يخلي الأيقونة بيضاء */
}


/* ---------------------------
         FILTER CARD
----------------------------*/
.dark-mode .filter-card,
.dark-mode .card,
.dark-mode .report-container {
    background-color: #0f1621 !important;
    color: white !important;
    border: 1px solid #1f2630 !important;
}

/* مدخلات التاريخ */
.dark-mode input[type="date"],
.dark-mode .form-control {
    background-color: #0b1120 !important;
    color: white !important;
    border: 1px solid #222 !important;
}

/* placeholder يكون ظاهر */
.dark-mode input::placeholder {
    color: #e2e2e2 !important;
    opacity: 0.7;
}
/* -----------------------------------
      FILE INPUT (اختيار ملف)
----------------------------------- */
.dark-mode input[type="file"] {
    background-color: #0b1120 !important;
    color: #fff !important;
    border: 1px solid #222 !important;
}

/* النص داخل زر اختيار الملف */
.dark-mode input[type="file"]::file-selector-button {
    background-color: #162030 !important;
    color: #fff !important;
    border: 1px solid #2a3240 !important;
    padding: 6px 12px;
    border-radius: 6px;
}

/* عند المرور بالماوس */
.dark-mode input[type="file"]::file-selector-button:hover {
    background-color: #1d2738 !important;
    border-color: #3b4556 !important;
}


/* -----------------------------------
      SEARCH ICON (أيقونة البحث)
----------------------------------- */

/* أي input داخل search bar */
.dark-mode .search-box input,
.dark-mode .search-input,
.dark-mode input[type="search"] {
    background-color: #0b1120 !important;
    color: #fff !important;
    border: 1px solid #222 !important;
}

/* أي أيقونة بحث داخل input */
.dark-mode .search-box i,
.dark-mode .search-icon,
.dark-mode .bi-search,
.dark-mode button.search-btn i {
    color: #fff !important;
    opacity: 0.9;
}

/* زر البحث */
.dark-mode .search-btn {
    background-color: #162030 !important;
    border: 1px solid #222 !important;
    color: #fff !important;
}

.dark-mode .search-btn:hover {
    background-color: #1d2738 !important;
}
/* ===============================
   🌙 DARK MODE — Dashboard
   ضيف كلاس .dark-mode على <body>
=============================== */

.dark-mode {
    background-color: #0a0f1c !important;
    color: #fff !important;
}

/* -----------------------------
   الهيدر العلوي في الداشبورد
------------------------------ */
.dark-mode .dashboard-header {
    background: #141b2d !important;
    color: #fff !important;
    box-shadow: 0 0 0 transparent !important;
    border: 1px solid #1e2639 !important;
}

/* -----------------------------
   الكارت الأساسي الكبير
------------------------------ */
.dark-mode .dashboard-card {
    background: #141b2d !important;
    border: 1px solid #1f273b !important;
    color: #fff !important;
}

.dark-mode .dashboard-card p,
.dark-mode .dashboard-card h3 {
    color: #fff !important;
}

/* -----------------------------
   أيقونة اللوجو الكبيرة
------------------------------ */
.dark-mode .chef-icon {
    background: #0a0f1c !important;
    border-color: #ff6a00 !important;
}

/* -----------------------------
   كروت الإحصائيات
------------------------------ */
.dark-mode .stat-card {
    background: #111827 !important;
    border: 1px solid #1f2937 !important;
    box-shadow: none !important;
}

.dark-mode .stat-title {
    color: #d0d0d0 !important;
}

.dark-mode .stat-value {
    color: #fff !important;
}

.dark-mode .stat-icon {
    background: rgba(255,106,0,0.15) !important;
    color: #ff6a00 !important;
}

/* -----------------------------
   الشارت كارد
------------------------------ */
.dark-mode .chart-card {
    background: #111827 !important;
    border: 1px solid #1f2937 !important;
    color: #fff !important;
    box-shadow: none !important;
}

.dark-mode .chart-card h5 {
    color: #fff !important;
}

/* -----------------------------
   مخططات Chart.js
------------------------------ */
.dark-mode canvas {
    background-color: #0e1625 !important;
    border-radius: 12px;
    padding: 10px;
}

/* تصغير ألوان الليجند */
.dark-mode .chartjs-render-monitor {
    color: #fff !important;
}

/* -----------------------------
   فواصل (HR)
------------------------------ */
.dark-mode hr {
    border-color: #1f2937 !important;
}
/* ==========================================
   🌙 DARK MODE GLOBAL RESET
========================================== */

body.dark-mode {
    background: #0b1220 !important;
    color: #fff !important;
}

/* ==========================================
   🟥 جعل كل الكروت داكنة مهما كان نوعها
========================================== */

body.dark-mode .card,
body.dark-mode .dashboard-card,
body.dark-mode .stat-card,
body.dark-mode .chart-card,
body.dark-mode .shadow,
body.dark-mode .card-body,
body.dark-mode .card-header,
body.dark-mode .card-footer {
    background: #131a2a !important;
    color: #fff !important;
    border-color: #1f273b !important;
    box-shadow: none !important;
}

/* العناوين داخل أي كارت */
body.dark-mode .card h1,
body.dark-mode .card h2,
body.dark-mode .card h3,
body.dark-mode .card h4,
body.dark-mode .card h5,
body.dark-mode .card p,
body.dark-mode .card span {
    color: #fff !important;
}

/* ==========================================
   🟦 الشـــارت (Canvas)
========================================== */

body.dark-mode canvas {
    background: #0e1625 !important;
    border-radius: 10px !important;
    border: 1px solid #1f273b !important;
}

/* إطار الشارت نفسه */
body.dark-mode .chartjs-size-monitor,
body.dark-mode .chart-container {
    background: #131a2a !important;
    border: 1px solid #1f273b !important;
}

/* الكتابة داخل الشارت */
body.dark-mode .chartjs-render-monitor {
    color: #fff !important;
}

/* ==========================================
   🟧 إخفاء أي Border أبيض بالقوة
========================================== */

body.dark-mode * {
    border-color: #1f273b !important;
}

/* ==========================================
   🟩 الستايل الخاص بالكروت الصغيرة (stat cards)
========================================== */

body.dark-mode .stat-icon {
    background: rgba(255, 106, 0, 0.12) !important;
    color: #ff6a00 !important;
}

body.dark-mode .stat-title {
    color: #e5e5e5 !important;
}

body.dark-mode .stat-value {
    color: #ffffff !important;
}
/* =========================================================
   🌙 DARK MODE — صفحة الفاتورة بالكامل
========================================================= */

body.dark-mode .print-area {
    background: #0f172a !important;
    color: #fff !important;
    border-color: #1e293b !important;
}

/* 🔹 الهيدر والنصوص */
body.dark-mode .invoice-header *,
body.dark-mode .invoice-info *,
body.dark-mode .invoice-summary *,
body.dark-mode .invoice-serial,
body.dark-mode .total-words,
body.dark-mode h2,
body.dark-mode strong,
body.dark-mode span,
body.dark-mode div {
    color: #fff !important;
}

/* 🔹 الصورة */
body.dark-mode .invoice-image {
    border: 1px solid #334155 !important;
    box-shadow: none !important;
}

/* =========================================================
   🌙 الجدول داخل الفاتورة
========================================================= */

body.dark-mode table,
body.dark-mode #invoiceTable {
    background: #1e293b !important;
    color: #fff !important;
    border-color: #334155 !important;
}

body.dark-mode table th,
body.dark-mode table td {
    background: #1e293b !important;
    color: #fff !important;
    border: 1px solid #334155 !important;
}

body.dark-mode table th {
    background: #0f172a !important;
    font-weight: bold;
    color: #fff !important;
}

/* الصف المميز */
body.dark-mode .highlighted-row {
    background-color: #3f3f00 !important;
    box-shadow: 0 0 10px #999900 !important;
}

body.dark-mode .blinking {
    box-shadow: 0 0 15px #ffff33 !important;
}

/* =========================================================
   🌙 عناصر الإدخال (input / select)
========================================================= */

body.dark-mode input[type="date"],
body.dark-mode select {
    background: #0f172a !important;
    color: #fff !important;
    border: 1px solid #334155 !important;
}

/* =========================================================
   🌙 زر الطباعة
========================================================= */

body.dark-mode .btn-orange {
    background: #ea580c !important;
    color: #fff !important;
}

/* =========================================================
   🌙 التوتال / الملخص
========================================================= */

body.dark-mode .invoice-summary {
    background: #0f172a !important;
    border-radius: 8px;
    padding: 10px;
}

body.dark-mode .invoice-summary-wrapper {
    background: transparent !important;
}

/* =========================================================
   ❗ استثناء الطباعة (Print يجب يفضل أبيض)
========================================================= */

@media print {
    body.dark-mode .print-area,
    body.dark-mode table,
    body.dark-mode th,
    body.dark-mode td,
    body.dark-mode tr {
        background: #fff !important;
        color: #000 !important;
        border-color: #ccc !important;
    }
}
/* ================================================
   🌙 إجبــار الفاتورة كاملة على الداكن داخل الـ DARK MODE
================================================ */

body.dark-mode .print-area {
    background: #0f172a !important;   /* خلفية داكنة */
    color: #fff !important;           /* نص أبيض */
    border-color: #1e293b !important; 
}

/* عناوين الفاتورة */
body.dark-mode .print-area *,
body.dark-mode .print-area h2,
body.dark-mode .print-area strong,
body.dark-mode .print-area span,
body.dark-mode .print-area div {
    color: #fff !important;
}

/* الكروت / الصناديق داخل الفاتورة */
body.dark-mode .invoice-header,
body.dark-mode .invoice-info,
body.dark-mode .invoice-summary,
body.dark-mode .invoice-summary-wrapper,
body.dark-mode .total-words {
    background: transparent !important;
    color: #fff !important;
}

/* الصورة */
body.dark-mode .invoice-image {
    border: 1px solid #334155 !important;
    box-shadow: none !important;
}

/* يخلي كل شيء داخل الفاتورة يتلون */
body.dark-mode .print-area table th,
body.dark-mode .print-area table td {
    color: #fff !important;
}

/* يمنع الأبيض اللي جاي من inline styles */
body.dark-mode .print-area[style] {
    background: #0f172a !important;
}

  </style>
  <link href="https://fonts.googleapis.com/css2?family=Scheherazade+New:wght@700&display=swap" rel="stylesheet">
</head>
<body>
  <div class="loader">
    <div class="circle">
      <div class="loader-text">دوار السعادة</div>

      <div class="pulse-dot"></div>
      <div class="pulse-dot"></div>
      <div class="pulse-dot"></div>
      <div class="pulse-dot"></div>
      <div class="pulse-dot"></div>
      <div class="pulse-dot"></div>
    </div>
  </div>
<div id="page-wrapper" style="opacity:0; transition:opacity .8s ease;">
<nav class="navbar navbar-expand-lg sticky-top custom-navbar">
  <div class="container-fluid d-flex justify-content-between align-items-center">

    <!-- ✅ اللوجو على اليمين -->
    <a class="navbar-brand d-flex align-items-center" href="<?= BASE_URL ?>/home.php" style="text-decoration:none;">
      <img src="<?= BASE_URL ?>/assets/logo_header2.png" alt="Logo" class="logo-modern">
    </a>

    <!-- ✅ زر القائمة على الشمال (يظهر فقط في الموبايل) -->
    <button class="btn btn-orange d-md-none rounded-circle shadow position-fixed top-2 p-2 z-3"
            style="width:45px; height:45px; left:10px;"
            data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu" aria-label="القائمة">
      <i class="bi bi-list fs-4 text-white"></i>
    </button>

    <!-- ✅ العناصر الثابتة في اليمين الكبير -->
    <ul class="navbar-nav ms-auto align-items-lg-center gap-3 d-none d-md-flex">

      <!-- الدور -->
      <li class="nav-item">
        <span class="badge role-badge">
          <i class="bi bi-person-badge me-1"></i> <?= esc(current_role()) ?>
        </span>
      </li>

      <!-- المستخدمون -->
      <?php if(has_permission('users.view')): ?>
      <li class="nav-item">
        <a class="nav-link <?= $current_page=='users.php'?'active':'' ?>" href="<?= BASE_URL ?>/users.php">
          <i class="bi bi-people me-1"></i> المستخدمون
        </a>
      </li>
      <?php endif ?>

      <!-- Theme toggle -->
      <li class="nav-item d-flex align-items-center">
        <button id="themeToggle" class="btn theme-toggle-btn ms-2" aria-label="Toggle theme" title="تبديل بين الوضع الفاتح والداكن">
          <i id="themeIcon" class="bi bi-moon-fill"></i>
        </button>
      </li>

      <!-- خروج -->
      <li class="nav-item">
        <a class="btn btn-logout" href="<?= BASE_URL ?>/logout.php">
          <i class="bi bi-box-arrow-right me-1"></i> خروج
        </a>
      </li>

    </ul>
  </div>
</nav>
</div>

<!-- القائمة الجانبية في الموبايل (Offcanvas) -->
<div class="offcanvas offcanvas-start" tabindex="-1" id="sidebarMenu">
  <div class="offcanvas-header border-bottom" style="padding: 6px 10px; height: 65px; min-height: unset;">
    <a href="<?= BASE_URL ?>/home.php" 
      class="navbar-brand d-flex align-items-center text-decoration-none">
      <img src="<?= BASE_URL ?>/assets/logo_header2.png" 
          style="height: 32px; width: auto; transform: scale(1.5); margin-right: 30px;"
          alt="logo" 
          class="rounded">
    </a>
    <button type="button" class="btn-close" data-bs-dismiss="offcanvas" style="transform: scale(0.85);"></button>
  </div>

  <div class="offcanvas-body">

    <!-- ✅ روابط أساسية -->
    <a class="sidebar-link d-block mb-2 <?= $current_page=='home.php'?'active':'' ?>" href="<?= BASE_URL ?>/home.php">
      <i class="bi bi-house"></i> الرئيسية
    </a>

    <?php if(has_permission('purchases.view')): ?>
    <a class="sidebar-link d-block mb-2 <?= $current_page=='purchases.php'?'active':'' ?>" href="<?= BASE_URL ?>/purchases.php">
      <i class="bi bi-bag"></i> تهيئة المشتريات
    </a>
    <?php endif ?>

    <?php if(has_permission('orders.view')): ?>
    <a class="sidebar-link d-block mb-2 <?= $current_page=='orders.php'?'active':'' ?>" href="<?= BASE_URL ?>/orders.php">
      <i class="bi bi-gear"></i> أوامر التشغيل
    </a>
    <?php endif ?>

    <?php if(has_permission('custodies.view')): ?>
    <a class="sidebar-link d-block mb-2 <?= $current_page=='custodies.php'?'active':'' ?>" href="<?= BASE_URL ?>/custodies.php">
      <i class="bi bi-wallet2"></i> العهد
    </a>
    <?php endif ?>

    <?php if(has_permission('assets.view')): ?>
    <a class="sidebar-link d-block mb-2 <?= $current_page=='assetes.php'?'active':'' ?>" href="<?= BASE_URL ?>/assetes.php">
      <i class="bi bi-building"></i> الأصول
    </a>
    <?php endif ?>

    <?php if(has_permission('expenses.view')): ?>
    <a class="sidebar-link d-block mb-2 <?= $current_page=='expenses.php'?'active':'' ?>" href="<?= BASE_URL ?>/expenses.php">
      <i class="bi bi-cash-stack"></i> المصروفات
    </a>
    <?php endif ?>

    <?php if(has_permission('reports.view')): ?>
    <a class="sidebar-link d-block mb-2 <?= $current_page=='reports.php'?'active':'' ?>" href="<?= BASE_URL ?>/reports.php">
      <i class="bi bi-graph-up"></i> التقارير
    </a>
    <?php endif ?>

    <?php if(has_permission('settings.edit')): ?>
    <hr class="my-3">
    <h6 class="text-muted small px-2">الإعدادات</h6>

    <?php if(has_permission('roles.view')): ?>
    <a class="sidebar-link d-block mb-2 <?= $current_page=='roles.php'?'active':'' ?>" href="<?= BASE_URL ?>/roles.php">
      <i class="bi bi-shield-lock"></i> الأدوار
    </a>
    <?php endif ?>

    <?php if(has_permission('permissions.view')): ?>
    <a class="sidebar-link d-block mb-2 <?= $current_page=='permissions.php'?'active':'' ?>" href="<?= BASE_URL ?>/permissions.php">
      <i class="bi bi-person-check"></i> الصلاحيات
    </a>
    <?php endif ?>
    <?php endif ?>

    <!-- ✅ خط فاصل قبل المستخدم -->
    <hr class="my-3">

    <!-- ✅ معلومات المستخدم -->
    <div class="px-2 mb-3">
      <span class="badge bg-light text-dark w-100 d-flex align-items-center justify-content-center py-2">
        <i class="bi bi-person-badge me-2 text-orange"></i>
        <span><?= esc(current_role()) ?></span>
      </span>
    </div>

    <?php if(has_permission('users.view')): ?>
    <a class="sidebar-link d-block mb-2 <?= $current_page=='users.php'?'active':'' ?>" href="<?= BASE_URL ?>/users.php">
      <i class="bi bi-people"></i> المستخدمون
    </a>
    <?php endif ?>

    <!-- ✅ تسجيل الخروج -->
    <a class="btn btn-logout w-100 mt-2 d-flex align-items-center justify-content-center gap-2" href="<?= BASE_URL ?>/logout.php">
      <i class="bi bi-box-arrow-right"></i> خروج
    </a>

  </div>
</div>


<div class="container-fluid">
  <div class="row">
    <!-- Sidebar في الديسكتوب -->
    <aside class="col-lg-2 col-md-3 border-end min-vh-100 d-none d-md-block">
      <div class="p-3">
        <div class="text-muted small mb-2">القائمة</div>
        <a class="sidebar-link d-block mb-2 <?= $current_page=='home.php'?'active':'' ?>" href="<?= BASE_URL ?>/home.php"><i class="bi bi-house"></i> الرئيسية</a>
        <?php if(has_permission('purchases.view')): ?>
        <a class="sidebar-link d-block mb-2 <?= $current_page=='purchases.php'?'active':'' ?>" href="<?= BASE_URL ?>/purchases.php"><i class="bi bi-bag"></i> تهيئة المشتريات</a>
        <?php endif ?>
        <?php if(has_permission('orders.view')): ?>
        <a class="sidebar-link d-block mb-2 <?= $current_page=='orders.php'?'active':'' ?>" href="<?= BASE_URL ?>/orders.php"><i class="bi bi-gear"></i> أوامر التشغيل</a>
        <?php endif ?>
        <?php if(has_permission('custodies.view')): ?>
        <a class="sidebar-link d-block mb-2 <?= $current_page=='custodies.php'?'active':'' ?>" href="<?= BASE_URL ?>/custodies"><i class="bi bi-wallet2"></i> العهد</a>
        <?php endif ?>
        <?php if(has_permission('assets.view')): ?>
        <a class="sidebar-link d-block mb-2 <?= $current_page=='assetes.php'?'active':'' ?>" href="<?= BASE_URL ?>/assetes.php"><i class="bi bi-building"></i> الأصول</a>
        <?php endif ?>
        <!--<a class="sidebar-link d-block mb-2 <?= $current_page=='gov_fees.php'?'active':'' ?>" href="<?= BASE_URL ?>/gov_fees.php"><i class="bi bi-file-earmark-text"></i> الرسوم الحكومية</a>
        <a class="sidebar-link d-block mb-2 <?= $current_page=='subscriptions.php'?'active':'' ?>" href="<?= BASE_URL ?>/subscriptions.php"><i class="bi bi-journal-bookmark"></i> الاشتراكات والخدمات</a>
        <a class="sidebar-link d-block mb-2 <?= $current_page=='rentals.php'?'active':'' ?>" href="<?= BASE_URL ?>/rentals.php"><i class="bi bi-house-door"></i> الإيجارات</a>-->
        <?php if(has_permission('expenses.view')): ?>
        <a class="sidebar-link d-block <?= $current_page=='expenses.php'?'active':'' ?>" href="<?= BASE_URL ?>/expenses.php"><i class="bi bi-cash-stack"></i> المصروفات</a>
        <?php endif ?>
        <?php if(has_permission('reports.view')): ?>
        <a class="sidebar-link d-block <?= $current_page=='reports.php'?'active':'' ?>" href="<?= BASE_URL ?>/reports.php"><i class="bi bi-graph-up"></i> التقارير</a>
        <?php endif ?>
        <?php if(has_permission('settings.edit')): ?>
        <hr class="my-2">
        <h6 class="text-muted small px-2">الإعدادات</h6>
        <?php if(has_permission('roles.view')): ?>
        <a class="sidebar-link d-block mb-2 <?= $current_page=='roles.php'?'active':'' ?>" href="<?= BASE_URL ?>/roles.php">
            <i class="bi bi-shield-lock"></i> الأدوار
        </a>
        <?php endif ?>
        <?php if(has_permission('permissions.view')): ?>
        <a class="sidebar-link d-block mb-2 <?= $current_page=='permissions.php'?'active':'' ?>" href="<?= BASE_URL ?>/permissions.php">
            <i class="bi bi-person-check"></i> الصلاحيات
        </a>
        <?php endif ?>
        <?php endif ?>
      </div>
    </aside>

    <!-- المحتوى -->
    <main class="col-12 col-md-9 col-lg-10 p-4">
      <?php if($m = flash('msg')): ?>
        <div class="flash mb-3"><?= esc($m) ?></div>
      <?php endif; ?>

  <script>
    window.addEventListener('load', () => {
      const loader = document.querySelector('.loader');
      const page = document.getElementById('page-wrapper');

      // إخفاء اللودر
      loader.classList.add('hidden');

      // إظهار المحتوى تدريجيًا
      page.style.opacity = '1';

      // لو فيه Toast
      const toastEl = document.getElementById('liveToast');
      if(toastEl){
        const toast = new bootstrap.Toast(toastEl, { delay: 2500 });
        toast.show();
      }
    });
  </script>
  <script>
(function(){
  const root = document.documentElement;
  const toggle = document.getElementById('themeToggle');
  const icon = document.getElementById('themeIcon');

  // قراءة التفضيل: localStorage -> prefers-color-scheme
  function getPreferredTheme(){
    const saved = localStorage.getItem('theme');
    if(saved === 'dark' || saved === 'light') return saved;
    // fallback to system
    const sys = window.matchMedia && window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
    return sys;
  }

  // تطبيق الثيم
  function applyTheme(theme){
    if(theme === 'dark'){
      root.setAttribute('data-theme','dark');
      icon.className = 'bi bi-sun-fill'; // show sun to indicate can switch to light
      icon.setAttribute('title','الوضع الفاتح');
    } else {
      root.removeAttribute('data-theme');
      icon.className = 'bi bi-moon-fill';
      icon.setAttribute('title','الوضع الداكن');
    }
  }

  // شغّل حسب المحفوظ أو التفضيل
  const initial = getPreferredTheme();
  applyTheme(initial);

  // الاستماع لتغيّر إعداد النظام (لو المستخدم غير تفضيله)
  if(window.matchMedia) {
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', e => {
      const saved = localStorage.getItem('theme');
      if(!saved) applyTheme(e.matches ? 'dark' : 'light');
    });
  }

  // زر التبديل
  toggle?.addEventListener('click', () => {
    const current = document.documentElement.getAttribute('data-theme') === 'dark' ? 'dark' : 'light';
    const next = current === 'dark' ? 'light' : 'dark';
    localStorage.setItem('theme', next);
    applyTheme(next);
  });

  // إمكانية مسح الاختيار للمستخدم (optional) عبر الضغط مع Ctrl+Click
  toggle?.addEventListener('dblclick', (e) => {
    // ضع الاختيار على automatic (يتبع النظام)
    localStorage.removeItem('theme');
    applyTheme(getPreferredTheme());
  });
})();
</script>
