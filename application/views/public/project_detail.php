<?php
$fallback_img = 'https://upload.wikimedia.org/wikipedia/commons/d/d4/Santa_Monica_Church_Alburquerque_%28Tagbilaran_East_Road%2C_Alburquerque%2C_Bohol%3B_01-12-2023%29.jpg';
$cover = !empty($item['cover_image']) ? base_url($item['cover_image']) : $fallback_img;
$can_donate = $item['status'] === 'active';
$is_parishioner = !empty($current_user) && defined('ROLE_PARISHIONER') && (int)$current_user['role_id'] === (int)ROLE_PARISHIONER;
?>

<article>
  <section class="relative overflow-hidden bg-parish-900 min-h-[430px] flex items-end">
    <img src="<?= html_escape($cover) ?>" alt="<?= html_escape($item['title']) ?>" class="absolute inset-0 w-full h-full object-cover">
    <div class="absolute inset-0 bg-gradient-to-r from-parish-900/96 via-parish-900/82 to-black/25"></div>

    <div class="relative max-w-6xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-12 sm:py-16 text-white">
      <a href="<?= site_url('projects') ?>" class="inline-flex items-center gap-2 text-sm text-white/65 hover:text-white transition"><i class="ph ph-arrow-left"></i> Back to Projects</a>

      <div class="mt-8 max-w-3xl">
        <div class="flex flex-wrap items-center gap-2">
          <?php if (!empty($item['category'])): ?><span class="text-[11px] uppercase tracking-[.16em] text-gold-200 font-bold"><?= html_escape($item['category']) ?></span><?php endif; ?>
          <?php if (!empty($item['is_featured'])): ?><span class="px-2.5 py-1 rounded-full bg-gold-400 text-parish-950 text-[9px] uppercase tracking-wider font-bold">Featured Project</span><?php endif; ?>
          <?php if ($item['status']==='closed'): ?><span class="px-2.5 py-1 rounded-full bg-white/15 text-white text-[9px] uppercase tracking-wider font-bold">Completed / Closed</span><?php endif; ?>
        </div>

        <h1 class="text-4xl sm:text-5xl lg:text-6xl font-bold tracking-[-.03em] leading-[1.02] mt-3"><?= html_escape($item['title']) ?></h1>
        <?php if (!empty($item['short_description'])): ?><p class="text-lg text-white/70 mt-5 leading-relaxed"><?= html_escape($item['short_description']) ?></p><?php endif; ?>

        <div class="mt-6 flex flex-wrap gap-x-5 gap-y-2 text-sm text-white/60">
          <?php if (!empty($item['location'])): ?><span class="inline-flex items-center gap-2"><i class="ph ph-map-pin"></i><?= html_escape($item['location']) ?></span><?php endif; ?>
          <?php if (!empty($item['start_date'])): ?><span class="inline-flex items-center gap-2"><i class="ph ph-calendar-blank"></i>Started <?= format_date($item['start_date']) ?></span><?php endif; ?>
          <?php if (!empty($item['target_date'])): ?><span class="inline-flex items-center gap-2"><i class="ph ph-flag"></i>Target <?= format_date($item['target_date']) ?></span><?php endif; ?>
        </div>
      </div>
    </div>
  </section>

  <section class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-16">
    <div class="grid lg:grid-cols-[1fr_.42fr] gap-8 lg:gap-10 items-start">
      <div class="space-y-8">
        <section class="rounded-[2rem] bg-white border border-stonewarm-200 p-7 sm:p-10 shadow-soft">
          <div class="heritage-kicker text-gold-600">About the project</div>
          <h2 class="text-2xl sm:text-3xl font-bold text-parish-900 mt-2">Why this project matters</h2>
          <div class="mt-5 text-gray-700 leading-relaxed whitespace-pre-line"><?= nl2br(html_escape($item['description'] ?: $item['short_description'])) ?></div>
        </section>

        <section class="rounded-[2rem] bg-parish-50 border border-parish-100 p-7 sm:p-9">
          <div class="grid sm:grid-cols-3 gap-5">
            <div>
              <div class="text-[10px] uppercase tracking-wider text-gray-400 font-bold">Verified gifts</div>
              <div class="text-2xl font-bold text-parish-900 mt-1"><?= (int)$item['donor_count'] ?></div>
            </div>
            <div>
              <div class="text-[10px] uppercase tracking-wider text-gray-400 font-bold">Raised</div>
              <div class="text-2xl font-bold text-parish-900 mt-1"><?= peso($item['raised_amount']) ?></div>
            </div>
            <div>
              <div class="text-[10px] uppercase tracking-wider text-gray-400 font-bold">Status</div>
              <div class="text-lg font-bold text-parish-900 mt-1"><?= $item['status']==='active' ? 'Open for Support' : 'Completed / Closed' ?></div>
            </div>
          </div>
        </section>
      </div>

      <aside class="lg:sticky lg:top-28 space-y-5">
        <div class="rounded-[2rem] bg-parish-900 text-white p-7 shadow-heritage">
          <div class="heritage-kicker text-gold-300">Project funding</div>

          <?php if ((float)$item['goal_amount'] > 0): ?>
            <div class="mt-4">
              <div class="text-3xl font-bold"><?= peso($item['raised_amount']) ?></div>
              <div class="text-sm text-white/50 mt-1">raised of <?= peso($item['goal_amount']) ?> goal</div>
            </div>
            <div class="h-3 rounded-full bg-white/10 overflow-hidden mt-5">
              <div class="h-full rounded-full bg-gold-400" style="width:<?= min(100,(float)$item['progress_percent']) ?>%"></div>
            </div>
            <div class="flex items-center justify-between text-xs text-white/50 mt-2">
              <span><?= number_format((float)$item['progress_percent'],1) ?>% funded</span>
              <span><?= peso($item['remaining_amount']) ?> remaining</span>
            </div>
          <?php else: ?>
            <div class="text-3xl font-bold mt-4"><?= peso($item['raised_amount']) ?></div>
            <div class="text-sm text-white/50 mt-1">verified support received</div>
          <?php endif; ?>

          <?php if ($can_donate): ?>
            <?php if ($is_parishioner): ?>
              <a href="<?= site_url('my/donations/new/' . $item['id']) ?>" class="mt-7 w-full inline-flex items-center justify-center gap-2 px-5 py-3.5 rounded-xl bg-gold-500 hover:bg-gold-600 text-white font-semibold transition"><i class="ph ph-heart"></i> Donate to This Project</a>
            <?php else: ?>
              <a href="<?= site_url('login') ?>" class="mt-7 w-full inline-flex items-center justify-center gap-2 px-5 py-3.5 rounded-xl bg-gold-500 hover:bg-gold-600 text-white font-semibold transition"><i class="ph ph-sign-in"></i> Log In to Donate</a>
              <p class="text-[11px] text-white/45 mt-3 text-center">Sign in or create a parishioner account to submit a GCash donation for verification.</p>
            <?php endif; ?>
          <?php else: ?>
            <div class="mt-7 rounded-xl bg-white/10 border border-white/10 p-4 text-sm text-white/70">This project is no longer accepting donations. Thank you to everyone who supported it.</div>
          <?php endif; ?>
        </div>

        <div class="rounded-2xl bg-white border border-stonewarm-200 p-5">
          <div class="font-semibold text-gray-800">Prefer a general parish gift?</div>
          <p class="text-xs text-gray-500 mt-2 leading-relaxed">You can give to the General Parish Fund instead of selecting a specific project.</p>
          <a href="<?= site_url('donate') ?>" class="mt-4 inline-flex items-center gap-2 text-sm font-semibold text-parish-700 hover:underline">View donation options <i class="ph ph-arrow-right"></i></a>
        </div>
      </aside>
    </div>
  </section>
</article>
