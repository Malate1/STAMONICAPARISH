<section class="max-w-md mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center">
  <?php if ($cert): ?>
    <?php
      $released = ($cert['status'] ?? '') === 'released';
      $ready = ($cert['status'] ?? '') === 'ready_for_release';
      $icon_class = $released ? 'bg-emerald-100 text-emerald-600' : ($ready ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-500');
      $title = $released ? 'Certificate Verified' : ($ready ? 'Certificate Prepared — Not Yet Released' : 'Certificate Record Found');
      $type_label = ($cert['certificate_type'] ?? '') === 'no_record'
        ? 'Certificate of No Record'
        : ucfirst((string)$cert['certificate_type']) . ' Certificate';
    ?>
    <div class="w-16 h-16 rounded-full <?= $icon_class ?> flex items-center justify-center text-3xl mx-auto">
      <i class="ph-fill <?= $released ? 'ph-seal-check' : ($ready ? 'ph-clock' : 'ph-info') ?>"></i>
    </div>
    <h1 class="text-2xl font-semibold text-gray-900 mt-5"><?= html_escape($title) ?></h1>
    <?php if (!$released): ?>
      <p class="text-sm text-gray-500 mt-2">This verification token exists in the parish system, but the certificate has not yet been recorded as released. Do not treat it as an issued certificate until the status below is <strong>Released</strong>.</p>
    <?php endif; ?>

    <div class="mt-8 text-left bg-gray-50 rounded-xl border border-gray-100 p-6 space-y-3 text-sm">
      <div class="flex justify-between gap-4"><span class="text-gray-400">Document</span><span class="font-medium text-gray-800 text-right"><?= html_escape($type_label) ?></span></div>
      <div class="flex justify-between gap-4"><span class="text-gray-400">Certificate No.</span><span class="font-medium text-gray-800 text-right"><?= html_escape($cert['request_code']) ?></span></div>
      <div class="flex justify-between gap-4"><span class="text-gray-400">Status</span><span class="font-medium text-right <?= $released ? 'text-emerald-600' : 'text-amber-600' ?>"><?= html_escape(strtoupper(str_replace('_',' ',$cert['status']))) ?></span></div>
      <div class="flex justify-between gap-4"><span class="text-gray-400">Released</span><span class="font-medium text-gray-800 text-right"><?= !empty($cert['released_at']) ? format_date($cert['released_at']) : '—' ?></span></div>
      <div class="flex justify-between gap-4"><span class="text-gray-400">Parish</span><span class="font-medium text-gray-800 text-right">Sta. Monica Parish Church</span></div>
    </div>
  <?php else: ?>
    <div class="w-16 h-16 rounded-full bg-red-100 text-red-600 flex items-center justify-center text-3xl mx-auto"><i class="ph-fill ph-x-circle"></i></div>
    <h1 class="text-2xl font-semibold text-gray-900 mt-5">Certificate Not Found</h1>
    <p class="text-gray-500 mt-2">This verification token does not match any certificate record in the parish system. It may be invalid or altered.</p>
  <?php endif; ?>
</section>
