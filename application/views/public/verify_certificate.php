<section class="max-w-md mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center">
  <?php if ($cert): ?>
    <div class="w-16 h-16 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-3xl mx-auto"><i class="ph-fill ph-seal-check"></i></div>
    <h1 class="text-2xl font-semibold text-gray-900 mt-5">Certificate Verified</h1>
    <div class="mt-8 text-left bg-gray-50 rounded-xl border border-gray-100 p-6 space-y-3 text-sm">
      <div class="flex justify-between"><span class="text-gray-400">Document</span><span class="font-medium text-gray-800"><?= ucfirst($cert['certificate_type']) ?> Certificate</span></div>
      <div class="flex justify-between"><span class="text-gray-400">Certificate No.</span><span class="font-medium text-gray-800"><?= html_escape($cert['request_code']) ?></span></div>
      <div class="flex justify-between"><span class="text-gray-400">Status</span><span class="font-medium <?= $cert['status'] === 'released' ? 'text-emerald-600' : 'text-amber-600' ?>"><?= strtoupper(str_replace('_',' ',$cert['status'])) ?></span></div>
      <div class="flex justify-between"><span class="text-gray-400">Issued</span><span class="font-medium text-gray-800"><?= $cert['released_at'] ? format_date($cert['released_at']) : '—' ?></span></div>
      <div class="flex justify-between"><span class="text-gray-400">Issued by</span><span class="font-medium text-gray-800">Sta. Monica Parish Church</span></div>
    </div>
  <?php else: ?>
    <div class="w-16 h-16 rounded-full bg-red-100 text-red-600 flex items-center justify-center text-3xl mx-auto"><i class="ph-fill ph-x-circle"></i></div>
    <h1 class="text-2xl font-semibold text-gray-900 mt-5">Certificate Not Found</h1>
    <p class="text-gray-500 mt-2">This QR code does not match any certificate on record. It may be invalid or altered.</p>
  <?php endif; ?>
</section>
