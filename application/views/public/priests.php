<section class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-14">
  <h1 class="text-3xl font-semibold text-gray-900 mb-10">Our Priests</h1>
  <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
    <?php if (empty($priests)): ?>
      <p class="text-gray-400">No priest profiles published yet.</p>
    <?php endif; ?>
    <?php foreach ($priests as $p): if (isset($p['is_public']) && !$p['is_public']) continue; ?>
    <div class="p-6 rounded-2xl border border-gray-100 text-center">
      <div class="w-16 h-16 rounded-full bg-gold-500 text-white flex items-center justify-center text-xl font-semibold mx-auto"><?= initials($p['first_name'] . ' ' . $p['last_name']) ?></div>
      <h3 class="font-semibold text-gray-800 mt-4"><?= html_escape(($p['title'] ?: 'Rev. Fr.') . ' ' . $p['first_name'] . ' ' . $p['last_name']) ?></h3>
      <div class="text-xs text-gold-600 uppercase tracking-wide mt-1"><?= html_escape($p['position'] ?: 'Priest') ?></div>
      <?php if (!empty($p['bio'])): ?><p class="text-sm text-gray-500 mt-3"><?= html_escape($p['bio']) ?></p><?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>
</section>
