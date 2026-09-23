<?php
$hero_img = 'https://upload.wikimedia.org/wikipedia/commons/c/c8/Santa_Monica_Church_Alburquerque_with_convent_%28Tagbilaran_East_Road%2C_Alburquerque%2C_Bohol%3B_01-12-2023%29.jpg';
?>

<section class="relative overflow-hidden bg-parish-900 min-h-[340px] flex items-end">
  <img src="<?= $hero_img ?>" alt="Santa Monica Parish Church and convent" class="absolute inset-0 w-full h-full object-cover object-center">
  <div class="absolute inset-0 bg-gradient-to-r from-parish-900/95 via-parish-900/72 to-black/20"></div>
  <div class="relative max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-14 sm:py-16 text-white">
    <div class="heritage-kicker text-gold-200">Serve with purpose</div>
    <h1 class="text-4xl sm:text-5xl font-bold tracking-tight mt-3">Parish Ministries</h1>
    <p class="text-white/70 mt-3 max-w-2xl text-lg">Find a community where you can pray, serve, learn and help strengthen parish life.</p>
  </div>
</section>

<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20">
  <div class="mb-9">
    <div class="heritage-kicker text-gold-600">Faith in action</div>
    <h2 class="text-3xl sm:text-4xl font-bold text-parish-900 mt-2">There is a place for you in the community.</h2>
    <p class="text-gray-500 mt-2 max-w-2xl">Explore parish groups and ministries, learn what they do, and send your interest directly online.</p>
  </div>

  <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
    <?php if (empty($ministries)): ?>
      <div class="sm:col-span-2 lg:col-span-3 rounded-[2rem] bg-white border border-stonewarm-200 p-10 text-center text-gray-500">No ministries are listed yet.</div>
    <?php endif; ?>
    <?php foreach ($ministries as $m): ?>
    <a href="<?= site_url('ministries/' . $m['slug']) ?>" class="group rounded-3xl bg-white border border-stonewarm-200 p-6 hover:border-parish-200 hover:shadow-heritage transition duration-300">
      <div class="w-14 h-14 rounded-2xl bg-parish-50 text-parish-700 flex items-center justify-center text-2xl group-hover:bg-parish-800 group-hover:text-white transition"><i class="ph ph-users-three"></i></div>
      <h3 class="text-xl font-bold text-parish-900 mt-5"><?= html_escape($m['name']) ?></h3>
      <p class="text-sm text-gray-500 mt-2 leading-relaxed line-clamp-4"><?= html_escape($m['description']) ?></p>
      <div class="mt-6 pt-4 border-t border-stonewarm-200 flex items-center justify-between text-sm font-semibold text-parish-700">
        Learn more <i class="ph ph-arrow-right group-hover:translate-x-1 transition"></i>
      </div>
    </a>
    <?php endforeach; ?>
  </div>
</section>

<section class="bg-parish-50 border-y border-parish-100">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 grid md:grid-cols-[1fr_auto] gap-8 items-center">
    <div>
      <div class="heritage-kicker text-gold-600">Community life</div>
      <h2 class="text-2xl sm:text-3xl font-bold text-parish-900 mt-2">Looking for another way to participate?</h2>
      <p class="text-gray-600 mt-2 max-w-2xl">Watch the Events page for parish activities, formation sessions, celebrations and volunteer opportunities.</p>
    </div>
    <a href="<?= site_url('events') ?>" class="inline-flex items-center gap-2 px-5 py-3 rounded-full bg-parish-800 text-white font-semibold hover:bg-parish-900 transition">View Parish Events <i class="ph ph-arrow-right"></i></a>
  </div>
</section>
