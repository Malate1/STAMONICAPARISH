<div class="max-w-2xl mx-auto">
  <a href="<?= site_url('my/certificates') ?>" class="text-sm text-parish-700 hover:underline">← Back to Certificates</a>

  <div class="bg-white rounded-2xl border border-gray-100 p-6 sm:p-8 mt-4">
    <div class="flex items-start justify-between flex-wrap gap-3">
      <div>
        <div class="text-xs text-gray-400"><?= html_escape($cert['request_code']) ?></div>
        <h1 class="text-xl font-semibold text-gray-900 mt-1"><?= ucfirst($cert['certificate_type']) ?> Certificate</h1>
        <div class="text-sm text-gray-500 mt-1">Purpose: <?= html_escape($cert['purpose']) ?> · <?= $cert['number_of_copies'] ?> cop<?= $cert['number_of_copies'] > 1 ? 'ies' : 'y' ?></div>
      </div>
      <span class="px-3 py-1.5 rounded-full text-xs font-medium <?= status_badge_class($cert['status']) ?>"><?= status_label($cert['status']) ?></span>
    </div>

    <?php if ($cert['status'] === 'awaiting_payment'): ?>
    <div class="mt-5 p-4 rounded-lg bg-orange-50 border border-orange-100 flex items-center justify-between flex-wrap gap-3">
      <div class="text-sm text-orange-800"><strong>Payment required:</strong> <?= peso($cert['fee_amount']) ?></div>
      <a href="<?= site_url('my/payments/pay/certificate_request/' . $cert['id']) ?>" class="px-4 py-2 rounded-lg bg-orange-600 hover:bg-orange-700 text-white text-sm font-medium">Pay via GCash</a>
    </div>
    <?php elseif ($cert['status'] === 'payment_verification'): ?>
    <div class="mt-5 p-4 rounded-lg bg-blue-50 border border-blue-100 text-sm text-blue-700">Your payment is being verified by the parish office.</div>
    <?php elseif ($cert['status'] === 'no_record_found'): ?>
    <div class="mt-5 p-4 rounded-lg bg-red-50 border border-red-100 text-sm text-red-700">No matching record was found in the parish registry. Please visit the parish office for assistance.</div>
    <?php elseif ($cert['status'] === 'ready_for_release'): ?>
    <div class="mt-5 p-4 rounded-lg bg-emerald-50 border border-emerald-100 text-sm text-emerald-700">Your certificate is ready! Please visit the parish office to claim it.</div>
    <?php elseif ($cert['status'] === 'released'): ?>
    <div class="mt-5 p-4 rounded-lg bg-emerald-50 border border-emerald-100 text-sm text-emerald-700">
      Released on <?= format_date($cert['released_at']) ?>.
      <?php if ($cert['qr_code_token']): ?><div class="mt-1"><a href="<?= site_url('verify/' . $cert['qr_code_token']) ?>" target="_blank" class="underline font-medium">View verification page →</a></div><?php endif; ?>
    </div>
    <?php endif; ?>

    <?php if ($cert['record_name']): ?>
    <div class="mt-6 pt-6 border-t border-gray-100">
      <h2 class="text-sm font-semibold text-gray-700 mb-2">Matched Record</h2>
      <div class="text-sm text-gray-600"><?= html_escape($cert['record_name']) ?> · <?= format_date($cert['sacrament_date']) ?></div>
      <div class="text-xs text-gray-400 mt-1">Book <?= html_escape($cert['registry_book'] ?: '—') ?>, Page <?= html_escape($cert['registry_page'] ?: '—') ?></div>
    </div>
    <?php endif; ?>
  </div>
</div>
