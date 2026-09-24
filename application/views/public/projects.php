<?php
$fallback_img = 'https://upload.wikimedia.org/wikipedia/commons/d/d4/Santa_Monica_Church_Alburquerque_%28Tagbilaran_East_Road%2C_Alburquerque%2C_Bohol%3B_01-12-2023%29.jpg';
?>

<section class="relative overflow-hidden bg-parish-900 min-h-[360px] flex items-end">
  <img src="<?= $fallback_img ?>" alt="Sta. Monica Parish Church" class="absolute inset-0 w-full h-full object-cover opacity-45">
  <div class="absolute inset-0 bg-gradient-to-r from-parish-900 via-parish-900/90 to-parish-900/45"></div>
  <div class="relative max-w-7xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-14 sm:py-16 text-white">
    <div class="heritage-kicker text-gold-200">Building together</div>
    <h1 class="text-4xl sm:text-5xl font-bold tracking-tight mt-3">Parish Projects</h1>
    <p class="text-white/70 mt-3 max-w-2xl text-lg">See the projects, improvements and community initiatives Sta. Monica Parish is working toward—and how every verified gift helps move them forward.</p>
  </div>
</section>

<?php if (!empty($featured_project)): 
  $fp_img = !empty($featured_project['cover_image']) ? base_url($featured_project['cover_image']) : $fallback_img;
?>
<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-12 sm:pt-16">
  <a href="<?= site_url('projects/' . $featured_project['slug']) ?>" class="group block rounded-[2rem] overflow-hidden bg-parish-900 text-white shadow-heritage">
    <div class="grid lg:grid-cols-[1.08fr_.92fr] min-h-[430px]">
      <div class="relative min-h-[300px] lg:min-h-0 overflow-hidden">
        <img src="<?= html_escape($fp_img) ?>" alt="<?= html_escape($featured_project['title']) ?>" class="absolute inset-0 w-full h-full object-cover transition duration-700 group-hover:scale-[1.03]">
        <div class="absolute inset-0 bg-gradient-to-t from-black/35 to-transparent lg:bg-gradient-to-r lg:from-transparent lg:to-parish-900/30"></div>
      </div>

      <div class="p-7 sm:p-10 lg:p-12 flex flex-col justify-center">
        <div class="inline-flex self-start items-center gap-2 px-3 py-1.5 rounded-full bg-white/10 border border-white/10 text-[10px] uppercase tracking-[.15em] font-bold text-gold-200">
          <i class="ph ph-star"></i> Featured Parish Project
        </div>
        <?php if (!empty($featured_project['category'])): ?>
          <div class="text-[11px] uppercase tracking-[.15em] text-white/45 font-semibold mt-5"><?= html_escape($featured_project['category']) ?></div>
        <?php endif; ?>
        <h2 class="text-3xl sm:text-4xl font-bold mt-2 leading-tight"><?= html_escape($featured_project['title']) ?></h2>
        <p class="text-white/65 leading-relaxed mt-4"><?= html_escape($featured_project['short_description'] ?: mb_strimwidth(strip_tags($featured_project['description']),0,220,'…')) ?></p>

        <?php if ((float)$featured_project['goal_amount'] > 0): ?>
          <div class="mt-7">
            <div class="flex items-center justify-between gap-4 text-sm">
              <span class="font-semibold"><?= peso($featured_project['raised_amount']) ?> raised</span>
              <span class="text-white/50">Goal <?= peso($featured_project['goal_amount']) ?></span>
            </div>
            <div class="mt-2 h-2.5 rounded-full bg-white/10 overflow-hidden">
              <div class="h-full rounded-full bg-gold-400" style="width:<?= min(100,(float)$featured_project['progress_percent']) ?>%"></div>
            </div>
            <div class="text-[11px] text-white/45 mt-2"><?= number_format((float)$featured_project['progress_percent'],1) ?>% funded · <?= (int)$featured_project['donor_count'] ?> verified gift<?= (int)$featured_project['donor_count']===1?'':'s' ?></div>
          </div>
        <?php endif; ?>

        <div class="mt-7 inline-flex items-center gap-2 font-semibold text-gold-200 group-hover:text-white transition">View project details <i class="ph ph-arrow-right"></i></div>
      </div>
    </div>
  </a>
</section>
<?php endif; ?>

<section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-18">
  <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-7">
    <div>
      <div class="heritage-kicker text-gold-600">Current & completed work</div>
      <h2 class="text-3xl font-bold text-parish-900 mt-2">Projects our parish community can follow</h2>
    </div>
    <a href="<?= site_url('donate') ?>" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-full bg-parish-50 text-parish-700 text-sm font-semibold hover:bg-parish-100 transition"><i class="ph ph-heart"></i> Donation Options</a>
  </div>

  <?php if (empty($projects)): ?>
    <div class="rounded-[2rem] bg-white border border-stonewarm-200 p-10 sm:p-14 text-center">
      <i class="ph ph-folder-open text-4xl text-gray-300"></i>
      <h3 class="font-semibold text-gray-800 mt-4">No parish projects have been published yet.</h3>
      <p class="text-sm text-gray-500 mt-2">Please check again for future restoration, ministry and community initiatives.</p>
    </div>
  <?php else: ?>
    <div class="grid md:grid-cols-2 xl:grid-cols-3 gap-6">
      <?php foreach ($projects as $p):
        $img = !empty($p['cover_image']) ? base_url($p['cover_image']) : $fallback_img;
      ?>
      <a href="<?= site_url('projects/' . $p['slug']) ?>" class="group rounded-3xl overflow-hidden bg-white border border-stonewarm-200 hover:border-parish-200 hover:shadow-heritage transition duration-300">
        <div class="relative h-56 overflow-hidden bg-parish-900">
          <img src="<?= html_escape($img) ?>" alt="<?= html_escape($p['title']) ?>" class="absolute inset-0 w-full h-full object-cover group-hover:scale-105 transition duration-700">
          <div class="absolute inset-0 bg-gradient-to-t from-black/65 via-transparent to-transparent"></div>
          <div class="absolute top-4 left-4 flex gap-2">
            <?php if ($p['status']==='closed'): ?><span class="px-2.5 py-1 rounded-full bg-white/90 text-gray-700 text-[10px] font-bold uppercase tracking-wide">Completed / Closed</span><?php endif; ?>
            <?php if (!empty($p['is_featured'])): ?><span class="px-2.5 py-1 rounded-full bg-gold-400 text-parish-950 text-[10px] font-bold uppercase tracking-wide">Featured</span><?php endif; ?>
          </div>
          <?php if (!empty($p['category'])): ?><div class="absolute bottom-4 left-4 text-[10px] uppercase tracking-[.14em] text-gold-200 font-bold"><?= html_escape($p['category']) ?></div><?php endif; ?>
        </div>

        <div class="p-6">
          <h3 class="text-xl font-bold text-parish-900 group-hover:text-parish-700 transition"><?= html_escape($p['title']) ?></h3>
          <p class="text-sm text-gray-500 leading-relaxed mt-2 line-clamp-3"><?= html_escape($p['short_description'] ?: mb_strimwidth(strip_tags($p['description']),0,150,'…')) ?></p>

          <?php if ((float)$p['goal_amount'] > 0): ?>
            <div class="mt-5">
              <div class="flex justify-between gap-3 text-xs"><span class="font-semibold text-gray-700"><?= peso($p['raised_amount']) ?></span><span class="text-gray-400">of <?= peso($p['goal_amount']) ?></span></div>
              <div class="h-2 rounded-full bg-gray-100 overflow-hidden mt-2"><div class="h-full bg-parish-600 rounded-full" style="width:<?= min(100,(float)$p['progress_percent']) ?>%"></div></div>
              <div class="text-[10px] text-gray-400 mt-1.5"><?= number_format((float)$p['progress_percent'],1) ?>% funded</div>
            </div>
          <?php endif; ?>

          <div class="mt-5 pt-4 border-t border-stonewarm-100 flex items-center justify-between text-xs">
            <span class="text-gray-400"><?= !empty($p['target_date']) ? 'Target ' . format_date($p['target_date']) : 'Ongoing parish project' ?></span>
            <span class="font-semibold text-parish-700">View <i class="ph ph-arrow-right ml-1"></i></span>
          </div>
        </div>
      </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</section>

<section class="bg-parish-50 border-y border-parish-100">
  <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-14 text-center">
    <i class="ph ph-hand-heart text-4xl text-parish-700"></i>
    <h2 class="text-2xl sm:text-3xl font-bold text-parish-900 mt-3">Support what matters most to the parish</h2>
    <p class="text-gray-600 mt-3 max-w-2xl mx-auto">Choose a specific project or give to the General Parish Fund. Verified donations are reflected in project progress so supporters can see the community moving forward together.</p>
    <a href="<?= site_url('donate') ?>" class="mt-6 inline-flex items-center gap-2 px-6 py-3 rounded-full bg-parish-800 hover:bg-parish-900 text-white font-semibold transition">Ways to Give <i class="ph ph-arrow-right"></i></a>
  </div>
</section>
