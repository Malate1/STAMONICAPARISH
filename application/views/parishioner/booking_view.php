<div class="max-w-3xl mx-auto">
  <a href="<?= site_url('my/bookings') ?>" class="text-sm text-parish-700 hover:underline">← Back to My Bookings</a>

  <div class="bg-white rounded-2xl border border-gray-100 p-6 sm:p-8 mt-4">
    <div class="flex items-start justify-between flex-wrap gap-3">
      <div>
        <div class="text-xs text-gray-400"><?= html_escape($booking['booking_code']) ?></div>
        <h1 class="text-xl font-semibold text-gray-900 mt-1"><?= html_escape($booking['service_name']) ?></h1>
        <div class="text-sm text-gray-500 mt-1">Preferred: <?= format_date($booking['preferred_date']) ?><?= $booking['confirmed_date'] ? ' · Confirmed: ' . format_datetime($booking['confirmed_date']) : '' ?></div>
      </div>
      <span class="px-3 py-1.5 rounded-full text-xs font-medium <?= status_badge_class($booking['status']) ?>"><?= status_label($booking['status']) ?></span>
    </div>

    <?php if ($booking['status'] === 'missing_requirements' && $booking['rejection_reason']): ?>
    <div class="mt-5 p-4 rounded-lg bg-red-50 border border-red-100 text-sm text-red-700">
      <strong>Action needed:</strong> <?= html_escape($booking['rejection_reason']) ?>
    </div>
    <?php endif; ?>

    <?php if ($booking['status'] === 'awaiting_payment'): ?>
    <div class="mt-5 p-4 rounded-lg bg-orange-50 border border-orange-100 flex items-center justify-between">
      <div class="text-sm text-orange-800"><strong>Payment required:</strong> <?= peso($booking['fee_amount']) ?></div>
      <a href="<?= site_url('my/payments/pay/service_booking/' . $booking['id']) ?>" class="px-4 py-2 rounded-lg bg-orange-600 hover:bg-orange-700 text-white text-sm font-medium">Pay via GCash</a>
    </div>
    <?php elseif ($booking['status'] === 'payment_verification'): ?>
    <div class="mt-5 p-4 rounded-lg bg-blue-50 border border-blue-100 text-sm text-blue-700">
      Your payment proof was submitted and is being verified by the parish office.
    </div>
    <?php endif; ?>

    <?php if (!empty($documents)): ?>
    <div class="mt-6 pt-6 border-t border-gray-100">
      <h2 class="text-sm font-semibold text-gray-700 mb-3">Submitted Documents</h2>
      <div class="space-y-2">
        <?php foreach ($documents as $d): ?>
        <a href="<?= base_url($d['file_path']) ?>" target="_blank" class="flex items-center gap-3 p-3 rounded-lg border border-gray-100 hover:border-parish-300 text-sm">
          <i class="ph ph-file text-gray-400"></i>
          <span class="flex-1 text-gray-700"><?= html_escape($d['label'] ?: $d['original_name']) ?></span>
          <?php if ($d['verified']): ?><span class="text-xs text-emerald-600 font-medium">Verified</span><?php else: ?><span class="text-xs text-gray-400">Pending review</span><?php endif; ?>
        </a>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <div class="mt-6 pt-6 border-t border-gray-100">
      <h2 class="text-sm font-semibold text-gray-700 mb-4">Status Timeline</h2>
      <div class="space-y-4">
        <?php foreach ($history as $h): ?>
        <div class="flex gap-3">
          <div class="w-2 h-2 rounded-full bg-parish-600 mt-1.5 flex-shrink-0"></div>
          <div>
            <div class="text-sm text-gray-700"><?= status_label($h['to_status']) ?></div>
            <?php if ($h['remarks']): ?><div class="text-xs text-gray-400"><?= html_escape($h['remarks']) ?></div><?php endif; ?>
            <div class="text-xs text-gray-300 mt-0.5"><?= format_datetime($h['changed_at']) ?><?= $h['first_name'] ? ' · ' . html_escape($h['first_name'] . ' ' . $h['last_name']) : '' ?></div>
          </div>
        </div>
        <?php endforeach; ?>
      </div>
    </div>

    <?php if (!in_array($booking['status'], ['completed', 'cancelled', 'scheduled', 'approved'])): ?>
    <div class="mt-6 pt-6 border-t border-gray-100">
      <button onclick="cancelBooking(<?= $booking['id'] ?>)" class="text-sm text-red-600 hover:underline font-medium">Cancel this application</button>
    </div>
    <?php endif; ?>
  </div>
</div>

<script>
function cancelBooking(id){
  Swal.fire({
    icon: 'warning', title: 'Cancel Application?', text: 'This cannot be undone.',
    showCancelButton: true, confirmButtonText: 'Yes, cancel it', confirmButtonColor: '#dc2626', cancelButtonColor: '#6b7280'
  }).then(function(result){
    if(result.isConfirmed){
      $.post('<?= site_url('my/bookings/cancel/') ?>' + id, function(res){
        if(res.success){ toastr.success(res.message); setTimeout(function(){ location.reload(); }, 800); }
        else { toastr.error(res.message); }
      });
    }
  });
}
</script>
