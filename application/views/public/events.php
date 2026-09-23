<?php
$hero_img = 'https://upload.wikimedia.org/wikipedia/commons/d/d4/Santa_Monica_Church_Alburquerque_%28Tagbilaran_East_Road%2C_Alburquerque%2C_Bohol%3B_01-12-2023%29.jpg';
?>

<section class="relative overflow-hidden bg-parish-900 min-h-[340px] flex items-end">
  <img src="<?= $hero_img ?>" alt="Santa Monica Parish Church in Alburquerque" class="absolute inset-0 w-full h-full object-cover object-center">
  <div class="absolute inset-0 bg-gradient-to-r from-parish-900/95 via-parish-900/70 to-black/20"></div>
  <div class="relative max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-14 sm:py-16 text-white">
    <div class="heritage-kicker text-gold-200">Gather · celebrate · serve</div>
    <h1 class="text-4xl sm:text-5xl font-bold tracking-tight mt-3">Parish Events</h1>
    <p class="text-white/70 mt-3 max-w-2xl text-lg">Discover upcoming liturgical celebrations, parish activities, ministry gatherings and community events.</p>
  </div>
</section>

<section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20">
  <?php if (empty($events)): ?>
    <div class="rounded-[2rem] bg-white border border-stonewarm-200 p-10 sm:p-14 text-center shadow-soft">
      <div class="w-16 h-16 mx-auto rounded-2xl bg-parish-50 text-parish-700 flex items-center justify-center text-3xl"><i class="ph ph-calendar-dots"></i></div>
      <h2 class="text-2xl font-bold text-parish-900 mt-5">No upcoming events yet</h2>
      <p class="text-gray-500 mt-2">Future parish activities will appear here as soon as they are published.</p>
    </div>
  <?php else: ?>
    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
      <?php foreach ($events as $e): ?>
      <a href="<?= site_url('events/' . $e['slug']) ?>" class="group bg-white rounded-3xl border border-stonewarm-200 overflow-hidden hover:border-parish-200 hover:shadow-heritage transition duration-300">
        <div class="relative h-40 overflow-hidden bg-parish-900">
          <img src="<?= $hero_img ?>" alt="" class="absolute inset-0 w-full h-full object-cover opacity-35 group-hover:scale-105 transition duration-700">
          <div class="absolute inset-0 bg-gradient-to-t from-parish-900/90 to-transparent"></div>
          <div class="absolute left-5 bottom-4 flex items-end gap-3 text-white">
            <div class="w-16 h-16 rounded-2xl bg-white text-parish-900 flex flex-col items-center justify-center shadow-lg">
              <div class="text-[10px] font-bold uppercase tracking-wider text-gold-600"><?= date('M', strtotime($e['event_date'])) ?></div>
              <div class="text-2xl font-bold leading-none"><?= date('d', strtotime($e['event_date'])) ?></div>
            </div>
            <?php if ($e['category']): ?><div class="text-[11px] font-semibold uppercase tracking-wider text-gold-200 mb-1"><?= html_escape($e['category']) ?></div><?php endif; ?>
          </div>
        </div>
        <div class="p-5 sm:p-6">
          <h2 class="text-xl font-bold text-gray-900 group-hover:text-parish-800 transition"><?= html_escape($e['title']) ?></h2>
          <div class="mt-4 space-y-2 text-sm text-gray-500">
            <div class="flex items-center gap-2"><i class="ph ph-calendar-blank text-parish-700"></i><?= format_date($e['event_date']) ?><?= $e['event_time'] ? ' · ' . date('g:i A', strtotime($e['event_time'])) : '' ?></div>
            <div class="flex items-center gap-2"><i class="ph ph-map-pin text-parish-700"></i><?= html_escape($e['location'] ?: 'Parish grounds') ?></div>
          </div>
          <div class="mt-5 pt-4 border-t border-stonewarm-200 flex items-center justify-between">
            <span class="text-sm font-semibold text-parish-700">View event</span>
            <i class="ph ph-arrow-up-right text-gray-300 group-hover:text-parish-700"></i>
          </div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<section class="bg-white border-y border-stonewarm-200">
  <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-14 flex flex-col md:flex-row md:items-center justify-between gap-6">
    <div>
      <div class="heritage-kicker text-gold-600">Be part of parish life</div>
      <h2 class="text-2xl sm:text-3xl font-bold text-parish-900 mt-2">Find a ministry and serve with the community.</h2>
      <p class="text-gray-600 mt-2 max-w-2xl">Parish life continues beyond Sunday Mass. Discover groups where you can pray, volunteer, learn and serve.</p>
    </div>
    <a href="<?= site_url('ministries') ?>" class="px-5 py-3 rounded-full bg-parish-800 text-white font-semibold hover:bg-parish-900 transition flex-shrink-0">Explore Ministries</a>
  </div>
</section>
