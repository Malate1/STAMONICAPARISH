<?php
$fallback = 'https://upload.wikimedia.org/wikipedia/commons/d/d4/Santa_Monica_Church_Alburquerque_%28Tagbilaran_East_Road%2C_Alburquerque%2C_Bohol%3B_01-12-2023%29.jpg';
$cover = !empty($item['cover_image']) ? base_url($item['cover_image']) : $fallback;
$days = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];

$official_photo = static function($path){
    return !empty($path) ? base_url($path) : null;
};
?>

<section class="relative overflow-hidden bg-parish-900 min-h-[430px] flex items-end">
  <img src="<?= html_escape($cover) ?>" alt="<?= html_escape($item['name']) ?>" class="absolute inset-0 w-full h-full object-cover">
  <div class="absolute inset-0 bg-gradient-to-r from-parish-900/96 via-parish-900/78 to-black/20"></div>

  <div class="relative max-w-6xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-12 sm:py-16 text-white">
    <a href="<?= site_url('chapels') ?>" class="inline-flex items-center gap-2 text-sm text-white/65 hover:text-white"><i class="ph ph-arrow-left"></i> All Chapels</a>

    <div class="max-w-3xl mt-8">
      <div class="heritage-kicker text-gold-200">Chapel community</div>
      <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold tracking-[-.03em] leading-[1.02] mt-3"><?= html_escape($item['name']) ?></h1>
      <?php if ($item['patron_saint']): ?><p class="text-gold-100 text-lg mt-3"><?= html_escape($item['patron_saint']) ?></p><?php endif; ?>

      <div class="mt-6 flex flex-wrap gap-x-5 gap-y-2 text-sm text-white/65">
        <?php if ($item['address'] || $item['barangay']): ?><span class="inline-flex items-center gap-2"><i class="ph ph-map-pin"></i><?= html_escape($item['address'] ?: $item['barangay']) ?></span><?php endif; ?>
        <?php if ($item['feast_date']): ?><span class="inline-flex items-center gap-2"><i class="ph ph-calendar-star"></i>Fiesta: <?= html_escape($item['feast_date']) ?></span><?php endif; ?>
        <?php if ($item['contact_number']): ?><span class="inline-flex items-center gap-2"><i class="ph ph-phone"></i><?= html_escape($item['contact_number']) ?></span><?php endif; ?>
      </div>
    </div>
  </div>
</section>

<section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-16">
  <div class="grid lg:grid-cols-[1fr_.42fr] gap-8 lg:gap-10 items-start">
    <div class="space-y-8">
      <section class="rounded-[2rem] bg-white border border-stonewarm-200 p-7 sm:p-10 shadow-soft">
        <div class="heritage-kicker text-gold-600">About the chapel</div>
        <h2 class="text-2xl sm:text-3xl font-bold text-parish-900 mt-2">A local community of faith</h2>
        <?php if ($item['description']): ?>
          <div class="mt-5 text-gray-700 leading-relaxed whitespace-pre-line"><?= nl2br(html_escape($item['description'])) ?></div>
        <?php else: ?>
          <p class="mt-5 text-gray-500">The parish office is preparing more information about this chapel community.</p>
        <?php endif; ?>

        <?php if ($item['location_notes']): ?>
          <div class="mt-6 rounded-xl bg-parish-50/60 border border-parish-100 p-4">
            <div class="text-xs uppercase tracking-wider text-parish-600 font-bold">Directions / Landmark</div>
            <div class="text-sm text-gray-700 mt-1"><?= html_escape($item['location_notes']) ?></div>
          </div>
        <?php endif; ?>
      </section>

      <section class="rounded-[2rem] bg-white border border-stonewarm-200 p-7 sm:p-10">
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-3">
          <div>
            <div class="heritage-kicker text-gold-600">Worship</div>
            <h2 class="text-2xl sm:text-3xl font-bold text-parish-900 mt-2">Chapel Mass Schedule</h2>
          </div>
          <a href="<?= site_url('mass-schedule') ?>" class="text-sm font-semibold text-parish-700 hover:underline">Main parish schedule <i class="ph ph-arrow-right ml-1"></i></a>
        </div>

        <?php if (empty($mass_schedules)): ?>
          <div class="mt-6 rounded-xl border border-dashed border-stonewarm-200 p-8 text-center text-sm text-gray-400">
            No regular chapel Mass schedule has been published yet.
          </div>
        <?php else: ?>
          <div class="grid sm:grid-cols-2 gap-4 mt-6">
            <?php foreach ($mass_schedules as $m): ?>
              <article class="rounded-2xl border border-stonewarm-200 bg-stonewarm-50/40 p-5">
                <div class="flex items-start justify-between gap-4">
                  <div>
                    <div class="text-[10px] uppercase tracking-wider font-bold text-gold-600"><?= $days[(int)$m['day_of_week']] ?></div>
                    <div class="text-2xl font-bold text-parish-900 mt-1"><?= date('g:i A',strtotime($m['mass_time'])) ?></div>
                    <div class="text-sm font-semibold text-gray-700 mt-2"><?= html_escape($m['title']) ?></div>
                  </div>
                  <div class="w-10 h-10 rounded-xl bg-white border border-stonewarm-200 text-parish-700 flex items-center justify-center"><i class="ph ph-church"></i></div>
                </div>

                <?php if ($m['recurrence_note']): ?><div class="text-xs text-gray-500 mt-3"><i class="ph ph-repeat mr-1 text-parish-600"></i><?= html_escape($m['recurrence_note']) ?></div><?php endif; ?>
                <?php if ($m['language']): ?><div class="text-xs text-gray-500 mt-1"><i class="ph ph-translate mr-1 text-parish-600"></i><?= html_escape($m['language']) ?></div><?php endif; ?>
                <?php if ($m['notes']): ?><div class="text-xs text-gray-400 mt-2 leading-relaxed"><?= html_escape($m['notes']) ?></div><?php endif; ?>
              </article>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <p class="text-[11px] text-gray-400 mt-5">Schedules may change for fiestas, Holy Week, Christmas, funerals or other parish occasions. Check parish announcements for temporary changes.</p>
      </section>

      <section class="rounded-[2rem] bg-white border border-stonewarm-200 p-7 sm:p-10">
        <div class="heritage-kicker text-gold-600">GSK life</div>
        <h2 class="text-2xl sm:text-3xl font-bold text-parish-900 mt-2">GSK Clusters</h2>
        <p class="text-gray-600 mt-3">These smaller community groupings help organize prayer, formation, service and neighborhood-level participation under this chapel.</p>

        <?php if (empty($clusters)): ?>
          <div class="mt-6 rounded-xl border border-dashed border-stonewarm-200 p-8 text-center text-sm text-gray-400">No public GSK clusters have been added for this chapel yet.</div>
        <?php else: ?>
          <div class="space-y-4 mt-6">
            <?php foreach ($clusters as $cluster): ?>
              <article class="rounded-2xl border border-stonewarm-200 overflow-hidden">
                <div class="p-5 sm:p-6 bg-stonewarm-50/45">
                  <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
                    <div>
                      <div class="flex flex-wrap items-center gap-2">
                        <h3 class="text-lg font-bold text-parish-900"><?= html_escape($cluster['name']) ?></h3>
                        <?php if ($cluster['code']): ?><span class="px-2 py-0.5 rounded-full bg-white border border-stonewarm-200 text-[10px] font-bold text-gray-500"><?= html_escape($cluster['code']) ?></span><?php endif; ?>
                      </div>
                      <?php if ($cluster['coverage_area']): ?><div class="text-xs text-gray-500 mt-2"><i class="ph ph-map-trifold mr-1 text-parish-600"></i><?= html_escape($cluster['coverage_area']) ?></div><?php endif; ?>
                      <?php if ($cluster['meeting_schedule']): ?><div class="text-xs text-gray-500 mt-1"><i class="ph ph-calendar-dots mr-1 text-parish-600"></i><?= html_escape($cluster['meeting_schedule']) ?></div><?php endif; ?>
                    </div>
                    <span class="inline-flex self-start px-2.5 py-1 rounded-full bg-parish-50 text-parish-700 text-[10px] uppercase tracking-wider font-bold">GSK Cluster</span>
                  </div>
                  <?php if ($cluster['description']): ?><p class="text-sm text-gray-600 leading-relaxed mt-4"><?= html_escape($cluster['description']) ?></p><?php endif; ?>
                </div>

                <?php if (!empty($cluster['officials'])): ?>
                  <div class="p-5 sm:p-6 border-t border-stonewarm-200">
                    <div class="text-[10px] uppercase tracking-wider text-gray-400 font-bold mb-3">Cluster Officials</div>
                    <div class="grid sm:grid-cols-2 gap-3">
                      <?php foreach ($cluster['officials'] as $o): ?>
                        <div class="flex items-center gap-3 rounded-xl bg-gray-50/70 border border-gray-100 p-3">
                          <div class="w-9 h-9 rounded-lg bg-white border border-gray-100 text-parish-700 flex items-center justify-center overflow-hidden flex-shrink-0">
                            <?php if ($official_photo($o['photo'])): ?><img src="<?= html_escape($official_photo($o['photo'])) ?>" alt="" class="w-full h-full object-cover"><?php else: ?><i class="ph ph-user"></i><?php endif; ?>
                          </div>
                          <div class="min-w-0">
                            <div class="text-sm font-semibold text-gray-800"><?= html_escape($o['full_name']) ?></div>
                            <div class="text-[11px] text-gray-500"><?= html_escape($o['position_title']) ?><?= $o['committee_area'] ? ' · '.html_escape($o['committee_area']) : '' ?></div>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  </div>
                <?php endif; ?>
              </article>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>
      </section>
    </div>

    <aside class="lg:sticky lg:top-28 space-y-5">
      <section class="rounded-[2rem] bg-parish-900 text-white p-6 sm:p-7 shadow-heritage">
        <div class="heritage-kicker text-gold-300">Chapel Leadership</div>
        <h2 class="text-xl font-bold mt-2">Officials serving this chapel</h2>

        <?php if (empty($officials)): ?>
          <p class="text-sm text-white/55 mt-4">Chapel officials have not yet been published.</p>
        <?php else: ?>
          <div class="space-y-3 mt-5">
            <?php foreach ($officials as $o): $photo=$official_photo($o['photo']); ?>
              <div class="rounded-xl bg-white/10 border border-white/10 p-3 flex items-center gap-3">
                <div class="w-11 h-11 rounded-xl bg-white/10 overflow-hidden flex items-center justify-center text-gold-200 flex-shrink-0">
                  <?php if ($photo): ?><img src="<?= html_escape($photo) ?>" alt="" class="w-full h-full object-cover"><?php else: ?><i class="ph ph-user"></i><?php endif; ?>
                </div>
                <div class="min-w-0">
                  <div class="font-semibold text-sm"><?= html_escape($o['full_name']) ?></div>
                  <div class="text-[11px] text-gold-200 mt-0.5"><?= html_escape($o['position_title']) ?></div>
                  <?php if ($o['committee_area']): ?><div class="text-[10px] text-white/45 mt-0.5"><?= html_escape($o['committee_area']) ?></div><?php endif; ?>
                </div>
              </div>
            <?php endforeach; ?>
          </div>
        <?php endif; ?>

        <a href="<?= site_url('parish-organization') ?>" class="mt-6 w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-white text-parish-900 text-sm font-semibold hover:bg-gold-50">View Full Parish Organization <i class="ph ph-arrow-right"></i></a>
      </section>

      <?php if ($item['latitude'] && $item['longitude']): ?>
        <section class="rounded-2xl bg-white border border-stonewarm-200 p-5">
          <div class="text-sm font-semibold text-gray-800">Location</div>
          <p class="text-xs text-gray-500 mt-2">Coordinates are available for this chapel.</p>
          <a href="https://www.google.com/maps?q=<?= rawurlencode($item['latitude'].','.$item['longitude']) ?>" target="_blank" rel="noopener" class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-parish-700 hover:underline"><i class="ph ph-map-pin"></i> Open in Maps</a>
        </section>
      <?php endif; ?>
    </aside>
  </div>
</section>
