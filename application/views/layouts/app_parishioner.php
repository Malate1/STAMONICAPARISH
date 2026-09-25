<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" type="image/svg+xml" href="<?= base_url('favicon.svg') ?>">
<link rel="shortcut icon" href="<?= base_url('favicon.svg') ?>">
<title><?= isset($page_title) ? $page_title . ' | ' : '' ?>My Account - Sta. Monica Parish Connect</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Google+Sans:wght@400;500;600;700&family=Product+Sans:wght@400;700&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<link rel="stylesheet" href="https://code.jquery.com/ui/1.13.3/themes/base/jquery-ui.css">
<script src="https://code.jquery.com/ui/1.13.3/jquery-ui.min.js"></script>
<script defer src="https://cdnjs.cloudflare.com/ajax/libs/alpinejs/3.13.5/cdn.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.11/css/jquery.dataTables.min.css">
<script src="https://cdn.datatables.net/1.13.11/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/regular/style.css">
<link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/fill/style.css">
<script>
  tailwind.config = { theme: { extend: {
    fontFamily: { sans: ['Google Sans', 'Product Sans', 'ui-sans-serif', 'system-ui'] },
    colors: { parish: { 50:'#f1f8f3',100:'#dcefe1',200:'#b8dfc4',300:'#8bc99f',400:'#5cae79',500:'#3a8f5b',600:'#2a7146',700:'#235a38',800:'#1e482f',900:'#1a3c28' },
              gold: { 50:'#fdf9ec',100:'#f8edc7',200:'#f0d88a',300:'#e7bf4d',400:'#deac2c',500:'#c8901d',600:'#a97117',700:'#875417',800:'#704419',900:'#5f3a1a' } }
  } } }
</script>
<style>
  body{font-family:'Google Sans','Product Sans',ui-sans-serif,system-ui}
  .toast-top-right{top:80px !important}
  [x-cloak]{display:none !important}
  table.dataTable thead th{border-bottom:2px solid #e5e7eb !important;font-size:.72rem;text-transform:uppercase;letter-spacing:.03em;color:#6b7280}
  table.dataTable tbody td{vertical-align:middle;padding-top:.85rem;padding-bottom:.85rem}
  .dataTables_wrapper .dataTables_filter input, .dataTables_wrapper .dataTables_length select { border:1px solid #e5e7eb; border-radius:.5rem; padding:.35rem .6rem; margin-left:.5rem }
  .dataTables_wrapper .dataTables_paginate .paginate_button { padding:.3rem .65rem !important; border-radius:.5rem !important; margin-left:2px }
  .dataTables_wrapper .dataTables_paginate .paginate_button.current { background:#235a38 !important; color:white !important; border:none !important }
</style>
</head>
<body class="bg-gray-50 text-gray-800 antialiased" x-data="{ sidebarOpen: false }">

<div class="flex h-screen overflow-hidden">
  <?php $this->load->view('partials/sidebar_parishioner', ['current_user' => $current_user]); ?>

  <div class="flex-1 flex flex-col overflow-hidden">
    <?php $this->load->view('partials/topbar', ['current_user' => $current_user]); ?>
    <main class="flex-1 overflow-y-auto p-4 sm:p-6 lg:p-8">
      <?php $this->load->view($body_view, get_defined_vars()); ?>
    </main>
  </div>
</div>

<?php $this->load->view('partials/form_enhancements'); ?>
<?php $this->load->view('partials/toast_init'); ?>
</body>
</html>
