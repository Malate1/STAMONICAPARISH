<?php
$hero_img = 'https://upload.wikimedia.org/wikipedia/commons/c/c8/Santa_Monica_Church_Alburquerque_with_convent_%28Tagbilaran_East_Road%2C_Alburquerque%2C_Bohol%3B_01-12-2023%29.jpg';
?>
<article>
  <section class="relative overflow-hidden bg-parish-900 min-h-[300px] flex items-end">
    <img src="<?= $hero_img ?>" alt="" class="absolute inset-0 w-full h-full object-cover opacity-35">
    <div class="absolute inset-0 bg-gradient-to-r from-parish-900 via-parish-900/85 to-parish-900/45"></div>
    <div class="relative max-w-5xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-12 sm:py-14 text-white">
      <a href="<?= site_url('announcements') ?>" class="inline-flex items-center gap-2 text-sm text-white/65 hover:text-white transition"><i class="ph ph-arrow-left"></i> Back to Announcements</a>
      <div class="mt-6 flex flex-wrap items-center gap-2">
        <?php if ($item['is_pinned']): ?><span class="inline-flex items-center gap-1 rounded-full bg-gold-400 text-parish-900 px-3 py-1 text-[10px] font-bold uppercase tracking-wider"><i class="ph-fill ph-push-pin"></i> Pinned</span><?php endif; ?>
        <span class="text-[11px] text-gold-200 font-semibold uppercase tracking-wider"><?= ucfirst(str_replace('_',' ',$item['category'])) ?></span>
      </div>
      <h1 class="text-3xl sm:text-5xl font-bold tracking-tight leading-tight mt-3 max-w-4xl"><?= html_escape($item['title']) ?></h1>
      <div class="text-sm text-white/55 mt-4 flex items-center gap-2"><i class="ph ph-calendar-blank"></i><?= format_datetime($item['publish_date']) ?></div>
    </div>
  </section>

  <section class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-16">
    <div class="rounded-[2rem] bg-white border border-stonewarm-200 shadow-soft p-7 sm:p-10 lg:p-12">
      <div class="prose prose-sm sm:prose max-w-none text-gray-700 leading-relaxed whitespace-pre-line">
        <?= nl2br(html_escape($item['body'])) ?>
      </div>
    </div>

    <div class="mt-8 flex flex-col sm:flex-row gap-3 sm:items-center sm:justify-between">
      <a href="<?= site_url('announcements') ?>" class="inline-flex items-center gap-2 font-semibold text-parish-700 hover:text-parish-900"><i class="ph ph-arrow-left"></i> More Announcements</a>
      <a href="<?= site_url('contact') ?>" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-parish-700">Questions about this notice? Contact the parish office <i class="ph ph-arrow-right"></i></a>
    </div>
  </section>
</article>
