<a href="<?= site_url('priest/schedule') ?>" class="text-sm text-parish-700 hover:underline">← Back to Schedule</a>

<div class="bg-white rounded-2xl border border-gray-100 p-6 sm:p-8 mt-4 max-w-2xl">
  <div class="flex items-start justify-between flex-wrap gap-3">
    <div>
      <div class="text-xs text-gray-400"><?= html_escape($booking['booking_code']) ?></div>
      <h1 class="text-xl font-semibold text-gray-900 mt-1"><?= html_escape($booking['service_name']) ?></h1>
      <div class="text-sm text-gray-500 mt-1"><?= html_escape($booking['first_name'] . ' ' . $booking['last_name']) ?></div>
      <div class="text-sm text-gray-500 mt-1"><?= $booking['confirmed_date'] ? format_datetime($booking['confirmed_date']) : 'Not yet scheduled' ?></div>
    </div>
    <span class="px-3 py-1.5 rounded-full text-xs font-medium <?= status_badge_class($booking['status']) ?>"><?= status_label($booking['status']) ?></span>
  </div>

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

  <?php if (!empty($documents)): ?>
  <div class="mt-6 pt-6 border-t border-gray-100">
    <h2 class="text-sm font-semibold text-gray-700 mb-3">Documents</h2>
    <div class="space-y-2">
      <?php foreach ($documents as $d): ?>
      <a href="<?= base_url($d['file_path']) ?>" target="_blank" class="flex items-center gap-3 p-3 rounded-lg border border-gray-100 text-sm hover:border-parish-300">
        <i class="ph ph-file text-gray-400"></i> <?= html_escape($d['label'] ?: $d['original_name']) ?>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
  <?php endif; ?>

  <div class="mt-6 pt-6 border-t border-gray-100">
    <h2 class="text-sm font-semibold text-gray-700 mb-3">Notes</h2>
    <form id="note-form" class="flex gap-2">
      <input name="note" value="<?= html_escape($booking['staff_notes'] ?? '') ?>" placeholder="Add a note…" class="flex-1 px-4 py-2.5 rounded-lg border border-gray-200 text-sm">
      <button type="submit" class="px-4 py-2.5 rounded-lg bg-gray-800 hover:bg-gray-900 text-white text-sm font-medium">Save</button>
    </form>
  </div>

  <div class="mt-6 pt-6 border-t border-gray-100 flex gap-3">
    <?php if ($booking['status'] !== 'completed'): ?>
    <button onclick="markComplete()" class="px-5 py-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium">Mark Completed</button>
    <?php endif; ?>
  </div>
</div>

<script>
$('#note-form').on('submit', function(e){
  e.preventDefault();
  $.post('<?= site_url('priest/schedule/add_note') ?>', { id: <?= $booking['id'] ?>, note: $(this).find('input').val() }, function(res){
    toastr.success(res.message);
  });
});
function markComplete(){
  Swal.fire({ icon:'question', title:'Mark this service as completed?', showCancelButton:true, confirmButtonColor:'#059669' })
    .then(function(r){ if(r.isConfirmed){
      $.post('<?= site_url('priest/schedule/complete') ?>', { id: <?= $booking['id'] ?> }, function(res){
        toastr.success(res.message); setTimeout(function(){ location.reload(); }, 700);
      });
    }});
}
</script>
