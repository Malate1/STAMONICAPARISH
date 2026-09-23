<h1 class="text-2xl font-semibold text-gray-900 mb-1">My Payments</h1>
<p class="text-gray-500 text-sm mb-6">History of your GCash payments and verification status.</p>

<div class="bg-white rounded-2xl border border-gray-100 divide-y divide-gray-50">
  <?php if (empty($payments)): ?>
    <div class="text-center py-14 text-gray-400 text-sm">No payments submitted yet.</div>
  <?php endif; ?>
  <?php foreach ($payments as $p): ?>
  <div class="flex items-center justify-between p-5">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 rounded-lg bg-parish-50 text-parish-700 flex items-center justify-center text-lg"><i class="ph ph-credit-card"></i></div>
      <div>
        <div class="text-sm font-medium text-gray-800"><?= ucwords(str_replace('_', ' ', $p['payable_type'])) ?> — <?= peso($p['amount']) ?></div>
        <div class="text-xs text-gray-400"><?= html_escape($p['payment_code']) ?> · Ref: <?= html_escape($p['gcash_reference_no'] ?: '—') ?> · <?= format_date($p['created_at']) ?></div>
        <?php if ($p['receipt_no']): ?><div class="text-xs text-emerald-600 mt-0.5">OR: <?= html_escape($p['receipt_no']) ?></div><?php endif; ?>
      </div>
    </div>
    <span class="px-2.5 py-1 rounded-full text-xs font-medium <?= status_badge_class($p['status']) ?>"><?= status_label($p['status']) ?></span>
  </div>
  <?php endforeach; ?>
</div>
