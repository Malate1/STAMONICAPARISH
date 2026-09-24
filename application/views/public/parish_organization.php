<?php
$hero = 'https://upload.wikimedia.org/wikipedia/commons/2/2f/Santa_Monica_Church_Alburquerque_inside_%28Tagbilaran_East_Road%2C_Alburquerque%2C_Bohol%3B_01-12-2023%29.jpg';
$photo_url = static function($path){ return !empty($path) ? base_url($path) : null; };
?>

<section class="relative overflow-hidden bg-parish-900 min-h-[390px] flex items-end">
  <img src="<?= $hero ?>" alt="Sta. Monica Parish Church interior" class="absolute inset-0 w-full h-full object-cover opacity-40">
  <div class="absolute inset-0 bg-gradient-to-r from-parish-900 via-parish-900/92 to-parish-900/55"></div>
  <div class="relative max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-14 sm:py-16 text-white">
    <div class="heritage-kicker text-gold-200">Communion, participation & mission</div>
    <h1 class="text-4xl sm:text-5xl font-bold tracking-tight mt-3">Parish & GSK Organization</h1>
    <p class="text-white/70 mt-3 max-w-3xl text-lg">A public directory of the people who serve at parish level, in chapel communities, and in neighborhood GSK clusters.</p>
  </div>
</section>

<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-18">
  <div class="rounded-[2rem] border border-parish-100 bg-parish-50/60 p-6 sm:p-8 mb-10">
    <div class="grid lg:grid-cols-[.7fr_1.3fr] gap-6">
      <div>
        <div class="w-12 h-12 rounded-2xl bg-white border border-parish-100 text-parish-700 flex items-center justify-center text-2xl"><i class="ph ph-tree-structure"></i></div>
        <h2 class="text-2xl font-bold text-parish-900 mt-4">How the directory is organized</h2>
      </div>
      <div class="text-sm sm:text-base text-gray-600 leading-relaxed">
        <p>GSK/BEC life is rooted in local neighborhoods and chapel communities while remaining united with the parish and its pastoral leadership. The parish can organize chapel communities into smaller clusters or family/neighborhood groupings according to local pastoral needs.</p>
        <div class="mt-5 grid sm:grid-cols-3 gap-3">
          <div class="rounded-xl bg-white border border-parish-100 p-4 text-center"><div class="text-xs font-bold text-parish-700">PARISH</div><div class="text-[11px] text-gray-400 mt-1">Clergy & parish officials</div></div>
          <div class="rounded-xl bg-white border border-parish-100 p-4 text-center"><div class="text-xs font-bold text-parish-700">CHAPEL</div><div class="text-[11px] text-gray-400 mt-1">Chapel leaders & councils</div></div>
          <div class="rounded-xl bg-white border border-parish-100 p-4 text-center"><div class="text-xs font-bold text-parish-700">GSK CLUSTER</div><div class="text-[11px] text-gray-400 mt-1">Neighborhood-level leaders</div></div>
        </div>
      </div>
    </div>
  </div>

  <section class="mb-12">
    <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-6">
      <div>
        <div class="heritage-kicker text-gold-600">Pastoral leadership</div>
        <h2 class="text-3xl font-bold text-parish-900 mt-2">Parish Priests</h2>
      </div>
      <a href="<?= site_url('priests') ?>" class="text-sm font-semibold text-parish-700 hover:underline">View full priest profiles <i class="ph ph-arrow-right ml-1"></i></a>
    </div>

    <?php if (empty($priests)): ?>
      <div class="rounded-2xl border border-dashed border-stonewarm-200 p-8 text-center text-sm text-gray-400">Priest profiles have not yet been published.</div>
    <?php else: ?>
      <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-5">
        <?php foreach ($priests as $p):
          $p_photo = !empty($p['avatar']) ? (preg_match('/^https?:///i',$p['avatar']) ? $p['avatar'] : base_url(ltrim($p['avatar'],'/'))) : null;
          $name = trim(($p['title'] ?: 'Rev. Fr.').' '.$p['first_name'].' '.$p['last_name']);
        ?>
        <article class="rounded-2xl bg-white border border-stonewarm-200 p-5 flex gap-4">
          <div class="w-14 h-14 rounded-2xl bg-parish-50 text-parish-700 overflow-hidden flex-shrink-0 flex items-center justify-center font-bold">
            <?php if ($p_photo): ?><img src="<?= html_escape($p_photo) ?>" alt="" class="w-full h-full object-cover"><?php else: ?><i class="ph ph-church text-xl"></i><?php endif; ?>
          </div>
          <div>
            <div class="text-sm font-bold text-parish-900"><?= html_escape($name) ?></div>
            <div class="text-xs text-gold-700 mt-1"><?= html_escape($p['position'] ?: 'Priest') ?></div>
          </div>
        </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>

  <section class="mb-12">
    <div class="heritage-kicker text-gold-600">Parish level</div>
    <h2 class="text-3xl font-bold text-parish-900 mt-2">Parish Officials</h2>
    <p class="text-gray-600 mt-2 max-w-3xl">Lay officials, council members and coordinators serving across the whole parish.</p>

    <?php if (!$schema_ready || empty($structure['parish_officials'])): ?>
      <div class="mt-6 rounded-2xl border border-dashed border-stonewarm-200 p-8 text-center text-sm text-gray-400">Parish officials are being prepared for publication.</div>
    <?php else: ?>
      <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4 mt-6">
        <?php foreach ($structure['parish_officials'] as $o): $photo=$photo_url($o['photo']); ?>
          <article class="rounded-2xl bg-white border border-stonewarm-200 p-5">
            <div class="w-14 h-14 rounded-2xl bg-parish-50 text-parish-700 overflow-hidden flex items-center justify-center">
              <?php if ($photo): ?><img src="<?= html_escape($photo) ?>" alt="" class="w-full h-full object-cover"><?php else: ?><i class="ph ph-user text-xl"></i><?php endif; ?>
            </div>
            <h3 class="font-bold text-gray-900 mt-4"><?= html_escape($o['full_name']) ?></h3>
            <div class="text-sm text-parish-700 mt-1"><?= html_escape($o['position_title']) ?></div>
            <?php if ($o['committee_area']): ?><div class="text-xs text-gray-400 mt-1"><?= html_escape($o['committee_area']) ?></div><?php endif; ?>
            <?php if ($o['bio']): ?><p class="text-xs text-gray-500 mt-3 leading-relaxed"><?= html_escape($o['bio']) ?></p><?php endif; ?>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>

  <section>
    <div class="heritage-kicker text-gold-600">Local communities</div>
    <h2 class="text-3xl font-bold text-parish-900 mt-2">Chapels & GSK Clusters</h2>
    <p class="text-gray-600 mt-2 max-w-3xl">The hierarchy below shows each chapel, its chapel officials, and the officials serving each GSK cluster beneath it.</p>

    <?php if (!$schema_ready || empty($structure['chapels'])): ?>
      <div class="mt-6 rounded-2xl border border-dashed border-stonewarm-200 p-10 text-center text-sm text-gray-400">No chapel/GSK organization has been published yet.</div>
    <?php else: ?>
      <div class="mt-7 space-y-6">
        <?php foreach ($structure['chapels'] as $chapel): ?>
          <article class="rounded-[2rem] bg-white border border-stonewarm-200 overflow-hidden">
            <div class="p-6 sm:p-8 bg-gradient-to-r from-parish-900 to-parish-800 text-white">
              <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                <div>
                  <?php if ($chapel['patron_saint']): ?><div class="text-[10px] uppercase tracking-[.14em] font-bold text-gold-200"><?= html_escape($chapel['patron_saint']) ?></div><?php endif; ?>
                  <h3 class="text-2xl sm:text-3xl font-bold mt-1"><?= html_escape($chapel['name']) ?></h3>
                  <div class="text-sm text-white/55 mt-2"><?= html_escape($chapel['barangay'] ?: $chapel['address'] ?: 'Alburquerque, Bohol') ?></div>
                </div>
                <a href="<?= site_url('chapels/'.$chapel['slug']) ?>" class="inline-flex self-start items-center gap-2 px-4 py-2.5 rounded-full bg-white text-parish-900 text-sm font-semibold">Chapel Details <i class="ph ph-arrow-right"></i></a>
              </div>
            </div>

            <div class="p-6 sm:p-8">
              <div class="grid lg:grid-cols-[.85fr_1.15fr] gap-8">
                <div>
                  <div class="text-xs uppercase tracking-wider text-gray-400 font-bold">Chapel Officials</div>
                  <?php if (empty($chapel['officials'])): ?>
                    <p class="text-sm text-gray-400 mt-3">No chapel officials published yet.</p>
                  <?php else: ?>
                    <div class="space-y-3 mt-4">
                      <?php foreach ($chapel['officials'] as $o): $photo=$photo_url($o['photo']); ?>
                        <div class="flex items-center gap-3 rounded-xl bg-gray-50/70 border border-gray-100 p-3">
                          <div class="w-10 h-10 rounded-lg bg-white border border-gray-100 text-parish-700 overflow-hidden flex items-center justify-center flex-shrink-0">
                            <?php if ($photo): ?><img src="<?= html_escape($photo) ?>" alt="" class="w-full h-full object-cover"><?php else: ?><i class="ph ph-user"></i><?php endif; ?>
                          </div>
                          <div class="min-w-0">
                            <div class="text-sm font-semibold text-gray-800"><?= html_escape($o['full_name']) ?></div>
                            <div class="text-[11px] text-gray-500"><?= html_escape($o['position_title']) ?><?= $o['committee_area'] ? ' · '.html_escape($o['committee_area']) : '' ?></div>
                          </div>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>
                </div>

                <div>
                  <div class="text-xs uppercase tracking-wider text-gray-400 font-bold">GSK Clusters</div>
                  <?php if (empty($chapel['clusters'])): ?>
                    <p class="text-sm text-gray-400 mt-3">No clusters published yet.</p>
                  <?php else: ?>
                    <div class="grid sm:grid-cols-2 gap-4 mt-4">
                      <?php foreach ($chapel['clusters'] as $cluster): ?>
                        <div class="rounded-2xl border border-parish-100 bg-parish-50/35 p-4">
                          <div class="flex items-start justify-between gap-2">
                            <div>
                              <div class="font-bold text-parish-900"><?= html_escape($cluster['name']) ?></div>
                              <?php if ($cluster['coverage_area']): ?><div class="text-[11px] text-gray-500 mt-1"><?= html_escape($cluster['coverage_area']) ?></div><?php endif; ?>
                            </div>
                            <i class="ph ph-tree-structure text-parish-600"></i>
                          </div>

                          <?php if (!empty($cluster['officials'])): ?>
                            <div class="mt-4 pt-3 border-t border-parish-100 space-y-2">
                              <?php foreach ($cluster['officials'] as $o): ?>
                                <div>
                                  <div class="text-xs font-semibold text-gray-800"><?= html_escape($o['full_name']) ?></div>
                                  <div class="text-[10px] text-gray-500"><?= html_escape($o['position_title']) ?><?= $o['committee_area'] ? ' · '.html_escape($o['committee_area']) : '' ?></div>
                                </div>
                              <?php endforeach; ?>
                            </div>
                          <?php else: ?>
                            <div class="mt-4 pt-3 border-t border-parish-100 text-[11px] text-gray-400">Officials not yet published.</div>
                          <?php endif; ?>
                        </div>
                      <?php endforeach; ?>
                    </div>
                  <?php endif; ?>
                </div>
              </div>
            </div>
          </article>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </section>
</section>

<section class="bg-parish-900 text-white">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 flex flex-col md:flex-row md:items-center md:justify-between gap-6">
    <div>
      <div class="heritage-kicker text-gold-300">Community directory</div>
      <h2 class="text-2xl sm:text-3xl font-bold mt-2">Find your chapel and local GSK community.</h2>
      <p class="text-white/60 mt-2">Visit the chapel directory for Mass schedules, chapel details and local cluster information.</p>
    </div>
    <a href="<?= site_url('chapels') ?>" class="inline-flex items-center gap-2 px-6 py-3 rounded-full bg-white text-parish-900 font-semibold hover:bg-gold-50">Browse Chapels <i class="ph ph-arrow-right"></i></a>
  </div>
</section>
