<?php
$hero_img = 'https://upload.wikimedia.org/wikipedia/commons/2/2f/Santa_Monica_Church_Alburquerque_inside_%28Tagbilaran_East_Road%2C_Alburquerque%2C_Bohol%3B_01-12-2023%29.jpg';
?>

<section class="relative overflow-hidden bg-parish-900 min-h-[360px] flex items-end">
  <img src="<?= $hero_img ?>" alt="Interior of Santa Monica Parish Church" class="absolute inset-0 w-full h-full object-cover object-center">
  <div class="absolute inset-0 bg-gradient-to-r from-parish-900/95 via-parish-900/72 to-black/20"></div>
  <div class="relative max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-14 sm:py-16 text-white">
    <div class="heritage-kicker text-gold-200">Faith at life's milestones</div>
    <h1 class="text-4xl sm:text-5xl font-bold tracking-tight mt-3">Sacraments &amp; Parish Services</h1>
    <p class="text-white/70 mt-3 max-w-2xl text-lg">Prepare for life's sacred moments and submit parish service requests online, with clear steps from application to completion.</p>
  </div>
</section>

<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20">
  <div class="flex flex-col lg:flex-row lg:items-end justify-between gap-5 mb-9">
    <div>
      <div class="heritage-kicker text-gold-600">Online parish services</div>
      <h2 class="text-3xl sm:text-4xl font-bold text-parish-900 mt-2">Choose the service you need.</h2>
      <p class="text-gray-500 mt-2">Requirements, fees and availability are shown during the application process.</p>
    </div>
    <div class="inline-flex items-center gap-2 rounded-full border border-stonewarm-200 bg-white px-4 py-2.5 text-sm text-gray-500">
      <i class="ph ph-check-circle text-parish-700"></i>
      Track your application after logging in
    </div>
  </div>

  <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
    <?php
      $icons = ['baptism' => 'ph-drop', 'wedding' => 'ph-heart', 'funeral' => 'ph-cross', 'confirmation' => 'ph-sparkle', 'house_blessing' => 'ph-house-line', 'vehicle_blessing' => 'ph-car', 'counseling' => 'ph-chats-circle'];
      foreach ($service_types as $s):
        $icon = $icons[$s['service_key']] ?? 'ph-hand-heart';
    ?>
    <div class="group rounded-3xl bg-white border border-stonewarm-200 p-6 hover:border-parish-200 hover:shadow-heritage transition duration-300 flex flex-col">
      <div class="flex items-start justify-between gap-4">
        <div class="w-13 h-13 w-[52px] h-[52px] rounded-2xl bg-parish-50 text-parish-700 flex items-center justify-center text-2xl group-hover:bg-parish-800 group-hover:text-white transition">
          <i class="ph <?= $icon ?>"></i>
        </div>
        <span class="rounded-full bg-gold-50 text-gold-700 px-3 py-1 text-xs font-semibold"><?= peso($s['base_fee']) ?></span>
      </div>
      <h3 class="text-xl font-bold text-parish-900 mt-5"><?= html_escape($s['name']) ?></h3>
      <p class="text-sm text-gray-500 leading-relaxed mt-2 flex-1"><?= html_escape($s['description']) ?></p>
      <a href="<?= site_url('my/bookings/new/' . $s['service_key']) ?>" class="mt-6 pt-4 border-t border-stonewarm-200 flex items-center justify-between text-sm font-semibold text-parish-700 group-hover:text-parish-900">
        Begin application <i class="ph ph-arrow-right"></i>
      </a>
    </div>
    <?php endforeach; ?>
  </div>
</section>

<section class="bg-white border-y border-stonewarm-200">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 grid md:grid-cols-3 gap-5">
    <div class="rounded-2xl bg-stonewarm-50 border border-stonewarm-200 p-6">
      <div class="w-10 h-10 rounded-xl bg-white text-parish-700 flex items-center justify-center shadow-sm"><i class="ph ph-user-circle-plus text-xl"></i></div>
      <h3 class="font-bold text-parish-900 mt-4">1. Create an account</h3>
      <p class="text-sm text-gray-500 mt-2 leading-relaxed">Use one parish account to submit requests and follow their progress.</p>
    </div>
    <div class="rounded-2xl bg-stonewarm-50 border border-stonewarm-200 p-6">
      <div class="w-10 h-10 rounded-xl bg-white text-parish-700 flex items-center justify-center shadow-sm"><i class="ph ph-file-text text-xl"></i></div>
      <h3 class="font-bold text-parish-900 mt-4">2. Submit requirements</h3>
      <p class="text-sm text-gray-500 mt-2 leading-relaxed">Complete the application and provide the documents requested for your service.</p>
    </div>
    <div class="rounded-2xl bg-stonewarm-50 border border-stonewarm-200 p-6">
      <div class="w-10 h-10 rounded-xl bg-white text-parish-700 flex items-center justify-center shadow-sm"><i class="ph ph-bell-ringing text-xl"></i></div>
      <h3 class="font-bold text-parish-900 mt-4">3. Follow parish updates</h3>
      <p class="text-sm text-gray-500 mt-2 leading-relaxed">Track review, payment and scheduling updates from your account.</p>
    </div>
  </div>
</section>

<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
  <div class="rounded-[2rem] bg-parish-900 text-white p-7 sm:p-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
    <div>
      <div class="heritage-kicker text-gold-300">Need guidance?</div>
      <h2 class="text-2xl sm:text-3xl font-bold mt-2">Not sure which service or requirement applies?</h2>
      <p class="text-white/65 mt-2 max-w-2xl">Contact the parish office and we can help you understand the next step before submitting an application.</p>
    </div>
    <a href="<?= site_url('contact') ?>" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-full bg-white text-parish-900 font-semibold hover:bg-gold-50 transition flex-shrink-0">Ask the Parish Office <i class="ph ph-arrow-right"></i></a>
  </div>
</section>
