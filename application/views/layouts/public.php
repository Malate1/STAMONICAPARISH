<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<meta name="theme-color" content="#173f2b">
<title><?= isset($page_title) ? $page_title . ' | ' : '' ?>Sta. Monica Parish Church</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://upload.wikimedia.org" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Google+Sans:wght@400;500;600;700&family=Product+Sans:wght@400;700&display=swap" rel="stylesheet">
<script src="https://cdn.tailwindcss.com"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.7.1/jquery.min.js"></script>
<script defer src="https://cdnjs.cloudflare.com/ajax/libs/alpinejs/3.13.5/cdn.min.js"></script>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>
<link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/regular/style.css">
<link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/fill/style.css">
<script>
  tailwind.config = {
    theme: {
      extend: {
        fontFamily: { sans: ['Google Sans', 'Product Sans', 'ui-sans-serif', 'system-ui'] },
        colors: {
          parish: { 50:'#f1f8f3',100:'#dcefe1',200:'#b8dfc4',300:'#8bc99f',400:'#5cae79',500:'#3a8f5b',600:'#2a7146',700:'#235a38',800:'#1e482f',900:'#173b29' },
          gold: { 50:'#fdf9ec',100:'#f8edc7',200:'#f0d88a',300:'#e7bf4d',400:'#deac2c',500:'#c8901d',600:'#a97117',700:'#875417',800:'#704419',900:'#5f3a1a' },
          stonewarm: { 50:'#fbfaf6',100:'#f5f1e8',200:'#e9e0ce',300:'#d6c5a5' }
        },
        boxShadow: {
          'heritage': '0 24px 70px rgba(23,59,41,.14)',
          'soft': '0 16px 45px rgba(17,24,39,.08)'
        }
      }
    }
  }
</script>
<style>
  html{scroll-behavior:smooth}
  body{font-family:'Google Sans','Product Sans',ui-sans-serif,system-ui;background:#fbfaf6}
  .toast-top-right{top:80px !important}
  [x-cloak]{display:none !important}
  .scrollbar-thin::-webkit-scrollbar{height:6px;width:6px}
  .scrollbar-thin::-webkit-scrollbar-thumb{background:#d1d5db;border-radius:9999px}
  .heritage-kicker{letter-spacing:.16em;text-transform:uppercase;font-size:.72rem;font-weight:700}
  .heritage-card{background:rgba(255,255,255,.93);border:1px solid rgba(255,255,255,.75);box-shadow:0 18px 45px rgba(23,59,41,.09)}
  .heritage-photo{position:relative;overflow:hidden}
  .heritage-photo:after{content:"";position:absolute;inset:0;background:linear-gradient(180deg,transparent 45%,rgba(8,28,18,.28) 100%);pointer-events:none}
  .heritage-photo img{transition:transform .7s cubic-bezier(.2,.75,.2,1)}
  .heritage-photo:hover img{transform:scale(1.035)}
  .heritage-rule{width:56px;height:3px;border-radius:999px;background:#c8901d}
  .public-section-title{font-size:clamp(2rem,4vw,3.2rem);line-height:1.08;font-weight:700;letter-spacing:-.035em}
  .page-hero{background:
    radial-gradient(circle at 85% 15%,rgba(240,216,138,.28),transparent 28%),
    linear-gradient(135deg,#173b29 0%,#235a38 55%,#2a7146 100%)}
  @media (prefers-reduced-motion: reduce){
    html{scroll-behavior:auto}
    .heritage-photo img{transition:none}
  }
</style>
</head>
<body class="text-gray-800 antialiased selection:bg-gold-200 selection:text-parish-900">

<?php $this->load->view('partials/public_nav', ['current_user' => $current_user ?? null]); ?>

<main class="min-h-[60vh]">
<?php $this->load->view($body_view, $body_data ?? []); ?>
</main>

<?php $this->load->view('partials/public_footer'); ?>
<?php $this->load->view('partials/toast_init'); ?>

</body>
</html>
