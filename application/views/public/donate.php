<?php
$hero_img = 'https://upload.wikimedia.org/wikipedia/commons/2/2f/Santa_Monica_Church_Alburquerque_inside_%28Tagbilaran_East_Road%2C_Alburquerque%2C_Bohol%3B_01-12-2023%29.jpg';
?>

<section class="relative overflow-hidden bg-parish-900 min-h-[340px] flex items-end">
  <img src="<?= $hero_img ?>" alt="Interior of Santa Monica Parish Church" class="absolute inset-0 w-full h-full object-cover object-center">
  <div class="absolute inset-0 bg-gradient-to-r from-parish-900/95 via-parish-900/72 to-black/20"></div>
  <div class="relative max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-14 sm:py-16 text-white">
    <div class="heritage-kicker text-gold-200">Stewardship</div>
    <h1 class="text-4xl sm:text-5xl font-bold tracking-tight mt-3">Support Our Parish</h1>
    <p class="text-white/70 mt-3 max-w-2xl text-lg">Help sustain parish ministries, community outreach and the care of this historic place of worship.</p>
  </div>
</section>

<section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20">
  <div class="grid lg:grid-cols-[1fr_.85fr] gap-8 lg:gap-12 items-start">
    <div>
      <div class="heritage-kicker text-gold-600">Your generosity matters</div>
      <h2 class="text-3xl sm:text-4xl font-bold text-parish-900 mt-3">Giving helps faith and community continue to flourish.</h2>
      <p class="text-gray-600 leading-relaxed mt-5 text-lg">Donations help the parish support church upkeep, pastoral work, feeding and outreach programs, parish activities and other community needs.</p>

      <div class="grid sm:grid-cols-2 gap-4 mt-8">
        <div class="rounded-2xl bg-white border border-stonewarm-200 p-5">
          <i class="ph ph-church text-2xl text-parish-700"></i>
          <h3 class="font-bold text-parish-900 mt-3">Church Care</h3>
          <p class="text-sm text-gray-500 mt-1.5">Support ongoing care and maintenance of parish spaces.</p>
        </div>
        <div class="rounded-2xl bg-white border border-stonewarm-200 p-5">
          <i class="ph ph-hand-heart text-2xl text-parish-700"></i>
          <h3 class="font-bold text-parish-900 mt-3">Community Outreach</h3>
          <p class="text-sm text-gray-500 mt-1.5">Help fund charitable, pastoral and community-based programs.</p>
        </div>
        <div class="rounded-2xl bg-white border border-stonewarm-200 p-5">
          <i class="ph ph-users-three text-2xl text-parish-700"></i>
          <h3 class="font-bold text-parish-900 mt-3">Parish Ministries</h3>
          <p class="text-sm text-gray-500 mt-1.5">Strengthen activities that serve families, youth and parish groups.</p>
        </div>
        <div class="rounded-2xl bg-white border border-stonewarm-200 p-5">
          <i class="ph ph-heart-straight text-2xl text-parish-700"></i>
          <h3 class="font-bold text-parish-900 mt-3">Faith Mission</h3>
          <p class="text-sm text-gray-500 mt-1.5">Support the continuing spiritual and pastoral mission of the parish.</p>
        </div>
      </div>
    </div>

    <div class="rounded-[2rem] bg-parish-900 text-white p-7 sm:p-9 shadow-heritage text-center">
      <div class="heritage-kicker text-gold-300">GCash Giving</div>
      <h2 class="text-2xl font-bold mt-2">Make a parish donation</h2>
      <p class="text-sm text-white/60 mt-2">The official parish GCash QR code can be placed here by the administrator.</p>
      <div class="w-56 h-56 mx-auto mt-7 rounded-3xl bg-white border-8 border-white/10 flex items-center justify-center text-gray-300 shadow-lg">
        <i class="ph ph-qr-code" style="font-size:6rem"></i>
      </div>
      <a href="<?= site_url('login') ?>" class="mt-7 inline-flex items-center justify-center gap-2 w-full px-6 py-3.5 rounded-xl bg-gold-500 hover:bg-gold-600 text-white font-semibold transition">
        <i class="ph ph-user-circle"></i> Log In to Donate
      </a>
      <p class="text-xs text-white/45 mt-3 leading-relaxed">Logged-in donors can submit a payment reference and receive a parish acknowledgement. Anonymous giving may also be supported according to parish policy.</p>
    </div>
  </div>
</section>
