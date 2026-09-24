<?php
$fallback = 'https://upload.wikimedia.org/wikipedia/commons/d/d4/Santa_Monica_Church_Alburquerque_%28Tagbilaran_East_Road%2C_Alburquerque%2C_Bohol%3B_01-12-2023%29.jpg';
$days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
?>

<section class="relative overflow-hidden bg-parish-900 min-h-[360px] flex items-end">
  <img src="<?= $fallback ?>" alt="Sta. Monica Parish Church" class="absolute inset-0 w-full h-full object-cover opacity-40">
  <div class="absolute inset-0 bg-gradient-to-r from-parish-900 via-parish-900/92 to-parish-900/55"></div>
  <div class="relative max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-14 sm:py-16 text-white">
    <div class="heritage-kicker text-gold-200">Gagmayng Simbahanong Katilingban</div>
    <h1 class="text-4xl sm:text-5xl font-bold tracking-tight mt-3">Our Chapels & GSK Communities</h1>
    <p class="text-white/70 mt-3 max-w-3xl text-lg">Explore chapel communities across the parish, their regular Mass schedules, patronal identity, and the GSK clusters that help organize faith life at the neighborhood level.</p>
  </div>
</section>

<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-18">
  <div class="rounded-[2rem] border border-parish-100 bg-parish-50/60 p-6 sm:p-8 mb-10">
    <div class="grid lg:grid-cols-[.7fr_1.3fr] gap-6 items-start">
      <div>
        <div class="w-12 h-12 rounded-2xl bg-white text-parish-700 border border-parish-100 flex items-center justify-center text-2xl"><i class="ph ph-tree-structure"></i></div>
        <h2 class="text-2xl font-bold text-parish-900 mt-4">What is GSK?</h2>
      </div>
      <div class="text-sm sm:text-base text-gray-600 leading-relaxed">
        <p><strong class="text-gray-800">Gagmayng Simbahanong Katilingban (GSK)</strong> is a local Cebuano term used for Basic Ecclesial Communities: small Christian communities rooted in neighborhoods, barangays or sitios and united with the parish. Depending on the local setup, a GSK may be centered on a chapel and may also be divided into smaller family or neighborhood groupings.</p>
        <p class="mt-3">This directory follows the parish’s actual structure rather than forcing every chapel to use the same number of clusters or officer titles.</p>
        <a href="<?= site_url('parish-organization') ?>" class="mt-4 inline-flex items-center gap-2 font-semibold text-parish-700 hover:underline">View parish → chapel → cluster leadership <i class="ph ph-arrow-right"></i></a>
      </div>
    </div>
  </div>

  <?php if (!$schema_ready || empty($chapels)): ?>
    <div class="rounded-[2rem] bg-white border border-stonewarm-200 p-10 sm:p-14 text-center">
      <i class="ph ph-church text-4xl text-gray-300"></i>
      <h2 class="text-xl font-bold text-parish-900 mt-4">Chapel directory is being prepared</h2>
      <p class="text-gray-500 mt-2">The parish office can publish chapel details and Mass schedules from Parish Connect.</p>
    </div>
  <?php else: ?>
    <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-6">
      <?php foreach ($chapels as $chapel):
        $img = !empty($chapel['cover_image']) ? base_url($chapel['cover_image']) : $fallback;
      ?>
      <a href="<?= site_url('chapels/' . $chapel['slug']) ?>" class="group rounded-[2rem] overflow-hidden bg-white border border-stonewarm-200 hover:border-parish-200 hover:shadow-heritage transition duration-300">
        <div class="relative h-56 overflow-hidden bg-parish-900">
          <img src="<?= html_escape($img) ?>" alt="<?= html_escape($chapel['name']) ?>" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition duration-700">
          <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/10 to-transparent"></div>
          <div class="absolute left-5 right-5 bottom-5 text-white">
            <?php if ($chapel['patron_saint']): ?><div class="text-[10px] uppercase tracking-[.14em] font-bold text-gold-200"><?= html_escape($chapel['patron_saint']) ?></div><?php endif; ?>
            <h2 class="text-2xl font-bold mt-1"><?= html_escape($chapel['name']) ?></h2>
            <div class="text-xs text-white/65 mt-1"><i class="ph ph-map-pin mr-1"></i><?= html_escape($chapel['barangay'] ?: $chapel['address'] ?: 'Alburquerque, Bohol') ?></div>
          </div>
        </div>

        <div class="p-5 sm:p-6">
          <?php if ($chapel['description']): ?><p class="text-sm text-gray-500 line-clamp-3"><?= html_escape(strip_tags($chapel['description'])) ?></p><?php endif; ?>

          <div class="mt-5 grid grid-cols-2 gap-3">
            <div class="rounded-xl bg-parish-50/60 border border-parish-100 p-3">
              <div class="text-[10px] uppercase tracking-wider text-gray-400 font-bold">Masses</div>
              <div class="text-lg font-bold text-parish-900 mt-1"><?= count($chapel['mass_schedules']) ?></div>
              <div class="text-[10px] text-gray-400">published schedule<?= count($chapel['mass_schedules'])===1?'':'s' ?></div>
            </div>
            <div class="rounded-xl bg-gold-50/60 border border-gold-100 p-3">
              <div class="text-[10px] uppercase tracking-wider text-gray-400 font-bold">GSK</div>
              <div class="text-lg font-bold text-parish-900 mt-1"><?= count($chapel['clusters']) ?></div>
              <div class="text-[10px] text-gray-400">cluster<?= count($chapel['clusters'])===1?'':'s' ?></div>
            </div>
          </div>

          <?php if (!empty($chapel['mass_schedules'])): ?>
            <div class="mt-5 pt-4 border-t border-stonewarm-100">
              <div class="text-[10px] uppercase tracking-wider text-gray-400 font-bold mb-2">Regular Mass Schedule</div>
              <div class="space-y-1.5">
                <?php foreach (array_slice($chapel['mass_schedules'],0,3) as $m): ?>
                  <div class="flex items-center justify-between gap-3 text-xs"><span class="text-gray-600"><?= $days[(int)$m['day_of_week']] ?><?= $m['recurrence_note'] ? ' · '.html_escape($m['recurrence_note']) : '' ?></span><strong class="text-parish-800"><?= date('g:i A',strtotime($m['mass_time'])) ?></strong></div>
                <?php endforeach; ?>
              </div>
            </div>
          <?php endif; ?>

          <div class="mt-5 flex items-center justify-between text-sm font-semibold text-parish-700">
            <span>View chapel details</span><i class="ph ph-arrow-right"></i>
          </div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>
