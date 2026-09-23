<?php $base = strpos(uri_string(), 'staff') === 0 ? 'staff' : 'admin'; ?>
<a href="<?= site_url($base . '/certificate') ?>" class="text-sm text-parish-700 hover:underline">← Back to Certificate Requests</a>

<div class="grid lg:grid-cols-3 gap-6 mt-4">
  <div class="lg:col-span-2 space-y-6">
    <div class="bg-white rounded-2xl border border-gray-100 p-6">
      <div class="flex items-start justify-between flex-wrap gap-3">
        <div>
          <div class="text-xs text-gray-400"><?= html_escape($cert['request_code']) ?></div>
          <h1 class="text-xl font-semibold text-gray-900 mt-1"><?= ucfirst($cert['certificate_type']) ?> Certificate</h1>
          <div class="text-sm text-gray-500 mt-1"><?= html_escape($cert['first_name'] . ' ' . $cert['last_name']) ?> · <?= html_escape($cert['email']) ?></div>
          <div class="text-sm text-gray-500 mt-1">Purpose: <?= html_escape($cert['purpose']) ?> · <?= $cert['number_of_copies'] ?> cop<?= $cert['number_of_copies'] > 1 ? 'ies' : 'y' ?> · <?= peso($cert['fee_amount']) ?></div>
        </div>
        <span class="px-3 py-1.5 rounded-full text-xs font-medium <?= status_badge_class($cert['status']) ?>"><?= status_label($cert['status']) ?></span>
      </div>
    </div>

    <?php if (in_array($cert['status'], ['submitted', 'searching_record'])): ?>
    <div class="bg-white rounded-2xl border border-gray-100 p-6">
      <h2 class="text-sm font-semibold text-gray-700 mb-3">Search Parish Registry</h2>
      <div class="flex gap-2">
        <select id="search-type" class="px-3 py-2.5 rounded-lg border border-gray-200 text-sm">
          <?php
            $map = ['baptismal' => 'baptism', 'confirmation' => 'confirmation', 'marriage' => 'marriage'];
            $default = $map[$cert['certificate_type']] ?? 'baptism';
          ?>
          <option value="baptism" <?= $default === 'baptism' ? 'selected' : '' ?>>Baptism</option>
          <option value="confirmation" <?= $default === 'confirmation' ? 'selected' : '' ?>>Confirmation</option>
          <option value="communion">Communion</option>
          <option value="marriage" <?= $default === 'marriage' ? 'selected' : '' ?>>Marriage</option>
          <option value="funeral">Funeral</option>
        </select>
        <input id="search-keyword" placeholder="Search by name…" class="flex-1 px-4 py-2.5 rounded-lg border border-gray-200 text-sm" value="<?= html_escape($cert['first_name'] . ' ' . $cert['last_name']) ?>">
        <button onclick="searchRecords()" class="px-4 py-2.5 rounded-lg bg-parish-700 hover:bg-parish-800 text-white text-sm font-medium">Search</button>
      </div>
      <div id="search-results" class="mt-4 space-y-2"></div>
      <button onclick="markNoRecord()" class="text-xs text-red-600 hover:underline mt-3">No matching record found</button>
    </div>
    <?php endif; ?>

    <?php if ($cert['record_name']): ?>
    <div class="bg-white rounded-2xl border border-gray-100 p-6">
      <h2 class="text-sm font-semibold text-gray-700 mb-2">Linked Record</h2>
      <div class="text-sm text-gray-700"><?= html_escape($cert['record_name']) ?> · <?= format_date($cert['sacrament_date']) ?></div>
      <div class="text-xs text-gray-400 mt-1">Book <?= html_escape($cert['registry_book'] ?: '—') ?>, Page <?= html_escape($cert['registry_page'] ?: '—') ?></div>
    </div>
    <?php endif; ?>
  </div>

  <div class="space-y-6">
    <?php if ($payment): ?>
    <div class="bg-white rounded-2xl border border-gray-100 p-6">
      <h2 class="text-sm font-semibold text-gray-700 mb-3">Payment</h2>
      <div class="text-sm text-gray-600"><?= peso($payment['amount']) ?> — <span class="px-2 py-0.5 rounded-full text-xs font-medium <?= status_badge_class($payment['status']) ?>"><?= status_label($payment['status']) ?></span></div>
      <a href="<?= site_url($base . '/payment') ?>" class="text-xs text-parish-700 hover:underline font-medium mt-2 inline-block">Manage in Payments →</a>
    </div>
    <?php endif; ?>

    <div class="bg-white rounded-2xl border border-gray-100 p-6">
      <h2 class="text-sm font-semibold text-gray-700 mb-4">Actions</h2>
      <div class="space-y-2">
        <?php if ($cert['status'] === 'preparing'): ?>
          <button onclick="prepareCert()" class="w-full px-3 py-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium">Generate Certificate &amp; QR</button>
        <?php endif; ?>
        <?php if ($cert['status'] === 'ready_for_release'): ?>
          <button onclick="releaseCert()" class="w-full px-3 py-2.5 rounded-lg bg-green-600 hover:bg-green-700 text-white text-sm font-medium">Mark as Released</button>
          <a href="<?= site_url('verify/' . $cert['qr_code_token']) ?>" target="_blank" class="block text-center text-xs text-parish-700 hover:underline mt-2">Preview verification page →</a>
        <?php endif; ?>
        <?php if ($cert['status'] === 'released'): ?>
          <div class="text-sm text-emerald-600 font-medium text-center py-2">Released on <?= format_date($cert['released_at']) ?></div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>

<script>
function searchRecords(){
  $.post('<?= site_url($base . '/certificate/search_records') ?>', { type: $('#search-type').val(), keyword: $('#search-keyword').val() }, function(res){
    var $r = $('#search-results').empty();
    if(!res.data.length){ $r.append('<p class="text-xs text-gray-400">No matches found.</p>'); return; }
    res.data.forEach(function(rec){
      $r.append(
        '<div class="flex items-center justify-between p-3 rounded-lg border border-gray-100">' +
          '<div><div class="text-sm text-gray-700">'+ rec.full_name +'</div><div class="text-xs text-gray-400">'+ (rec.sacrament_date || '') +' · Bk. '+(rec.registry_book||'—')+' Pg. '+(rec.registry_page||'—')+'</div></div>' +
          '<button onclick="linkRecord('+rec.id+')" class="text-xs text-parish-700 hover:underline font-medium">Link this record</button>' +
        '</div>'
      );
    });
  });
}
function linkRecord(recordId){
  $.post('<?= site_url($base . '/certificate/link_record') ?>', { id: <?= $cert['id'] ?>, record_id: recordId }, function(res){
    toastr.success(res.message); setTimeout(function(){ location.reload(); }, 700);
  });
}
function markNoRecord(){
  $.post('<?= site_url($base . '/certificate/no_record') ?>', { id: <?= $cert['id'] ?> }, function(res){
    toastr.info(res.message); setTimeout(function(){ location.reload(); }, 700);
  });
}
function prepareCert(){
  $.post('<?= site_url($base . '/certificate/prepare') ?>', { id: <?= $cert['id'] ?> }, function(res){
    toastr.success(res.message); setTimeout(function(){ location.reload(); }, 700);
  });
}
function releaseCert(){
  $.post('<?= site_url($base . '/certificate/release') ?>', { id: <?= $cert['id'] ?> }, function(res){
    toastr.success(res.message); setTimeout(function(){ location.reload(); }, 700);
  });
}
</script>
