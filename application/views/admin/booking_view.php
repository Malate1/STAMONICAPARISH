<?php $base = strpos(uri_string(), 'staff') === 0 ? 'staff' : 'admin'; ?>
<a href="<?= site_url($base . '/booking') ?>" class="text-sm text-parish-700 hover:underline">← Back to Bookings</a>

<div class="grid lg:grid-cols-3 gap-6 mt-4">
  <div class="lg:col-span-2 space-y-6">
    <div class="bg-white rounded-2xl border border-gray-100 p-6">
      <div class="flex items-start justify-between flex-wrap gap-3">
        <div>
          <div class="text-xs text-gray-400"><?= html_escape($booking['booking_code']) ?></div>
          <h1 class="text-xl font-semibold text-gray-900 mt-1"><?= html_escape($booking['service_name']) ?></h1>
          <div class="text-sm text-gray-500 mt-1"><?= html_escape($booking['first_name'] . ' ' . $booking['last_name']) ?> · <?= html_escape($booking['email']) ?> · <?= html_escape($booking['mobile_number']) ?></div>
          <div class="mt-3 flex flex-wrap gap-2 text-xs">
            <span class="px-2.5 py-1 rounded-full <?= ($booking['booking_type'] ?? 'special') === 'regular' ? 'bg-emerald-50 text-emerald-700' : 'bg-gold-50 text-gold-700' ?> font-medium"><?= ucfirst($booking['booking_type'] ?? 'special') ?> Booking</span>
            <?php if ($booking['confirmed_date']): ?><span class="px-2.5 py-1 rounded-full bg-parish-50 text-parish-700 font-medium"><i class="ph ph-calendar-check mr-1"></i><?= format_datetime($booking['confirmed_date']) ?></span><?php endif; ?>
            <span class="px-2.5 py-1 rounded-full bg-gray-50 text-gray-600 font-medium">Fee: <?= (float) $booking['fee_amount'] === 0.0 ? 'FREE' : peso($booking['fee_amount']) ?></span>
            <?php if (!empty($booking['requires_priest'])): ?><span class="px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 font-medium"><i class="ph ph-users mr-1"></i>Priest required</span><?php endif; ?>
          </div>
        </div>
        <span class="px-3 py-1.5 rounded-full text-xs font-medium <?= status_badge_class($booking['status']) ?>"><?= status_label($booking['status']) ?></span>
      </div>

      <?php if ($booking['confirmed_date'] && !empty($booking['uses_main_church'])):
        $ceremony_ts = strtotime($booking['confirmed_date']);
        $protected_start = $ceremony_ts - ((int)($booking['booking_buffer_before_minutes'] ?? 0) * 60);
        $protected_end = $ceremony_ts + (((int)($booking['duration_minutes'] ?? 60) + (int)($booking['booking_buffer_minutes'] ?? 0)) * 60);
      ?>
      <div class="mt-5 rounded-xl border border-blue-100 bg-blue-50/50 p-4 text-xs text-blue-900">
        <div class="font-semibold"><i class="ph ph-shield-check mr-1"></i>Main Church protected window</div>
        <div class="mt-1"><?= date('g:i A', $protected_start) ?> – <?= date('g:i A', $protected_end) ?> · ceremony starts <?= date('g:i A', $ceremony_ts) ?></div>
        <div class="text-blue-800/70 mt-1">Another Main Church service is blocked if its preparation, ceremony, or clearance time overlaps this window.</div>
      </div>
      <?php endif; ?>

      <?php if (!empty($booking['details'])): $details = json_decode($booking['details'], true); ?>
      <div class="mt-5 pt-5 border-t border-gray-100 grid sm:grid-cols-2 gap-3">
        <?php foreach ($details as $k => $v): if (empty($v)) continue; ?>
        <div>
          <div class="text-xs text-gray-400"><?= ucwords(str_replace('_', ' ', $k)) ?></div>
          <div class="text-sm text-gray-700"><?= html_escape(is_array($v) ? implode(', ', $v) : $v) ?></div>
        </div>
        <?php endforeach; ?>
      </div>
      <?php endif; ?>
    </div>

    <?php if (!empty($documents)): ?>
    <div class="bg-white rounded-2xl border border-gray-100 p-6">
      <h2 class="text-sm font-semibold text-gray-700 mb-3">Submitted Requirements</h2>
      <div class="space-y-2">
        <?php foreach ($documents as $d): ?>
        <div class="flex items-center gap-3 p-3 rounded-lg border border-gray-100">
          <i class="ph ph-file text-gray-400"></i>
          <a href="<?= base_url($d['file_path']) ?>" target="_blank" class="flex-1 text-sm text-gray-700 hover:underline"><?= html_escape($d['label'] ?: $d['original_name']) ?></a>
          <?php if ($d['verified']): ?>
            <span class="text-xs text-emerald-600 font-medium">Verified</span>
          <?php else: ?>
            <button onclick="verifyDocument(<?= $d['id'] ?>, this)" class="text-xs text-parish-700 hover:underline font-medium">Mark Verified</button>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
      </div>
    </div>
    <?php endif; ?>

    <div class="bg-white rounded-2xl border border-gray-100 p-6">
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
  </div>

  <div class="space-y-6">
    <div class="bg-white rounded-2xl border border-gray-100 p-6">
      <h2 class="text-sm font-semibold text-gray-700 mb-4">Update Status</h2>
      <div class="grid grid-cols-1 gap-2">
        <button onclick="changeStatus('under_review')" class="px-3 py-2 rounded-lg bg-amber-50 text-amber-700 text-sm font-medium hover:bg-amber-100">Mark Under Review</button>
        <button onclick="promptMissingReq()" class="px-3 py-2 rounded-lg bg-red-50 text-red-700 text-sm font-medium hover:bg-red-100">Request Missing Requirements</button>
        <button onclick="changeStatus('requirements_complete')" class="px-3 py-2 rounded-lg bg-teal-50 text-teal-700 text-sm font-medium hover:bg-teal-100">Requirements Complete</button>
        <?php if ($booking['service_key'] === 'wedding'): ?>
        <button onclick="changeStatus('interview_processing')" class="px-3 py-2 rounded-lg bg-amber-50 text-amber-700 text-sm font-medium hover:bg-amber-100">Interview / Canonical Processing</button>
        <button onclick="changeStatus('priest_review')" class="px-3 py-2 rounded-lg bg-indigo-50 text-indigo-700 text-sm font-medium hover:bg-indigo-100">Send for Priest Review</button>
        <?php endif; ?>
        <?php if ((float) $booking['fee_amount'] > 0): ?>
        <button onclick="changeStatus('awaiting_payment')" class="px-3 py-2 rounded-lg bg-orange-50 text-orange-700 text-sm font-medium hover:bg-orange-100">Move to Awaiting Payment</button>
        <?php endif; ?>
        <button onclick="changeStatus('completed')" class="px-3 py-2 rounded-lg bg-green-50 text-green-700 text-sm font-medium hover:bg-green-100">Mark Completed</button>
        <button onclick="changeStatus('cancelled')" class="px-3 py-2 rounded-lg bg-gray-100 text-gray-600 text-sm font-medium hover:bg-gray-200">Cancel Application</button>
      </div>
    </div>

    <div class="bg-white rounded-2xl border border-gray-100 p-6">
      <h2 class="text-sm font-semibold text-gray-700 mb-4">Assign Priest &amp; Schedule</h2>
      <form id="assign-form" class="space-y-3">
        <select name="priest_id" class="w-full px-3 py-2.5 rounded-lg border border-gray-200 text-sm">
          <option value="">— Select Priest —</option>
          <?php foreach ($priests as $p): ?>
            <option value="<?= $p['id'] ?>" <?= $booking['assigned_priest_id'] == $p['id'] ? 'selected' : '' ?>><?= html_escape($p['first_name'] . ' ' . $p['last_name']) ?></option>
          <?php endforeach; ?>
        </select>
        <div>
          <label class="text-[11px] font-medium text-gray-500">Reserved Schedule</label>
          <input type="datetime-local" name="confirmed_date" value="<?= $booking['confirmed_date'] ? date('Y-m-d\TH:i', strtotime($booking['confirmed_date'])) : '' ?>" class="mt-1 w-full px-3 py-2.5 rounded-lg border border-gray-200 text-sm">
          <p class="text-[11px] text-gray-400 mt-1">Changing this time re-checks the full protected Main Church window (preparation + ceremony + clearance) and the selected priest’s availability.</p>
        </div>
        <button type="submit" class="w-full py-2.5 rounded-lg bg-parish-700 hover:bg-parish-800 text-white text-sm font-medium">Save Assignment</button>
      </form>
    </div>

    <?php if ($payment): ?>
    <div class="bg-white rounded-2xl border border-gray-100 p-6">
      <h2 class="text-sm font-semibold text-gray-700 mb-3">Payment</h2>
      <div class="text-sm text-gray-600"><?= peso($payment['amount']) ?> — <span class="px-2 py-0.5 rounded-full text-xs font-medium <?= status_badge_class($payment['status']) ?>"><?= status_label($payment['status']) ?></span></div>
      <div class="text-xs text-gray-400 mt-1">Ref: <?= html_escape($payment['gcash_reference_no'] ?: '—') ?></div>
      <a href="<?= site_url($base . '/payment') ?>" class="text-xs text-parish-700 hover:underline font-medium mt-2 inline-block">Manage in Payments →</a>
    </div>
    <?php endif; ?>
  </div>
</div>

<script>
function changeStatus(status, remarks){
  $.post('<?= site_url($base . '/booking/update_status') ?>', { id: <?= $booking['id'] ?>, status: status, remarks: remarks || '' }, function(res){
    if(res.success){ toastr.success(res.message); setTimeout(function(){ location.reload(); }, 700); } else { toastr.error(res.message); }
  });
}
function promptMissingReq(){
  Swal.fire({
    title: 'What is missing?', input: 'text', inputPlaceholder: 'e.g. Baptismal certificate not clear',
    showCancelButton: true, confirmButtonText: 'Send', confirmButtonColor: '#235a38'
  }).then(function(r){ if(r.isConfirmed && r.value){ changeStatus('missing_requirements', r.value); } });
}
function verifyDocument(id, btn){
  $.post('<?= site_url($base . '/booking/verify_document') ?>', { document_id: id }, function(){
    $(btn).replaceWith('<span class="text-xs text-emerald-600 font-medium">Verified</span>');
  });
}
$('#assign-form').on('submit', function(e){
  e.preventDefault();
  $.post('<?= site_url($base . '/booking/assign') ?>', $(this).serialize() + '&id=<?= $booking['id'] ?>', function(res){
    if(res.success){ toastr.success(res.message); setTimeout(function(){ location.reload(); }, 700); } else { toastr.error(res.message); }
  });
});
</script>
