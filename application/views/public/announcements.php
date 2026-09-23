<?php
$hero_img = 'https://upload.wikimedia.org/wikipedia/commons/c/c8/Santa_Monica_Church_Alburquerque_with_convent_%28Tagbilaran_East_Road%2C_Alburquerque%2C_Bohol%3B_01-12-2023%29.jpg';
?>

<section class="relative overflow-hidden bg-parish-900 min-h-[340px] flex items-end">
  <img src="<?= $hero_img ?>" alt="Santa Monica Parish Church grounds" class="absolute inset-0 w-full h-full object-cover object-center">
  <div class="absolute inset-0 bg-gradient-to-r from-parish-900/95 via-parish-900/72 to-black/25"></div>
  <div class="relative max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-14 sm:py-16 text-white">
    <div class="heritage-kicker text-gold-200">Parish bulletin</div>
    <h1 class="text-4xl sm:text-5xl font-bold tracking-tight mt-3">Announcements</h1>
    <p class="text-white/70 mt-3 max-w-2xl text-lg">Stay updated with parish notices, schedule changes, community reminders and important celebrations.</p>
  </div>
</section>

<section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20">
  <?php if (empty($announcements)): ?>
    <div class="rounded-[2rem] bg-white border border-stonewarm-200 p-10 sm:p-14 text-center shadow-soft">
      <div class="w-16 h-16 mx-auto rounded-2xl bg-parish-50 text-parish-700 flex items-center justify-center text-3xl"><i class="ph ph-megaphone"></i></div>
      <h2 class="text-2xl font-bold text-parish-900 mt-5">No announcements yet</h2>
      <p class="text-gray-500 mt-2">New parish notices and schedule updates will appear here when published.</p>
    </div>
  <?php else: ?>
    <div class="grid lg:grid-cols-[1.15fr_.85fr] gap-5">
      <?php
      $featured = array_shift($announcements);
      ?>
      <a href="<?= site_url('announcements/' . $featured['slug']) ?>" class="group relative min-h-[390px] rounded-[2rem] overflow-hidden bg-parish-900 shadow-heritage">
        <img src="<?= $hero_img ?>" alt="" class="absolute inset-0 w-full h-full object-cover opacity-45 group-hover:scale-[1.03] transition duration-700">
        <div class="absolute inset-0 bg-gradient-to-t from-parish-900 via-parish-900/55 to-transparent"></div>
        <div class="absolute inset-x-0 bottom-0 p-7 sm:p-9 text-white">
          <div class="flex flex-wrap items-center gap-2">
            <?php if ($featured['is_pinned']): ?><span class="inline-flex items-center gap-1 rounded-full bg-gold-400 text-parish-900 px-3 py-1 text-[10px] font-bold uppercase tracking-wider"><i class="ph-fill ph-push-pin"></i> Pinned</span><?php endif; ?>
            <span class="text-[11px] font-semibold uppercase tracking-wider text-gold-200"><?= ucfirst(str_replace('_',' ',$featured['category'])) ?></span>
          </div>
          <h2 class="text-3xl sm:text-4xl font-bold leading-tight mt-3"><?= html_escape($featured['title']) ?></h2>
          <div class="mt-4 flex items-center gap-2 text-sm text-white/65"><i class="ph ph-calendar-blank"></i><?= format_date($featured['publish_date']) ?></div>
          <div class="mt-6 inline-flex items-center gap-2 font-semibold text-sm text-white">Read announcement <i class="ph ph-arrow-right"></i></div>
        </div>
      </a>

      <div class="space-y-4">
        <?php foreach ($announcements as $a): ?>
        <a href="<?= site_url('announcements/' . $a['slug']) ?>" class="group block rounded-2xl bg-white border border-stonewarm-200 p-5 sm:p-6 hover:border-parish-200 hover:shadow-soft transition">
          <div class="flex items-center justify-between gap-3">
            <div class="flex items-center gap-2">
              <?php if ($a['is_pinned']): ?><i class="ph-fill ph-push-pin text-gold-500"></i><?php endif; ?>
              <span class="text-[11px] text-gold-600 font-semibold uppercase tracking-wider"><?= ucfirst(str_replace('_',' ',$a['category'])) ?></span>
            </div>
            <span class="text-xs text-gray-400"><?= format_date($a['publish_date']) ?></span>
          </div>
          <h3 class="text-lg font-bold text-gray-900 mt-2 group-hover:text-parish-800 transition"><?= html_escape($a['title']) ?></h3>
          <div class="mt-4 inline-flex items-center gap-1.5 text-sm font-semibold text-parish-700">Read more <i class="ph ph-arrow-right"></i></div>
        </a>
        <?php endforeach; ?>

        <?php if (empty($announcements)): ?>
        <div class="rounded-2xl bg-gold-50 border border-gold-100 p-6">
          <div class="text-sm font-semibold text-gold-700">Latest parish notice</div>
          <p class="text-sm text-gray-600 mt-2">This is currently the only published announcement. More updates will appear here automatically.</p>
        </div>
        <?php endif; ?>
      </div>
    </div>
  <?php endif; ?>
</section>

<section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 pb-20">
  <div class="rounded-[2rem] bg-parish-50 border border-parish-100 p-7 sm:p-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
    <div>
      <div class="heritage-kicker text-gold-600">Need more information?</div>
      <h2 class="text-2xl font-bold text-parish-900 mt-2">The parish office is here to help.</h2>
      <p class="text-gray-600 mt-2">For questions about a notice, Mass schedule, sacrament or parish activity, send us an inquiry.</p>
    </div>
    <a href="<?= site_url('contact') ?>" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-full bg-parish-800 text-white font-semibold hover:bg-parish-900 transition flex-shrink-0"><i class="ph ph-chat-circle"></i> Contact Parish Office</a>
  </div>
</section>
