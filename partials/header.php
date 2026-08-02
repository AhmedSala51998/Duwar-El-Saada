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
    /* ============================
        🌙 DARK MODE GLOBAL THEME
    ============================ */
    body.dark-mode {
      background-color: #121212 !important;
      color: #ffffff !important;
    }

    /* النص */
    body.dark-mode * {
      color: #eaeaea !important;
    }

    /* الروابط */
    body.dark-mode a {
      color: #ff944d !important;
    }

    /* النافبار */
    body.dark-mode .custom-navbar {
      background: rgba(18,18,18,0.9) !important;
      border-bottom: 1px solid #333 !important;
    }

    /* الأيقونات */
    body.dark-mode i {
      color: #ff944d !important;
    }

    /* الخلفيات العامة */
    body.dark-mode .card,
    body.dark-mode .table,
    body.dark-mode .modal-content,
    body.dark-mode .offcanvas,
    body.dark-mode .dropdown-menu,
    body.dark-mode .form-control,
    body.dark-mode input,
    body.dark-mode select,
    body.dark-mode textarea {
      background-color: #1e1e1e !important;
      color: #fff !important;
      border-color: #333 !important;
    }

    /* الكروت */
    body.dark-mode .card {
      box-shadow: 0 0 10px rgba(0,0,0,0.5) !important;
    }

    /* الجداول */
    body.dark-mode table {
      color: #fff !important;
    }
    body.dark-mode table tr {
      background: #1b1b1b !important;
    }
    body.dark-mode table td,
    body.dark-mode table th {
      border-color: #333 !important;
    }

    /* المودال */
    body.dark-mode .modal-header,
    body.dark-mode .modal-footer {
      border-color: #333 !important;
    }

    /* dropdown */
    body.dark-mode .dropdown-menu {
      background-color: #1f1f1f !important;
      border-color: #333 !important;
    }
    body.dark-mode .dropdown-item:hover {
      background-color: #333 !important;
    }

    /* البادجات */
    body.dark-mode .role-badge {
      background: #2c2c2c !important;
      color: #ff944d !important;
      border-color: #ff944d !important;
    }

    /* السايدبار */
    body.dark-mode .sidebar-link {
      color: #ddd !important;
    }
    body.dark-mode .sidebar-link:hover,
    body.dark-mode .sidebar-link.active {
      background: #333 !important;
      color: #ff944d !important;
    }

    /* loader */
    body.dark-mode .loader {
      background: #121212 !important;
    }
    body.dark-mode .loader-text {
      color: #ff944d !important;
    }

    /* buttons */
    body.dark-mode .btn-logout,
    body.dark-mode .btn-orange {
      background: #ff6a00 !important;
      color: white !important;
    }
    body.dark-mode .btn-orange:hover {
      background: #e65c00 !important;
    }

    /* حقول الإدخال */
    body.dark-mode .form-control {
      background: #1f1f1f !important;
      color: white !important;
      border-color: #444 !important;
    }

    body.dark-mode .form-control:focus {
      background: #222 !important;
      border-color: #ff944d !important;
      color: #fff !important;
    }
    /* خلفية الكونتينر */
    body.dark-mode .table-responsive {
        background-color: #1a1a1a !important;
        border-color: #333 !important;
    }

    /* خلفية الجدول */
    body.dark-mode .custom-table {
        background-color: #1a1a1a !important;
    }

    /* خلايا الجدول */
    body.dark-mode .custom-table td,
    body.dark-mode .custom-table th {
        background-color: #1e1e1e !important;
        color: #fff !important;
    }

    /* رأس الجدول */
    body.dark-mode .custom-table thead th {
        background-color: #222 !important;
        color: #fff !important;
    }

    /* الصفوف */
    body.dark-mode .custom-table tbody tr:nth-child(even) td {
        background-color: #262626 !important;
    }
    /* ============================
      1) Pagination
    ============================ */
    body.dark-mode .pagination .page-link {
        background-color: #1e1e1e !important;
        border-color: #333 !important;
        color: #fff !important;
    }

    body.dark-mode .pagination .page-item.active .page-link {
        background-color: #ff6a00 !important;
        border-color: #ff6a00 !important;
        color: #fff !important;
    }

    body.dark-mode .pagination .page-item.disabled .page-link {
        background-color: #2a2a2a !important;
        color: #777 !important;
        border-color: #333 !important;
    }

    /* ============================
      2) Placeholder في الدارك مود
    ============================ */
    body.dark-mode input::placeholder,
    body.dark-mode textarea::placeholder,
    body.dark-mode select::placeholder {
        color: #ccc !important;
        opacity: 1;
    }

    /* ============================
      3) Dropdown (قوائم Bootstrap)
    ============================ */
    body.dark-mode .dropdown-menu {
        background-color: #1e1e1e !important;
        border-color: #333 !important;
        color: #fff !important;
    }

    body.dark-mode .dropdown-item {
        color: #fff !important;
    }

    body.dark-mode .dropdown-item:hover,
    body.dark-mode .dropdown-item:focus {
        background-color: #333 !important;
        color: #fff !important;
    }

    /* ============================
      4) Select2
    ============================ */
    body.dark-mode .select2-container--default .select2-selection--single {
        background-color: #1e1e1e !important;
        border-color: #333 !important;
        color: #fff !important;
    }

    body.dark-mode .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #fff !important;
    }

    body.dark-mode .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: #ccc !important;
    }

    body.dark-mode .select2-dropdown {
        background-color: #1e1e1e !important;
        border-color: #333 !important;
    }

    body.dark-mode .select2-results__option {
        color: #fff !important;
    }

    body.dark-mode .select2-results__option--highlighted {
        background-color: #333 !important;
        color: #fff !important;
    }

    /* ============================
      5) Inputs + file input
    ============================ */
    body.dark-mode input,
    body.dark-mode textarea,
    body.dark-mode select {
        background-color: #1e1e1e !important;
        color: #fff !important;
        border-color: #333 !important;
    }

    body.dark-mode input[type="file"] {
        background-color: #1e1e1e !important;
        color: #fff !important;
    }

    /* نص اسم الملف المختار */
    body.dark-mode .form-control-file,
    body.dark-mode .custom-file-label {
        background-color: #1e1e1e !important;
        color: #fff !important;
        border-color: #333 !important;
    }
    /* ======================================
      1) مودال بالكامل في الدارك مود
    ====================================== */
    body.dark-mode .modal-content {
        background-color: #1f1f1f !important;
        color: #fff !important;
        border-color: #333 !important;
    }

    body.dark-mode .modal-header,
    body.dark-mode .modal-footer {
        background-color: #1c1c1c !important;
        border-color: #333 !important;
    }

    body.dark-mode .modal-title {
        color: #fff !important;
    }

    body.dark-mode .btn-close {
        filter: invert(1) brightness(200%);
    }

    /* ======================================
      2) الجداول داخل المودال
    ====================================== */
    body.dark-mode .modal-content table,
    body.dark-mode .modal-content .table {
        background-color: #1b1b1b !important;
        color: #fff !important;
    }

    body.dark-mode .modal-content table td,
    body.dark-mode .modal-content table th {
        background-color: #222 !important;
        color: #fff !important;
        border-color: #444 !important;
    }

    /* الصفوف بالتبادل */
    body.dark-mode .modal-content table tbody tr:nth-child(even) td {
        background-color: #262626 !important;
    }

    /* ======================================
      3) زر X أو أي زر أحمر أو رمادي داخل المودال
    ====================================== */
    body.dark-mode .btn-danger,
    body.dark-mode .btn-outline-danger {
        background-color: #a00000 !important;
        border-color: #cc0000 !important;
        color: #fff !important;
    }

    body.dark-mode .btn-outline-secondary,
    body.dark-mode .btn-secondary {
        background-color: #2d2d2d !important;
        color: #fff !important;
        border-color: #555 !important;
    }

    /* ======================================
      4) INPUT FILE داخل المودال وخارجها
    ====================================== */
    body.dark-mode input[type="file"],
    body.dark-mode .custom-file-label,
    body.dark-mode .form-control-file {
        background-color: #1e1e1e !important;
        color: #fff !important;
        border-color: #333 !important;
    }

    body.dark-mode input[type="file"]::-webkit-file-upload-button {
        background-color: #333 !important;
        color: #fff !important;
        border-color: #555 !important;
    }

    /* ======================================
      5) BADGE bg-light تتحول دارك مود
    ====================================== */
    body.dark-mode .badge.bg-light,
    body.dark-mode .badge.bg-light.text-dark {
        background-color: #333 !important;
        color: #fff !important;
        border-color: #444 !important;
    }

    /* ======================================
      6) SELECT + DROPDOWN داخل المودال
    ====================================== */
    body.dark-mode .modal-content select,
    body.dark-mode .modal-content .form-select {
        background-color: #1e1e1e !important;
        color: #fff !important;
        border-color: #444 !important;
    }

    body.dark-mode .modal-content option {
        background-color: #1e1e1e !important;
        color: #fff !important;
    }

    /* ======================================
      7) أي INPUT أو TEXTAREA داخل المودال
    ====================================== */
    body.dark-mode .modal-content input,
    body.dark-mode .modal-content textarea {
        background-color: #1e1e1e !important;
        color: #fff !important;
        border-color: #444 !important;
    }

    body.dark-mode .modal-content input::placeholder,
    body.dark-mode .modal-content textarea::placeholder {
        color: #bbb !important;
    }
    /* الوضع الداكن للملفات والمودالز */
    body.dark-mode {
        background-color: #121212; /* خلفية عامة داكنة */
        color: #ffffff; /* نص أبيض */
    }

    /* الدايركت على الملفات */
    body.dark-mode .custom-file-upload {
        background-color: #1e1e1e;
        color: #ffffff;
        border: 1px solid #333;
        box-shadow: 0 2px 5px rgba(0,0,0,0.5);
    }

    /* أي نصوص داخل الملفات */
    body.dark-mode .custom-file-upload span,
    body.dark-mode .custom-file-upload i {
        color: #ffffff;
    }

    /* حالة hover للملفات */
    body.dark-mode .custom-file-upload:hover {
        background-color: #2a2a2a;
    }

    /* preview الصور */
    body.dark-mode #preview-inv-main {
        border: 1px solid #444;
    }

    /* الـ alerts */
    body.dark-mode .alert {
        background-color: #1e1e1e;
        color: #ffffff;
        border: 1px solid #333;
    }

    /* القوائم داخل الـ alerts */
    body.dark-mode .alert ul li {
        color: #ffffff;
    }

    /* الخطوط والفواصل */
    body.dark-mode hr {
        border-color: #333;
    }
    /* Dark Mode Modals */
    body.dark-mode .modal-content {
        background-color: #1e1e1e !important; /* خلفية المودال داكنة */
        color: #ffffff !important; /* نص أبيض */
    }

    body.dark-mode .modal-header,
    body.dark-mode .modal-footer {
        background-color: #2a2a2a !important; /* رأس وتذييل المودال */
        color: #ffffff !important;
    }

    body.dark-mode .form-control,
    body.dark-mode .form-control:focus,
    body.dark-mode textarea {
        background-color: #2a2a2a !important;
        color: #ffffff !important;
        border: 1px solid #444 !important;
    }

    body.dark-mode .accordion-button {
        background-color: #2a2a2a !important;
        color: #ffffff !important;
    }

    body.dark-mode .accordion-button:not(.collapsed) {
        background-color: #3a3a3a !important;
        color: #ffffff !important;
    }

    body.dark-mode .accordion-body {
        background-color: #2a2a2a !important;
        color: #ffffff !important;
    }

    body.dark-mode .form-check-label {
        color: #ffffff !important;
    }

    body.dark-mode .btn-close {
        filter: invert(1); /* لجعل زر الإغلاق مرئي */
    }

    /* Badges داخل المودال */
    body.dark-mode .badge {
        background-color: #333 !important;
        color: #fff !important;
    }

    /* أي Table داخل المودال */
    body.dark-mode .table {
        color: #ffffff;
    }

    body.dark-mode .table th,
    body.dark-mode .table td {
        border-color: #444 !important;
    }

    /* تنسيق أزرار المودال */
    body.dark-mode .btn-outline-warning {
        color: #ff8800 !important;
        border-color: #ff8800 !important;
    }

    body.dark-mode .btn-outline-danger {
        color: #ff5555 !important;
        border-color: #ff5555 !important;
    }

    body.dark-mode .btn-outline-success {
        color: #00cc66 !important;
        border-color: #00cc66 !important;
    }
    /* Dark Mode for Add Multiple Roles & View Permissions Modals */
    body.dark-mode #addMultipleRoles .modal-content,
    body.dark-mode #viewPerms<?= $r['id'] ?> .modal-content {
        background-color: #1e1e1e !important;
        color: #ffffff !important;
    }

    /* Header & Footer */
    body.dark-mode #addMultipleRoles .modal-header,
    body.dark-mode #viewPerms<?= $r['id'] ?> .modal-header,
    body.dark-mode #addMultipleRoles .modal-footer,
    body.dark-mode #viewPerms<?= $r['id'] ?> .modal-footer {
        background-color: #2a2a2a !important;
        color: #ffffff !important;
    }

    /* Inputs, textarea, selects */
    body.dark-mode #addMultipleRoles .form-control,
    body.dark-mode #addMultipleRoles textarea,
    body.dark-mode #viewPerms<?= $r['id'] ?> .form-control,
    body.dark-mode #viewPerms<?= $r['id'] ?> textarea {
        background-color: #2a2a2a !important;
        color: #ffffff !important;
        border: 1px solid #444 !important;
    }

    /* Table & checkboxes */
    body.dark-mode #addMultipleRoles .table,
    body.dark-mode #addMultipleRoles .table th,
    body.dark-mode #addMultipleRoles .table td {
        border-color: #444 !important;
        color: #ffffff !important;
    }

    body.dark-mode #addMultipleRoles .permissions-box,
    body.dark-mode #addMultipleRoles .permissions-box .border,
    body.dark-mode #addMultipleRoles .permissions-box strong,
    body.dark-mode #addMultipleRoles .permissions-box label {
        background-color: #2a2a2a !important;
        color: #ffffff !important;
        border-color: #444 !important;
    }

    /* Accordion */
    body.dark-mode #addMultipleRoles .accordion-button,
    body.dark-mode #viewPerms<?= $r['id'] ?> .accordion-button {
        background-color: #2a2a2a !important;
        color: #ffffff !important;
    }
    body.dark-mode #addMultipleRoles .accordion-button:not(.collapsed),
    body.dark-mode #viewPerms<?= $r['id'] ?> .accordion-button:not(.collapsed) {
        background-color: #3a3a3a !important;
        color: #ffffff !important;
    }
    body.dark-mode #addMultipleRoles .accordion-body,
    body.dark-mode #viewPerms<?= $r['id'] ?> .accordion-body {
        background-color: #2a2a2a !important;
        color: #ffffff !important;
    }

    /* زراغلاق المودال */
    body.dark-mode #addMultipleRoles .btn-close,
    body.dark-mode #viewPerms<?= $r['id'] ?> .btn-close {
        filter: invert(1); /* يظهر باللون الأبيض */
    }

    /* Badges */
    body.dark-mode #addMultipleRoles .badge,
    body.dark-mode #viewPerms<?= $r['id'] ?> .badge {
        background-color: #333 !important;
        color: #fff !important;
    }

    /* أيقونات السهم والأيقونات داخل المودال */
    body.dark-mode #addMultipleRoles i,
    body.dark-mode #viewPerms<?= $r['id'] ?> i {
        color: #ffffff !important;
    }

    /* قائمة الصلاحيات في viewPerms */
    body.dark-mode #viewPerms<?= $r['id'] ?> .list-group-item {
        background-color: #2a2a2a !important;
        color: #ffffff !important;
        border-color: #444 !important;
    }

    body.dark-mode #viewPerms<?= $r['id'] ?> .text-orange {
        color: #ff8800 !important; /* يحافظ على اللون البرتقالي للكود */
    }
    /* فقط في الدارك مود */
    body.dark-mode .dark-perms {
      background-color: #1e1e1e; /* خلفية داكنة */
      color: #ffffff;            /* نص أبيض */
      border-radius: 5px;
      padding: 10px;
    }

    body.dark-mode .dark-perms .list-group-item {
      background-color: #2a2a2a; /* خلفية عناصر الليست */
      color: #ffffff;             /* نص أبيض */
    }

    body.dark-mode .dark-perms code {
      color: #ffa500; /* لون الكود لو حابب تظل برتقالي */
    }
    /* الدارك مود */
    body.dark-mode {
      background-color: #121212;
      color: #fff;
    }

    /* الكروت الإحصائية */
    body.dark-mode .stat-card,
    body.dark-mode .chart-card,
    body.dark-mode .dashboard-card {
      background-color: #1e1e1e !important;
      color: #fff !important;
      box-shadow: 0 2px 10px rgba(0, 0, 0, 0.5);
    }

    /* النصوص داخل الكروت */
    body.dark-mode .stat-title,
    body.dark-mode .stat-value,
    body.dark-mode h5 {
      color: #fff !important;
    }

    /* شارتس */
    body.dark-mode canvas {
      background-color: #1e1e1e !important;
    }

    /* مودال عرض الصلاحيات */
    body.dark-mode .modal-content {
      background-color: #1e1e1e !important;
      color: #fff !important;
    }

    /* الليست داخل المودال */
    body.dark-mode .modal-content .list-group-item {
      background-color: #2a2a2a !important;
      color: #fff !important;
      border: 1px solid #333;
    }

    /* الكود برتقالي يبقى واضح */
    body.dark-mode .modal-content code {
      color: #ffa500 !important;
    }

    /* زر الإغلاق */
    body.dark-mode .modal-content .btn-close {
      filter: invert(1); /* يخلي زر الإغلاق أبيض */
    }
    /* ======= Dark Mode للفاتورة ======= */
    body.dark-mode .print-area {
        background-color: #1e1e1e !important;
        color: #fff !important;
        border-color: #333 !important;
    }

    body.dark-mode .print-area table th {
        background-color: #2c2c2c !important;
        color: #fff !important;
        border-color: #444 !important;
    }

    body.dark-mode .print-area table td {
        background-color: #1e1e1e !important;
        color: #fff !important;
        border-color: #444 !important;
    }

    body.dark-mode .print-area .highlighted-row {
        background-color: #444d1e !important;
        box-shadow: 0 0 10px rgba(255, 193, 7, 0.6);
    }

    body.dark-mode .print-area .blinking {
        transform: scale(1.03);
        box-shadow: 0 0 15px rgba(255, 193, 7, 0.9);
    }

    body.dark-mode .invoice-summary, 
    body.dark-mode .invoice-info, 
    body.dark-mode .total-words {
        color: #fff !important;
    }

    body.dark-mode .invoice-image {
        border-color: #555 !important;
        box-shadow: 1px 1px 5px rgba(0,0,0,0.5);
    }

    /* select و span الضريبة */
    body.dark-mode select#vatRate,
    body.dark-mode #vatRateText {
        background-color: #2c2c2c !important;
        color: #fff !important;
        border-color: #555 !important;
    }

    /* inputs التاريخ */
    body.dark-mode #invoiceDate {
        background-color: #2c2c2c !important;
        color: #fff !important;
        border-color: #555 !important;
    }
    /* ===== Dark Mode للفلاتر فقط ===== */
    body.dark-mode .filter-form .form-control {
        background-color: #2c2c2c !important;
        color: #fff !important;
        border: 1px solid #555 !important;
    }

    body.dark-mode .filter-form .form-label {
        color: #fff !important;
    }

    body.dark-mode .filter-form button.btn-warning {
        background-color: #ff6a00 !important; /* يظل برتقالي */
        color: #fff !important;
        border: none !important;
    }
    @media print {
      body * { visibility: hidden; }
      .print-area, .print-area * { visibility: visible; }
      .print-area { position: absolute; left: 0; top: 0; width: 100%; }

      /* Force Light Mode styles */
      .print-area {
        background-color: #fff !important;
        color: #000 !important;
        border-color: #ccc !important;
      }

      .print-area table th {
        background-color: #f2f2f2 !important;
        color: #000 !important;
        border-color: #ccc !important;
      }

      .print-area table td {
        background-color: #fff !important;
        color: #000 !important;
        border-color: #ccc !important;
      }
    }
    /* ===== Dark Mode للفلاتر فقط ===== */
    body.dark-mode .filter-form {
        background-color: #1e1e1e; /* خلفية عامة للفورم */
        padding: 15px;
        border-radius: 8px;
    }

    body.dark-mode .filter-form .form-control {
        background-color: #2c2c2c !important; /* Inputs */
        color: #fff !important;
        border: 1px solid #555 !important;
    }

    body.dark-mode .filter-form .form-label {
        color: #fff !important; /* Labels */
    }

    body.dark-mode .filter-form button.btn-warning {
        background-color: #ff6a00 !important; /* يظل برتقالي */
        color: #fff !important;
        border: none !important;
    }

    /* لو في hover أو focus على الـ inputs */
    body.dark-mode .filter-form .form-control:focus {
        background-color: #3a3a3a !important;
        color: #fff !important;
        border-color: #ff6a00 !important; /* إشارة تمييز */
    }
    /* ====== Dark Mode فقط ====== */
    body.dark-mode .search-form .input-group {
      max-width: 250px;
    }

    body.dark-mode .search-form .input-group-text {
      background-color: #333; /* خلفية داكنة */
      color: #fff;            /* أيقون أبيض */
      border-color: #555;     /* حدود داكنة */
    }

    body.dark-mode .search-form .form-control {
      background-color: #222; /* خلفية الداكنة */
      color: #fff;            /* نص أبيض */
      border-color: #555;
    }

    body.dark-mode .search-form button.btn-orange i,
    body.dark-mode .search-form a.btn-outline-secondary i {
      color: #fff; /* أيقونات الأزرار أبيض */
    }

    /* placeholder */
    body.dark-mode .search-form .form-control::placeholder {
          color: #bbb;
        }
        /* ====== Dark Mode للبحث ====== */
    body.dark-mode .search-form .input-group-text {
      background-color: #333 !important; /* خلفية داكنة */
      color: #fff !important;            /* أيقون أبيض */
      border-color: #555 !important;     /* حدود داكنة */
    }

    body.dark-mode .search-form .form-control {
      background-color: #222 !important; /* خلفية الداكنة */
      color: #fff !important;            /* نص أبيض */
      border-color: #555 !important;
    }

    body.dark-mode .search-form .form-control::placeholder {
      color: #bbb !important;
    }

    body.dark-mode .search-form button.btn-orange i,
    body.dark-mode .search-form a.btn-outline-secondary i {
      color: #fff !important; /* أيقونات الأزرار أبيض */
    }
    /* الزر */
    #toggleDarkMobile,#toggleDarkDesktop {
      background: #ff6a00;
      color: #fff;
      border: none;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 4px 12px rgba(255,106,0,0.4);
      position: relative;
      overflow: hidden;
    }

    /* hover effect */
    #toggleDarkMobile:hover, #toggleDarkDesktop:hover{
      transform: scale(1.1);
      box-shadow: 0 6px 20px rgba(255,106,0,0.6);
    }

    /* فقاعات */
    #toggleDarkMobile .bubble {
      position: absolute;
      border-radius: 50%;
      background-color: rgba(255,255,255,0.5);
      pointer-events: none;
      animation: rise 2s infinite;
      opacity: 0;
    }
    #toggleDarkDesktop .bubble {
      position: absolute;
      border-radius: 50%;
      background-color: rgba(255,255,255,0.5);
      pointer-events: none;
      animation: rise 2s infinite;
      opacity: 0;
    }

    .bubble1 { width: 6px; height: 6px; bottom: 5px; left: 20%; animation-delay: 0s; }
    .bubble2 { width: 8px; height: 8px; bottom: 5px; left: 50%; animation-delay: 0.5s; }
    .bubble3 { width: 4px; height: 4px; bottom: 5px; left: 80%; animation-delay: 1s; }

    @keyframes rise {
      0% { transform: translateY(0) scale(0.5); opacity: 1; }
      80% { opacity: 0.5; }
      100% { transform: translateY(-50px) scale(1); opacity: 0; }
    }

    /* الزر العام */
    #toggleDarkDesktop,#toggleDarkMobile {
      background: #ff6a00;
      color: #fff;
      border: none;
      cursor: pointer;
      transition: all 0.3s ease;
      box-shadow: 0 4px 12px rgba(255,106,0,0.4);
      position: relative;
      overflow: hidden;
    }

    /* hover effect */
    #toggleDarkMobile:hover, #toggleDarkDesktop:hover {
      transform: scale(1.1);
      box-shadow: 0 6px 20px rgba(255,106,0,0.6);
    }

    /* فقاعات */
    #toggleDarkMobile .bubble {
      position: absolute;
      border-radius: 50%;
      background-color: rgba(255,255,255,0.5);
      pointer-events: none;
      animation: rise 2s infinite;
      opacity: 0;
    }
    #toggleDarkDesktop .bubble {
      position: absolute;
      border-radius: 50%;
      background-color: rgba(255,255,255,0.5);
      pointer-events: none;
      animation: rise 2s infinite;
      opacity: 0;
    }

    .bubble1 { width: 6px; height: 6px; bottom: 5px; left: 20%; animation-delay: 0s; }
    .bubble2 { width: 8px; height: 8px; bottom: 5px; left: 50%; animation-delay: 0.5s; }
    .bubble3 { width: 4px; height: 4px; bottom: 5px; left: 80%; animation-delay: 1s; }

    @keyframes rise {
      0% { transform: translateY(0) scale(0.5); opacity: 1; }
      80% { opacity: 0.5; }
      100% { transform: translateY(-50px) scale(1); opacity: 0; }
    }

    /* أيقونات في الدارك مود */
    .dark-mode #toggleIconMobile,#toggleIconDesktop,
    .dark-mode #logoutBtn i {
      color: #fff !important;
    }
    /* Dark Mode للـ Accordion */
    body.dark-mode .accordion-button {
        background-color: #333; /* خلفية داكنة */
        color: #fff;            /* النص أبيض */
        border-color: #444;     /* حدود داكنة */
    }

    /* السهم */
    body.dark-mode .accordion-button::after {
        filter: invert(1);      /* يجعل السهم أبيض */
    }
    /* ======= اجبار الفاتورة على شكل Light Mode عند الطباعة ======= */
    /* ======= الطباعة: النصوص سوداء فقط ======= */
    @media print {
        .print-area, 
        .print-area * {
            color: #000 !important;  /* النص أسود */
            -webkit-print-color-adjust: exact; /* لتأكيد الطباعة الصحيحة للألوان */
        }

        .print-area table th,
        .print-area table td {
            color: #000 !important;
            border-color: #000 !important; /* نحافظ على لون الحدود الحالي */
        }

        .invoice-header,
        .invoice-summary,
        .invoice-info,
        .total-words {
            color: #000 !important;
        }
    }
    /* أيقونة القائمة في الموبايل */
    .btn[data-bs-toggle="offcanvas"] i {
        color: #fff; /* افتراضي أبيض عند الداكن */
    }

    /* لو تستخدم dark-mode body */
    body.dark-mode .btn[data-bs-toggle="offcanvas"] i {
        color: #fff !important; /* اجبار اللون الأبيض في الداكن */
    }












    /* ===================== */
    /*      LIGHT MODE       */
    /* ===================== */

    aside {
      background: #ffffff;
      border-right: 1px solid #e6e6e6 !important;
      padding-top: 15px;
      min-height: 100vh;
    }

    /* العنوان */
    aside .text-muted.small {
      color: #8a8a8a !important;
      font-size: 0.85rem;
      padding-left: 10px;
      font-weight: 600;
    }

    /* روابط السايد بار */
    .sidebar-link {
      padding: 10px 14px;
      font-size: 0.95rem;
      color: #333;
      border-radius: 8px;
      display: flex;
      align-items: center;
      gap: 8px;
      transition: 0.25s ease;
      border: 1px solid transparent;
    }

    .sidebar-link i {
      color: #ff8a1f;
      font-size: 1.1rem;
      transition: 0.25s ease;
    }

    /* Hover Light */
    .sidebar-link:hover {
      background: rgba(255, 138, 31, 0.08);
      border-color: rgba(255, 138, 31, 0.25);
      transform: translateX(-4px);
    }

    .sidebar-link:hover i {
      color: #ff993d;
    }

    /* Active */
    .sidebar-link.active {
      background: rgba(255, 138, 31, 0.15);
      border-color: rgba(255, 138, 31, 0.3);
      font-weight: bold;
      color: #222 !important;
    }

    .sidebar-link.active i {
      color: #ff8a1f !important;
    }

    /* Divider */
    aside hr {
      border-color: #e3e3e3;
    }

    /* عنوان الإعدادات */
    aside h6 {
      color: #8a8a8a !important;
      margin-top: 10px;
      padding-left: 10px;
      font-size: 0.82rem;
      font-weight: 600;
    }

    /* Scrollbar */
    aside::-webkit-scrollbar {
      width: 6px;
    }

    aside::-webkit-scrollbar-thumb {
      background: #cfcfcf;
      border-radius: 4px;
    }
    /* ===================== */
    /*       DARK MODE       */
    /* ===================== */

    body.dark-mode aside {
      background: #0f0f0f;
      border-right: 1px solid #1f1f1f !important;
    }

    body.dark-mode .text-muted.small,
    body.dark-mode aside h6 {
      color: #888 !important;
    }

    body.dark-mode .sidebar-link {
      color: #e0e0e0;
      border-color: transparent;
    }

    body.dark-mode .sidebar-link i {
      color: #ff8d2c;
    }

    /* Hover */
    body.dark-mode .sidebar-link:hover {
      background: rgba(255, 140, 40, 0.08);
      border-color: rgba(255, 140, 40, 0.25);
    }

    body.dark-mode .sidebar-link:hover i {
      color: #ffa14a;
    }

    /* Active */
    body.dark-mode .sidebar-link.active {
      background: linear-gradient(90deg, #ff8a1f25, #ff8a1f10);
      border-color: #ff8d2c55;
      color: #fff !important;
    }

    body.dark-mode .sidebar-link.active i {
      color: #ff9e3c !important;
    }

    /* Scrollbar */
    body.dark-mode aside::-webkit-scrollbar-thumb {
      background: #333;
    }


    /* =======================================
      🌟 Odoo-like Enterprise Table Styling
      ======================================= */

    .odoo-table {
      width: 100%;
      border-collapse: separate;
      border-spacing: 0 10px;
      font-family: "Cairo", sans-serif;
    }

    /* ===== Header ===== */
    .odoo-table thead th {
      background: #f3f4f6;
      padding: 14px 10px;
      color: #374151;
      font-size: 14px;
      font-weight: 700;
      border-bottom: 1px solid #e5e7eb;
      text-align: center;
    }

    /* ===== Rows ===== */
    .odoo-table tbody tr {
      background: #ffffff;
      border-radius: 14px;
      box-shadow: 0 2px 8px rgba(0,0,0,0.04);
      transition: 0.15s ease;
    }

    .odoo-table tbody tr:hover {
      box-shadow: 0 4px 14px rgba(0,0,0,0.08);
    }

    /* ===== Cells ===== */
    .odoo-table tbody td {
      padding: 12px;
      vertical-align: middle;
    }

    .odoo-table input:not([type="checkbox"]),
    .odoo-table select {
      border: 1px solid #d1d5db !important;
      padding: 8px 10px;
      border-radius: 10px !important;
      font-size: 14px;
      transition: 0.2s;
      background: #fafafa;
    }

    .odoo-table input:focus,
    .odoo-table select:focus {
      background: #fff;
      border-color: #ff9e3c !important; /* لون أودو */
      box-shadow: 0 0 0 3px rgba(251, 146, 60, 0.35);
    }

    /* زر الإزالة */
    .remove-row {
      background: #ef4444 !important;
      border: none;
      padding: 6px 10px;
      border-radius: 8px;
      transition: 0.2s;
    }

    .remove-row:hover {
      background: #dc2626 !important;
    }

    /* زر إضافة صف */
    #addRow {
      background: #ff9e3c;
      color: #fff;
      border: none;
      margin-top: 10px;
      padding: 10px 18px;
      border-radius: 10px;
      font-weight: 600;
      transition: 0.2s;
    }

    #addRow:hover {
      background: #ff9e3c;
    }

    @media screen and (max-width: 768px) {
      #addMultipleUsers .modal-dialog {
        margin: 10px;
      }

      #addMultipleUsers .modal-body {
        padding: 10px;
      }

      /* جعل الجدول قابل للتمرير */
      #addMultipleUsers .table-responsive {
        overflow-x: auto !important;
        -webkit-overflow-scrolling: touch; /* تمرير سلس */
      }

      #addMultipleUsers table {
        min-width: 1100px; /* يجعل الجدول أعرض لتفعيل الاسكرول */
        font-size: 12px; /* تصغير النص قليلاً للموبايل */
      }

      #addMultipleUsers th,
      #addMultipleUsers td {
        white-space: nowrap;
        padding: 6px 8px;
        vertical-align: middle;
      }

      #addMultipleUsers .btn {
        font-size: 12px;
        padding: 5px 10px;
      }

      /* تحسين المسافات حول زر إضافة صف */
      #addMultipleUsers .text-end.mt-3 {
        text-align: center !important;
        margin-top: 15px !important;
      }
    }


    /* تحديث شكل select2 ليكون مثل باقي الحقول */
    .select2-container .select2-selection--single {
        height: 38px !important;
        padding: -5px 10px !important;
        border: 1px solid #d1d5db !important;
        border-radius: 10px !important;
        background: #fafafa !important;
        font-size: 14px !important;
    }

    /* النص داخل select2 */
    .select2-selection__rendered {
        line-height:38px !important;
        color: inherit !important;
    }

    /* السهم */
    .select2-selection__arrow {
       height: 38px !important;
    }

    .select2-container--default .select2-selection--single:focus,
    .select2-container--default .select2-selection--single.select2-selection--focus {
        background: #fff !important;
        border-color: #ff9e3c !important;
        box-shadow: 0 0 0 3px rgba(251, 146, 60, 0.35) !important;
        outline: none !important;
    }

    #addOrder .select2-container .select2-selection--single {
        height: 42px !important;
        padding: 0 10px !important;
        border: 1px solid #d1d5db !important;
        border-radius: 7px !important;
        background: #fafafa !important;
        font-size: 14px !important;
    }

    /* النص داخل select2 */
    #addOrder .select2-selection__rendered {
        line-height:42px !important;
        color: inherit !important;
    }

    /* السهم */
    #addOrder .select2-selection__arrow {
       height: 42px !important;
    }

    #addOrder .select2-container--default .select2-selection--single:focus,
    #addOrder .select2-container--default .select2-selection--single.select2-selection--focus {
        background: #fff !important;
        border-color: #ff9e3c !important;
        box-shadow: 0 0 0 3px rgba(251, 146, 60, 0.35) !important;
        outline: none !important;
    }

    /* ===== Dark Mode للـ Select2 داخل مودال #addOrder ===== */
    body.dark-mode #addOrder .select2-container .select2-selection--single {
        background: #2c2c2c !important;       /* خلفية داكنة */
        border: 1px solid #555 !important;    /* حدود أغمق */
        color: #eee !important;               /* نص فاتح */
    }

    /* النص داخل Select2 */
    body.dark-mode #addOrder .select2-selection__rendered {
        color: #eee !important;               /* نص فاتح للوضوح */
    }

    /* السهم */
    body.dark-mode #addOrder .select2-selection__arrow {
        color: #eee !important;
    }

    /* عند التركيز */
    body.dark-mode #addOrder .select2-container--default .select2-selection--single:focus,
    body.dark-mode #addOrder .select2-container--default .select2-selection--single.select2-selection--focus {
        background: #3a3a3a !important;       /* خلفية أغمق عند التركيز */
        border-color: #ff9e3c !important;
        box-shadow: 0 0 0 3px rgba(251,146,60,0.35) !important;
    }

    /* ===== Dark Mode شامل للأزرار واللينكات ===== */
    /* ===== Dark Mode شامل للأزرار واللينكات ===== */
    body.dark-mode {
        --btn-bg-dark: #2c2c2c;   /* خلفية الداكنة */
        --btn-hover-dark: #3a3a3a; /* عند hover */
        --btn-text-dark: #fff;     /* نص وأيقونات */
    }

    /* كل الأزرار العادية */
    body.dark-mode button:not(.btn-close),
    body.dark-mode .btn:not(.btn-close) {
        background-color: var(--btn-bg-dark) !important;
        color: var(--btn-text-dark) !important;
        border-color: var(--btn-bg-dark) !important;
        transition: background 0.2s ease;
    }

    body.dark-mode button:not(.btn-close):hover,
    body.dark-mode .btn:not(.btn-close):hover {
        background-color: var(--btn-hover-dark) !important;
        color: var(--btn-text-dark) !important;
    }

    body.dark-mode button:not(.btn-close):focus,
    body.dark-mode .btn:not(.btn-close):focus {
        background-color: var(--btn-hover-dark) !important;
        color: var(--btn-text-dark) !important;
        outline: none;
        box-shadow: 0 0 0 3px rgba(255, 158, 60, 0.35);
    }

    /* كل اللينكات */
    body.dark-mode a,
    body.dark-mode a.btn {
        color: var(--btn-text-dark) !important;
        text-decoration: none;
    }

    body.dark-mode a:hover {
        color: #ff9e3c !important; /* لون برتقالي عند hover */
    }

    /* الأيقونات داخل الأزرار واللينكات */
    body.dark-mode button i,
    body.dark-mode .btn i,
    body.dark-mode a i {
        color: var(--btn-text-dark) !important;
    }

    body.dark-mode .border-end {
        border-left: 1px solid #3a3a3a !important;
    }

    body.dark-mode .filter-form {
        border: 1px solid #3a3a3a !important;
    }

    /* إزالة الشادو البرتقالي من زر تبديل المود في Dark Mode */
    body.dark-mode #toggleDarkDesktop,
    body.dark-mode #toggleDarkDesktop:focus,
    body.dark-mode #toggleDarkDesktop:hover,
    body.dark-mode #toggleDarkMobile,
    body.dark-mode #toggleDarkMobile:focus,
    body.dark-mode #toggleDarkMobile:hover {
        box-shadow: none !important;
    }

    body.dark-mode #logoutBtn .btn.btn-logout{
        box-shadow: none !important;
    }

    body.dark-mode #logoutBtn{
        box-shadow: none !important;
    }

    body.dark-mode .users_text{
        color: #FFF !important;
    }

    .filter-form button.btn-warning {
        background-color: #ff9e3c !important;   /* خلفية داكنة */
        color: #fff !important;                 /* نص وأيقونات بيضاء */
        border: 1px solid #ff9e3c !important;      /* بوردر داكن بسيط */
    }

    .filter-form button.btn-warning:hover {
        background-color: #ff9e3c !important;   /* عند الـ hover */
        color: #fff !important;
    }

    body.dark-mode .filter-form button.btn-warning {
        background-color: #2c2c2c !important;   /* خلفية داكنة */
        color: #fff !important;                 /* نص وأيقونات بيضاء */
        border: 1px solid #444 !important;      /* بوردر داكن بسيط */
    }

    body.dark-mode .filter-form button.btn-warning:hover {
        background-color: #3a3a3a !important;   /* عند الـ hover */
        color: #fff !important;
    }

    /* === Dark Mode للـ Hero Card === */
    body.dark-mode .hero-card {
        background: #1e1e1e !important;    /* خلفية داكنة جذابة */
        color: #fff !important;            /* نص أبيض */
        border: 1px solid #333 !important; /* حدّ بسيط داكن */
    }

    /* عنوان البطاقة */
    body.dark-mode .hero-card .hero-title {
        color: #fff !important;
    }

    /* النص تحت العنوان */
    body.dark-mode .hero-card .hero-sub {
        color: #ddd !important;  /* درجة رمادي فاتح ناعمة */
    }

    /* اللوجو لو عايزه أفتح في الدارك مود */
    body.dark-mode .hero-card .floating-logo img {
        filter: brightness(1.3); /* اختياري – يجعل الشعار أوضح */
    }

    /* ===== Dark Mode - Modal Header ===== */
    body.dark-mode .modal-header {
        background: #1f1f1f !important;   /* خلفية داكنة */
        color: #fff !important;           /* نص أبيض */
        border-bottom: 1px solid #333 !important;
    }

    /* عنوان المودال */
    body.dark-mode .modal-header .modal-title {
        color: #fff !important;
    }

    /* زر الإغلاق */
    body.dark-mode .modal-header .btn-close {
        filter: invert(1) brightness(2) !important;  /* أيقونة بيضاء */
    }
    /* ===== Dark Mode Scrollbar Styling ===== */
    body.dark-mode ::-webkit-scrollbar {
        width: 8px;
    }

    body.dark-mode ::-webkit-scrollbar-track {
        background: #1f1f1f;   /* خلفية الداكن */
    }

    body.dark-mode ::-webkit-scrollbar-thumb {
        background: #444;       /* جزء التحريك */
        border-radius: 10px;
    }

    body.dark-mode ::-webkit-scrollbar-thumb:hover {
        background: #555;
    }
    /* 🎨 أيقونة الكالندر بيضاء في الدارك مود */
    body.dark-mode input[type="date"]::-webkit-calendar-picker-indicator {
      filter: brightness(0) invert(1); /* يجعل اللون أبيض */
      opacity: 1;
    }
    /* ======  Number Input Arrows (Dark Mode)  ====== */

    /* الخلفية الداكنة لحقل الرقم */
    body.dark-mode input[type="number"] {
      background: #555;
      color: #fff;
      border-color: #444;
    }

    /* تأثير على الأسهم */
    body.dark-mode input[type="number"]::-webkit-inner-spin-button,
    body.dark-mode input[type="number"]::-webkit-outer-spin-button {
      background: #555;      /* لون خلفية الأسهم */
      border-radius: 4px;
      filter: invert(1);     /* يخلي لون الأسهم أبيض */
    }
    /* زر إغلاق المودال — لون أبيض في الدارك مود */
    /* زر إغلاق المودال — لون أبيض في الدارك مود */
    body.dark-mode .btn-close {
        filter: invert(1) brightness(200%); /* يجعل الأيقونة بيضاء تماماً */
    }
    body.dark-mode .floating-logo {
      background: #222 !important;
      border-color: #555 !important;
    }
    /* ========================================================= */
    /* 🌙🔥 Ultra Premium Dark Charts Theme */
    /* ========================================================= */

    body.dark-mode {
      background: #111 !important;
      color: #eee;
    }

    /* ===== كروت الشارتات ===== */
    body.dark-mode .chart-card {
      background: #181818 !important;
      border-radius: 28px;
      border: 1px solid #222;
      box-shadow: 0 0 35px rgba(0,0,0,0.55);
      transition: .3s;
    }

    body.dark-mode .chart-card:hover {
      box-shadow: 0 0 45px rgba(255,106,0,0.20);
    }

    body.dark-mode .chart-card h5 {
      color: #fff;
    }

    /* ===== ألوان الأعمدة الاحترافية الداكنة ===== */
    :root {
      --orange-dark: rgba(255,110,10,0.85);
      --blue-dark: rgba(0,123,255,0.85);
      --green-dark: rgba(40,167,69,0.85);
      --gray-dark: rgba(140,140,150,0.85);

      /* درجات فخمة للـ hover */
      --orange-dark-hover: rgba(255,120,20,1);
      --blue-dark-hover: rgba(20,140,255,1);
      --green-dark-hover: rgba(50,190,90,1);
      --gray-dark-hover: rgba(160,160,170,1);
    }

    /* ===== تنعيم الشارت نفسه ===== */
    body.dark-mode canvas {
      background: transparent !important;
    }

    /* ===== Dropdown Button & Arrow ===== */
    .role-badge_drop {
        transition: background-color 0.3s, color 0.3s;
        display: flex;
        align-items: center;
        justify-content: space-between;
        width: 180px;
    }

    /* تحريك السهم */
    .dropdown-toggle::after {
        display: none; /* نخفي السهم الافتراضي */
    }

    #roleArrow {
        transition: transform 0.3s;
    }

    /* إذا dropdown مفتوح */
    .dropdown.show #roleArrow {
        transform: rotate(180deg); /* السهم للأعلى */
    }

    /* Active role */
    .dropdown-item.active-role {
        background-color: orange !important;
        color: #fff !important;
    }

    /* نصوص الأدوار العادية */
    .dropdown-item {
        color: #111;
    }

    /* Dark mode */
    body.dark-mode .dropdown-menu {
        background-color: #333;
    }

    body.dark-mode .dropdown-item {
        color: #fff;
    }

    body.dark-mode .dropdown-item.active-role {
        background-color: orange;
        color: #fff;
    }

    /* Soft open/close */
   .dropdown-menu {
        transition: all 0.3s ease;
    }
  .role-badge_drop, #roleDropdown {
      background: #ffffff;
      border: 1px solid #e5e7eb;
      color: #555;
      font-weight: 600;
      border-radius: 20px;
      padding: 8px 14px;
      transition: .2s;
  }

  #roleDropdown:hover {
      background: #f8f9fa;
      border-color: #d0d7de;
  }

  .dropdown-menu {
      border-radius: 14px;
      padding: 8px;
      border: 1px solid #e6e6e6 !important;
      box-shadow: 0px 10px 30px rgba(0,0,0,0.08);
  }

  .dropdown-menu .dropdown-item {
      border-radius: 10px;
      font-weight: 500;
      padding: 8px 14px;
      transition: .15s;
  }

  .dropdown-menu .dropdown-item:hover {
      background: #f3f4f6;
  }

  .active-role {
      background: #2563eb !important;
      color: white !important;
  }

  .loading-btn {
      opacity: 0.6;
      pointer-events: none;
  }
  #roleArrow {
      transition: transform 0.25s ease;
  }

  #roleDropdown.show #roleArrow {
      transform: rotate(180deg);
  }

  #roleArrow11 {
      transition: transform 0.25s ease;
  }

  #roleDropdown.show #roleArrow11 {
      transform: rotate(180deg);
  }

  #roleArrow33 {
      transition: transform 0.25s ease;
  }

  /* لما الزرار يبقى مفتوح */
  button[aria-expanded="true"] #roleArrow33 {
      transform: rotate(180deg);
  }

  /* ======== زر الدروب ======== */
  .role-badge_drop {
      display: flex;
      align-items: center;
      padding: 6px 12px;
      font-size: 14px;
      font-weight: 500;
      color: #333;
      border: 1px solid #ccc;
      border-radius: 8px;
      cursor: pointer;
      transition: all 0.2s;
      box-shadow:none !important
  }

  .role-badge_drop:hover {
      background: rgba(0,0,0,0.05);
  }

  /* السهم المثلث الممتلئ */
  .role-badge_drop .bi-caret-down-fill {
      font-size: 0.75rem;
      vertical-align: middle;
  }

  /* ======== قائمة الدروب ======== */
  .role-dropdown {
      min-width: 180px;
      border: 1px solid #ccc;
      border-radius: 8px;
      padding: 4px 5px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.1);
  }

  /* كل عنصر */
  .role-dropdown .dropdown-item {
      display: flex;
      align-items: center;
      padding: 6px 12px;
      font-size: 14px;
      color: #333;
      margin-bottom: 3px;
      transition: background 0.2s;
  }

  .role-dropdown .dropdown-item i {
      font-size: 16px;
      color: #555;
  }

  .role-dropdown .dropdown-item:hover,
  .role-dropdown .dropdown-item.active-role {
      background: rgba(0,0,0,0.08);
      color: #000;
  }

  /* إزالة الخلفية الافتراضية للـ dropdown */
  .dropdown-menu.show {
      border: 1px solid #ccc;
  }
  /* لون الأيقونة في العنصر النشط */
  .role-dropdown .dropdown-item.active-role i {
      color: #fff; /* أبيض */
  }

  .sidebar-dropdown .sidebar-link {
      position: relative;
  }

  .sidebar-dropdown .dropdown-icon {
      font-size: 0.7rem;
      transition: transform 0.3s ease;
      display: inline-block;
  }

  /* عندما يكون الـ collapse مفتوح، السهم يتحرك لأسفل */
  .sidebar-dropdown a[aria-expanded="true"] .dropdown-icon {
      transform: rotate(180deg); /* يتحرك من لأعلى ▲ إلى لأسفل ▼ */
  }

  /* لضمان السهم في منتصف ارتفاع الرابط */
  .sidebar-dropdown .sidebar-link span.dropdown-icon {
      display: flex;
      align-items: center;
  }

  #roleArrow11 {
    transition: transform 0.25s ease;
  }

  /* لما الأكورديون مفتوح */
  a[aria-expanded="true"] #roleArrow11 {
    transform: rotate(180deg);
  }

  /* لما الصفحة active (في حالتك in_array(...) ) */
  a.active #roleArrow11 {
    transform: rotate(180deg);
  }

  /* ---------- Sidebar Container ---------- */
  .modern-sidebar {
    background: var(--sidebar-bg);
    border-left: 1px solid var(--border);
    padding: 20px 15px;
    transition: 0.3s ease-in-out;
  }

  /* ---------- Colors (Light / Dark Auto) ---------- */
  :root {
    --sidebar-bg: #ffffff;
    --text: #444;
    --text-muted: #888;
    --primary: #F28C28;   /* برتقالي رايق */
    --primary-hover: #d97417;
    --border: #e6e6e6;
    --active-bg: rgba(242,140,40,0.12);
  }

  .dark-mode {
    --sidebar-bg: #1c1c1c;
    --text: #ddd;
    --text-muted: #999;
    --border: #333;
    --active-bg: rgba(242,140,40,0.18);
  }

  /* ---------- Title ---------- */
  .sidebar-title {
    font-size: 13px;
    color: var(--text-muted);
    margin-bottom: 12px;
  }

  /* ---------- Links ---------- */
  .sidebar-link {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 10px 12px;
    margin-bottom: 6px;
    border-radius: 10px;
    font-size: 15px;
    color: var(--text);
    text-decoration: none;
    transition: 0.25s;
  }

  .sidebar-link i {
    font-size: 18px;
    color: var(--primary);
  }

  /* Hover */
  .sidebar-link:hover {
    background: var(--active-bg);
    transform: translateX(-4px);
  }

  /* Active Smooth */
  .sidebar-link.active {
    background: var(--primary);
    color: #fff !important;
    box-shadow: 0 4px 10px rgba(242,140,40,0.35);
  }

  .sidebar-link.active i {
    color: #fff !important;
  }

  /* ---------- Dropdown ---------- */
  .sidebar-dropdown .sidebar-link {
    justify-content: space-between;
  }

  .submenu .sidebar-link {
    padding-right: 25px;
    font-size: 14px;
  }

  /* Smooth Collapse */
  .collapse {
    transition: 0.3s ease;
  }
  .modern-sidebar {
    border-right: 1px solid var(--border) !important;
    height:auto !important
  }



  /* ---------------- DARK MODE ---------------- */
  body.dark-mode {
    --sidebar-bg: #141414;          /* خلفية sidebar أغمق وأشيك */
    --text: #e5e5e5;                /* النص فاتح */
    --text-muted: #9d9d9d;          /* الرمادي */
    --border: #222;                 /* حدود خفيفة جداً */
    --active-bg: rgba(242,140,40,0.22); /* خلفية الـ hover / submenu */
    --primary: #F28C28;             /* البرتقالي الرئيسي */
    --primary-hover: #ff9f41;       /* برتقالي أفتح بالمناسبة */
  }

  /* ---------- Sidebar Container ---------- */
  body.dark-mode .modern-sidebar {
    background: var(--sidebar-bg);
    border-right: 1px solid var(--border);
  }

  /* ---------- Links ---------- */
  body.dark-mode .sidebar-link {
    color: var(--text);
  }

  body.dark-mode .sidebar-link i {
    color: var(--primary);
  }

  /* hover */
  body.dark-mode .sidebar-link:hover {
    background: var(--active-bg);
    transform: translateX(-4px);
  }

  /* active */
  body.dark-mode .sidebar-link.active {
    background: var(--primary);
    color: #FFF !important;
    box-shadow:none !important;
    border:none !important
  }

  body.dark-mode .sidebar-link.active i {
    color: #FFF !important;
  }

  /* --------- Dropdown arrow ---------- */
  body.dark-mode #roleArrow11 {
    color: var(--primary);
  }

  /* --------- collapse background --------- */
  body.dark-mode .submenu .sidebar-link {
    background: transparent !important;
  }
  body.dark-mode .submenu .sidebar-link.active {
    background: var(--primary);
    color: #FFF !important;
    box-shadow:none !important;
    border:none !important
  }

  /* روابط submenu (الموجودة داخل collapse) */
  .submenu .sidebar-link {
    padding-right: 35px !important;   /* زيادة مسافة ناحية اليمين */
    font-size: 14px;
    opacity: 0.9;
  }

  .submenu .sidebar-link i {
    font-size: 16px;
  }
  /* Active داخل submenu */
  .submenu .sidebar-link.active {
    background: rgba(242,140,40,0.25) !important;
    color: var(--primary) !important;
    box-shadow: none !important;
    transform: none !important;
  }

  .submenu .sidebar-link.active i {
    color: var(--primary) !important;
  }



.mobile-sidebar {
  width: 290px !important;
  background: var(--bs-body-bg);
  border: none;
}

.mobile-sidebar-header {
  padding: 14px 14px;
  border-bottom: 1px solid #eee;
  background: var(--bs-body-bg);
}

.brand-logo {
  height: 40px;
  margin-left: 10px;
}

.brand-text small {
  font-size: 11px;
}

.mobile-sidebar-body {
  padding: 15px 10px;
}

.menu-group-title {
  font-size: 13px;
  margin-bottom: 5px;
  padding-right: 5px;
  color: #888;
}

.mobile-item {
  display: flex;
  align-items: center;
  padding: 10px 12px;
  border-radius: 10px;
  font-size: 15px;
  transition: 0.25s ease;
  color: #333;
  text-decoration:none;
  margin-bottom:5px
}

.mobile-item i {
  margin-left: 10px;
  font-size: 18px;
  color: #ff8c00;
}

.mobile-item:hover {
  background: #fff4e5;
}

.mobile-item.active {
  background: #ff8c00;
  color: white;
}

.mobile-item.active i {
  color: white;
}

.menu-divider {
  border-bottom: 1px solid #eee;
  margin: 12px 0;
}


/* أزرار الدور */
.role-btn {
  background: #f7f7f7;
  border-radius: 10px;
}

.role-dropdown .dropdown-item.active-role {
  background: #ff8c00;
  color: white;
}


/* زر تسجيل الخروج */
.logout-btn {
  background: #ffefdb;
  color: #d95329;
  border-radius: 12px;
  padding: 10px;
}

.logout-btn i {
  font-size: 18px;
}


body.dark-mode .mobile-sidebar,
body.dark-mode .mobile-sidebar-header {
  background: #1f1f1f;
  border-color: #333;
}

body.dark-mode .mobile-item {
  color: #ddd;
}

body.dark-mode .mobile-item:hover {
  background: rgba(255, 140, 0, 0.15);
}

body.dark-mode .menu-group-title {
  color: #aaa;
}

body.dark-mode .menu-divider {
  border-color: #333;
}

body.dark-mode .role-btn {
  background: #2a2a2a;
  color: #ddd;
}

body.dark-mode .logout-btn {
  background: #422212;
  color: #ff9b4a;
}
dotlottie-wc {
    filter: hue-rotate(-20deg) saturate(3) brightness(1.1);
}

/* صندوق الترحيب */
.welcome-box {
    position: absolute;
    left: 60%;
    transform: translateX(-50%);
    animation: fadeSlide 1s ease forwards;
    opacity: 0;
    pointer-events: none;
}

/* نص الترحيب */
.welcome-text {
    font-size: 0.95rem;
    font-weight: 600;
    color: #2a2a2a;
    padding: 4px 14px;
    border-radius: 12px;
    background: rgba(255,255,255,0.2);
    backdrop-filter: blur(5px);
    box-shadow: 0 0 12px rgba(0,0,0,0.08);
}

/* وضع الداكن */
.dark-mode .welcome-text {
    color: #ff8c00;
    background: rgba(255, 140, 0, 0.08);
    box-shadow: 0 0 15px rgba(255,140,0,0.18);
}

/* الأنيميشن */
@keyframes fadeSlide {
    0% { opacity: 0; transform: translate(-50%, -5px); }
    100% { opacity: 1; transform: translate(-50%, 0); }
}

    </style>
  <script src="https://unpkg.com/@lottiefiles/dotlottie-wc@0.8.5/dist/dotlottie-wc.js" type="module"></script>
  <link href="https://fonts.googleapis.com/css2?family=Scheherazade+New:wght@700&display=swap" rel="stylesheet">
</head>
<body>
  <div class="position-fixed top-0 end-0 p-3" style="z-index: 1080;">
    <div id="roleToast" class="toast align-items-center text-white bg-primary border-0" role="alert" aria-live="assertive" aria-atomic="true">
      <div class="d-flex">
        <div class="toast-body" id="roleToastBody">
          <!-- الرسالة ستوضع هنا -->
        </div>
        <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast" aria-label="Close"></button>
      </div>
    </div>
  </div>
  <div class="loader">
    <!--<div class="circle">
      <div class="loader-text">دوار السعادة</div>

      <div class="pulse-dot"></div>
      <div class="pulse-dot"></div>
      <div class="pulse-dot"></div>
      <div class="pulse-dot"></div>
      <div class="pulse-dot"></div>
      <div class="pulse-dot"></div>
    </div>-->
    <dotlottie-wc src="https://lottie.host/0e7469d2-315d-4405-a70f-e47552619be8/5l1Eex354m.lottie" style="width: 300px;height: 300px" autoplay loop></dotlottie-wc>
  </div>
<div id="page-wrapper" style="opacity:0; transition:opacity .8s ease;">
<nav class="navbar navbar-expand-lg sticky-top custom-navbar">
  <div class="container-fluid d-flex justify-content-between align-items-center">

    <!-- ✅ اللوجو على اليمين -->
    <a class="navbar-brand d-flex align-items-center" href="<?= BASE_URL ?>/home.php" style="text-decoration:none;">
      <img src="<?= BASE_URL ?><?= esc(getSystemSettings('main_logo') ?: '/assets/logo_header2.png') ?>" alt="Logo" class="logo-modern">
    </a>

    <!--<div class="welcome-box d-none d-md-block mx-auto text-center">
      <span class="welcome-text">
        مرحبًا <strong><?= esc(current_user()) ?></strong> — نتمنى لك يومًا سعيدًا داخل نظام دوّار السعادة
      </span>
    </div>-->

    <!-- ✅ زر القائمة على الشمال (يظهر فقط في الموبايل) -->
    <!-- مجموعة أزرار الموبايل (القائمة + الداكن) -->
    <div class="mobile-buttons d-md-none position-fixed top-2 d-flex gap-2" style="left:10px; z-index:1050;">
      <?php if(has_permission('settings.light_and_dark_mode')): ?>
      <!-- زر الداكن -->
        <button id="toggleDarkMobile" class="btn btn-orange position-relative overflow-hidden rounded-circle shadow p-2"
                style="width:45px; height:45px; display:flex; align-items:center; justify-content:center; font-size:1.2rem;">
          <i class="bi bi-moon" id="toggleIconMobile"></i>
          <span class="bubble bubble1"></span>
          <span class="bubble bubble2"></span>
          <span class="bubble bubble3"></span>
        </button>
      <?php endif ?>
      <!-- زر القائمة -->
      <button class="btn btn-orange rounded-circle shadow p-2"
              style="width:45px; height:45px;"
              data-bs-toggle="offcanvas" data-bs-target="#sidebarMenu" aria-label="القائمة">
        <i class="bi bi-list fs-4 text-white"></i>
      </button>

    </div>

    <!-- ✅ العناصر الثابتة في اليمين الكبير -->
    <ul class="navbar-nav ms-auto align-items-lg-center gap-3 d-none d-md-flex">

      <!-- الدور -->
      <li class="nav-item">
        <?php if(current_user_id_seq() === 'Ad0001'): ?>
            <div class="dropdown">
                <button class="btn role-badge_drop dropdown-toggle" type="button" id="roleDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="bi bi-person-badge me-1"></i>
                    <span id="currentRoleText"><?= esc(current_role()) ?></span>
                    <i class="bi bi-caret-down-fill ms-1" id="roleArrow"></i>
                </button>
                <ul class="dropdown-menu role-dropdown" aria-labelledby="roleDropdown">
                    <?php
                    $stmt = $pdo->query("SELECT id, name FROM roles ORDER BY id ASC");
                    $roles = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($roles as $role):
                        $active = $role['name'] === current_role() ? 'active-role' : '';
                    ?>
                    <li>
                        <a class="dropdown-item <?= $active ?>" href="#" onclick="switchRole(<?= $role['id'] ?>, '<?= $role['name'] ?>'); return false;">
                            <i class="bi bi-person-circle me-2"></i> <?= esc($role['name']) ?>
                        </a>
                    </li>
                    <?php endforeach; ?>
                </ul>
            </div>
            <?php else: ?>
              <span class="badge role-badge">
                <i class="bi bi-person-badge me-1"></i> <?= esc(current_role()) ?>
              </span>
            <?php endif; ?>
      </li>

      <!-- المستخدمون -->
      <?php if(has_permission('users.view')): ?>
      <li class="nav-item">
        <a class="users_text nav-link <?= $current_page=='users.php'?'active':'' ?>" href="<?= BASE_URL ?>/users.php">
          <i class="bi bi-people me-1"></i> المستخدمون
        </a>
      </li>
      <?php endif ?>
      <?php if(has_permission('settings.light_and_dark_mode')): ?>
      <li>
        <button id="toggleDarkDesktop" class="btn btn-orange position-relative overflow-hidden" 
                style="width:50px !important; height:50px !important; border-radius:50% !important; display:flex; align-items:center; justify-content:center; font-size:1.2rem;">
          <i class="bi bi-moon" id="toggleIconDesktop"></i>
          <span class="bubble bubble1"></span>
          <span class="bubble bubble2"></span>
          <span class="bubble bubble3"></span>
        </button>
      </li>
      <?php endif ?>
      <!--<li>
        <a href="logout.php" class="btn btn-orange" id="logoutBtn" style="width:50px; height:50px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-size:1.2rem;">
          <i class="bi bi-box-arrow-right"></i>
        </a>
      </li>-->

      <!-- خروج -->
      <li class="nav-item" id="logoutBtn">
        <a class="btn btn-logout" href="<?= BASE_URL ?>/logout.php">
          <i class="bi bi-box-arrow-right me-1"></i> خروج
        </a>
      </li>

    </ul>
  </div>
</nav>
</div>

<!-- القائمة الجانبية في الموبايل (Offcanvas) -->
<div class="offcanvas offcanvas-start mobile-sidebar" tabindex="-1" id="sidebarMenu">
  <div class="offcanvas-header mobile-sidebar-header">

    <div class="brand-box d-flex align-items-center w-100">
      <img src="<?= BASE_URL ?><?= esc(getSystemSettings('main_logo') ?: '/assets/logo_header2.png') ?>"
          class="brand-logo">

      <button type="button" class="btn-close ms-auto" data-bs-dismiss="offcanvas"></button>
    </div>
  </div>

  <div class="offcanvas-body mobile-sidebar-body">

    <div class="menu-group-title">القائمة الرئيسية</div>

    <a class="mobile-item <?= $current_page=='home.php'?'active':'' ?>" href="<?= BASE_URL ?>/home.php">
      <i class="bi bi-house"></i> الرئيسية
    </a>

    <?php if(has_permission('purchases.view')): ?>
    <a class="mobile-item <?= $current_page=='purchases.php'?'active':'' ?>" href="<?= BASE_URL ?>/purchases.php">
      <i class="bi bi-bag"></i> تهيئة المشتريات
    </a>
    <?php endif ?>

    <?php if(has_permission('orders.view')): ?>
    <a class="mobile-item <?= $current_page=='orders.php'?'active':'' ?>" href="<?= BASE_URL ?>/orders.php">
      <i class="bi bi-gear"></i> أوامر التشغيل
    </a>
    <?php endif ?>

    <?php if(has_permission('custodies.view')): ?>
    <a class="mobile-item <?= $current_page=='custodies.php'?'active':'' ?>" href="<?= BASE_URL ?>/custodies.php">
      <i class="bi bi-wallet2"></i> العهد
    </a>
    <?php endif ?>

    <?php if(has_permission('assets.view')): ?>
    <a class="mobile-item <?= $current_page=='assetes.php'?'active':'' ?>" href="<?= BASE_URL ?>/assetes.php">
      <i class="bi bi-building"></i> الأصول
    </a>
    <?php endif ?>

    <?php if(has_permission('expenses.view')): ?>
    <a class="mobile-item <?= $current_page=='expenses.php'?'active':'' ?>" href="<?= BASE_URL ?>/expenses.php">
      <i class="bi bi-cash-stack"></i> المصروفات
    </a>
    <?php endif ?>

    <?php if(has_permission('branches.view')): ?>
    <a class="mobile-item <?= $current_page=='branches.php'?'active':'' ?>" href="<?= BASE_URL ?>/branches.php">
      <i class="bi bi-diagram-3"></i> الفروع
    </a>
    <?php endif ?>

    <?php if(has_permission('reports.view')): ?>
    <a class="mobile-item <?= $current_page=='reports.php'?'active':'' ?>" href="<?= BASE_URL ?>/reports.php">
      <i class="bi bi-graph-up"></i> التقارير
    </a>
    <?php endif ?>

    <?php if(has_permission('logs.view')): ?>
    <a class="mobile-item <?= $current_page=='activity_logs.php'?'active':'' ?>" href="<?= BASE_URL ?>/activity_logs.php">
      <i class="bi bi-file-earmark-text"></i> العمليات والتحديثات
    </a>
    <?php endif ?>


    <!-- الإعدادات -->
    <div class="menu-divider"></div>
    <div class="menu-group-title mt-2">الإعدادات</div>

    <?php if(has_permission('roles.view')): ?>
    <a class="mobile-item <?= $current_page=='roles.php'?'active':'' ?>" href="<?= BASE_URL ?>/roles.php">
      <i class="bi bi-shield-lock"></i> الأدوار
    </a>
    <?php endif ?>

    <?php if(has_permission('permissions.view')): ?>
    <a class="mobile-item <?= $current_page=='permissions.php'?'active':'' ?>" href="<?= BASE_URL ?>/permissions.php">
      <i class="bi bi-person-check"></i> الصلاحيات
    </a>
    <?php endif ?>

    <?php if(has_permission('systems_settings.edit')): ?>
    <a class="mobile-item <?= $current_page=='settings.php'?'active':'' ?>" href="<?= BASE_URL ?>/settings.php">
      <i class="bi bi-gear-fill"></i> إعدادات النظام
    </a>
    <?php endif ?>


    <!-- معلومات المستخدم -->
    <div class="menu-divider"></div>
    <div class="menu-group-title mt-2">حساب المستخدم</div>

    <div class="px-2 mt-2">
      <?php if(current_user_id_seq() === 'Ad0001'): ?>
      <div class="dropdown w-100">
        <button class="btn role-btn dropdown-toggle w-100" data-bs-toggle="dropdown">
          <i class="bi bi-person-badge me-2"></i>
          <span id="currentRoleText"><?= esc(current_role()) ?></span>
        </button>

        <ul class="dropdown-menu role-dropdown w-100">
          <?php foreach ($roles as $role): ?>
          <li>
            <a class="dropdown-item <?= $role['name'] === current_role() ? 'active-role' : '' ?>"
              href="#" onclick="switchRole(<?= $role['id'] ?>,'<?= $role['name'] ?>'); return false;">
              <i class="bi bi-person-circle me-2"></i> <?= esc($role['name']) ?>
            </a>
          </li>
          <?php endforeach; ?>
        </ul>
      </div>
      <?php else: ?>
      <span class="badge role-badge w-100 py-2">
        <i class="bi bi-person-badge me-2"></i> <?= esc(current_role()) ?>
      </span>
      <?php endif; ?>
    </div>

    <!-- تسجيل الخروج -->
    <a class="btn logout-btn w-100 mt-3" href="<?= BASE_URL ?>/logout.php">
      <i class="bi bi-box-arrow-right"></i> خروج
    </a>

  </div>
</div>



<div class="container-fluid">
  <div class="row">
    <!-- Sidebar في الديسكتوب -->
    <aside class="modern-sidebar d-none d-md-block col-md-2 col-lg-2">
      
      <div class="sidebar-title">القائمة</div>

      <a class="sidebar-link <?= $current_page=='home.php'?'active':'' ?>" href="<?= BASE_URL ?>/home.php">
        <i class="bi bi-house"></i> الرئيسية
      </a>

      <?php if(has_permission('purchases.view')): ?>
      <a class="sidebar-link <?= $current_page=='purchases.php'?'active':'' ?>" href="<?= BASE_URL ?>/purchases.php">
        <i class="bi bi-bag"></i> تهيئة المشتريات
      </a>
      <?php endif ?>

      <?php if(has_permission('orders.view')): ?>
      <a class="sidebar-link <?= $current_page=='orders.php'?'active':'' ?>" href="<?= BASE_URL ?>/orders.php">
        <i class="bi bi-gear"></i> أوامر التشغيل
      </a>
      <?php endif ?>

      <?php if(has_permission('custodies.view')): ?>
      <a class="sidebar-link <?= $current_page=='custodies.php'?'active':'' ?>" href="<?= BASE_URL ?>/custodies.php">
        <i class="bi bi-wallet2"></i> العهد
      </a>
      <?php endif ?>

      <?php if(has_permission('assets.view')): ?>
      <a class="sidebar-link <?= $current_page=='assetes.php'?'active':'' ?>" href="<?= BASE_URL ?>/assetes.php">
        <i class="bi bi-building"></i> الأصول
      </a>
      <?php endif ?>

      <?php if(has_permission('expenses.view')): ?>
      <a class="sidebar-link <?= $current_page=='expenses.php'?'active':'' ?>" href="<?= BASE_URL ?>/expenses.php">
        <i class="bi bi-cash-stack"></i> المصروفات
      </a>
      <?php endif ?>

      <?php if(has_permission('branches.view')): ?>
      <a class="sidebar-link <?= $current_page=='branches.php'?'active':'' ?>" href="<?= BASE_URL ?>/branches.php">
        <i class="bi bi-diagram-3"></i> الفروع
      </a>
      <?php endif ?>

      <?php if(has_permission('reports.view')): ?>
      <a class="sidebar-link <?= $current_page=='reports.php'?'active':'' ?>" href="<?= BASE_URL ?>/reports.php">
        <i class="bi bi-graph-up"></i> التقارير
      </a>
      <?php endif ?>

      <?php if(has_permission('logs.view')): ?>
      <a class="sidebar-link <?= $current_page=='activity_logs.php'?'active':'' ?>" href="<?= BASE_URL ?>/activity_logs.php">
        <i class="bi bi-file-earmark-text"></i> العمليات والتحديثات
      </a>
      <?php endif ?>

      <?php if(has_permission('systems_settings.view')): ?>
        <hr>

        <?php
          $sub = ['roles.php','permissions.php','settings.php'];
          $open = in_array($current_page,$sub)?'show':'';
        ?>

        <a class="sidebar-link justify-content-between <?= in_array($current_page,$sub)?'active':'' ?>" 
          data-bs-toggle="collapse" href="#sysDrop">
            
            <span><i class="bi bi-gear-fill"></i> إعدادات النظام</span>

            <i class="bi bi-chevron-down ms-auto" id="roleArrow11"></i>
        </a>

        <div class="collapse submenu <?= $open ?>" id="sysDrop">
          <?php if(has_permission('roles.view')): ?>
          <a class="sidebar-link <?= $current_page=='roles.php'?'active':'' ?>" href="<?= BASE_URL ?>/roles.php">
            <i class="bi bi-shield-lock"></i> الأدوار
          </a>
          <?php endif ?>

          <?php if(has_permission('permissions.view')): ?>
          <a class="sidebar-link <?= $current_page=='permissions.php'?'active':'' ?>" href="<?= BASE_URL ?>/permissions.php">
            <i class="bi bi-person-check"></i> الصلاحيات
          </a>
          <?php endif ?>

          <?php if(has_permission('systems_settings.edit')): ?>
          <a class="sidebar-link <?= $current_page=='settings.php'?'active':'' ?>" href="<?= BASE_URL ?>/settings.php">
            <i class="bi bi-gear"></i> الإعدادات العامة
          </a>
          <?php endif ?>
        </div>

      <?php endif ?>

    </aside>


    <!-- المحتوى -->
    <main class="col-12 col-md-10 col-lg-10 p-4">
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
// ================================
// 🟢 دالة تحديث ألوان الشارتات
// ================================
function getChartColors() {
    const isDark = document.body.classList.contains("dark-mode");
    return {
        chartTextColor: isDark ? "#ccc" : "#111",
        chartGridColor: isDark ? "rgba(255,255,255,0.07)" : "rgba(0,0,0,0.08)",
        chartTooltipBg: isDark ? "#000" : "#fff",
        chartTooltipText: isDark ? "#fff" : "#000"
    };
}

function updateChartsColors() {
    const { chartTextColor, chartGridColor, chartTooltipBg, chartTooltipText } = getChartColors();
    charts.forEach(chart => {
        // تحديث المحاور
        if(chart.options.scales) {
            if(chart.options.scales.x) { chart.options.scales.x.ticks.color = chartTextColor; chart.options.scales.x.grid.color = chartGridColor; }
            if(chart.options.scales.y) { chart.options.scales.y.ticks.color = chartTextColor; chart.options.scales.y.grid.color = chartGridColor; }
        }
        // تحديث الليجيند
        if(chart.options.plugins && chart.options.plugins.legend) {
            chart.options.plugins.legend.labels.color = chartTextColor;
        }
        // تحديث التولتيب
        if(chart.options.plugins && chart.options.plugins.tooltip) {
            chart.options.plugins.tooltip.backgroundColor = chartTooltipBg;
            chart.options.plugins.tooltip.titleColor = chartTooltipText;
            chart.options.plugins.tooltip.bodyColor  = chartTooltipText;
            chart.options.plugins.tooltip.borderColor= chartGridColor;
        }
        chart.update();
    });
}

// ================================
// 🟢 كود التبديل بين الداكن/الليت (موجود عندك)
// ================================
const toggleBtnMobile = document.getElementById('toggleDarkMobile');
const toggleIconMobile = document.getElementById('toggleIconMobile');

const toggleBtnDesktop = document.getElementById('toggleDarkDesktop');
const toggleIconDesktop = document.getElementById('toggleIconDesktop');

const logoutIcon = document.querySelector('#logoutBtn i');

function updateDarkModeIcons() {
    const dark = document.body.classList.contains('dark-mode');
    toggleIconMobile.className = dark ? 'bi bi-sun' : 'bi bi-moon';
    toggleIconDesktop.className = dark ? 'bi bi-sun' : 'bi bi-moon';
    toggleIconMobile.style.color = dark ? '#fff' : '';
    toggleIconDesktop.style.color = dark ? '#fff' : '';
    logoutIcon.style.color = dark ? '#fff' : '';
}

[toggleBtnMobile, toggleBtnDesktop].forEach(btn => {
    btn.onclick = function() {
        document.body.classList.toggle("dark-mode");
        localStorage.setItem("dark-mode", document.body.classList.contains("dark-mode") ? "on" : "off");
        updateDarkModeIcons();
        updateChartsColors(); // 🔥 حدث الشارتات فوراً
    }
});

if (localStorage.getItem("dark-mode") === "on") {
    document.body.classList.add("dark-mode");
}
updateDarkModeIcons();
updateChartsColors(); // 🔥 حدث الشارتات عند تحميل الصفحة
</script>
<script>
function showToast(message, isSuccess = true) {
    const toastEl = document.getElementById('roleToast');
    const toastBody = document.getElementById('roleToastBody');

    toastBody.innerText = message;
    toastEl.classList.remove('bg-success', 'bg-danger', 'bg-primary');
    toastEl.classList.add(isSuccess ? 'bg-success' : 'bg-danger');

    const toast = new bootstrap.Toast(toastEl);
    toast.show();
}

function switchRole(role_id, role_name) {

    const btn = document.getElementById('roleDropdown');
    btn.classList.add("loading-btn");

    fetch('switch_role.php', {
        method: 'POST',
        headers: { 
            'Content-Type': 'application/json',
            'Accept': 'application/json'
        },
        body: JSON.stringify({ role_id, role_name })
    })
    .then(res => res.json())
    .then(data => {
        btn.classList.remove("loading-btn");

        if(data.success){
            showToast('✔ تم تغيير الدور بنجاح!', true);
            setTimeout(() => location.reload(), 800);
        } else {
            showToast(data.message, false);
        }
    })
    .catch(err => {
        btn.classList.remove("loading-btn");
        showToast('⚠ خطأ في الاتصال بالسيرفر', false);
    });
}
</script>