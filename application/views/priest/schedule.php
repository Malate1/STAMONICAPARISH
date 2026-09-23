<div class="flex items-center justify-between mb-6">
  <h1 class="text-2xl font-semibold text-gray-900">My Schedule</h1>
  <button onclick="openBlockModal()" class="px-4 py-2.5 rounded-lg bg-parish-700 hover:bg-parish-800 text-white text-sm font-medium flex items-center gap-2"><i class="ph ph-calendar-x"></i> Block Dates</button>
</div>

<div class="grid lg:grid-cols-3 gap-6">
  <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 p-6">
    <h2 class="font-semibold text-gray-800 mb-4">Assigned Services</h2>
    <?php if (empty($assignments)): ?>
      <p class="text-sm text-gray-400 text-center py-10">No assignments yet.</p>
    <?php else: ?>
      <div class="divide-y divide-gray-50">
        <?php foreach ($assignments as $a): ?>
        <a href="<?= site_url('priest/schedule/view/' . $a['id']) ?>" class="flex items-center justify-between py-3 hover:bg-gray-50 -mx-2 px-2 rounded-lg">
          <div>
            <div class="text-sm font-medium text-gray-800"><?= html_escape($a['service_name']) ?> — <?= html_escape($a['first_name'] . ' ' . $a['last_name']) ?></div>
            <div class="text-xs text-gray-400"><?= $a['confirmed_date'] ? format_datetime($a['confirmed_date']) : 'Not yet scheduled' ?></div>
          </div>
          <span class="px-2.5 py-1 rounded-full text-xs font-medium <?= status_badge_class($a['status']) ?>"><?= status_label($a['status']) ?></span>
        </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <div class="bg-white rounded-2xl border border-gray-100 p-6">
    <h2 class="font-semibold text-gray-800 mb-4">Blocked Dates</h2>
    <?php if (empty($blocked_dates)): ?>
      <p class="text-sm text-gray-400">No blocked dates.</p>
    <?php else: ?>
      <div class="space-y-2">
        <?php foreach ($blocked_dates as $b): ?>
        <div class="flex items-center justify-between text-sm p-2 rounded-lg border border-gray-100">
          <div>
            <div class="text-gray-700"><?= format_date($b['date_from']) ?> – <?= format_date($b['date_to']) ?></div>
            <?php if ($b['reason']): ?><div class="text-xs text-gray-400"><?= html_escape($b['reason']) ?></div><?php endif; ?>
          </div>
          <button onclick="unblockDate(<?= $b['id'] ?>)" class="text-xs text-red-500 hover:underline">Remove</button>
        </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>
</div>

<div id="block-modal" class="hidden fixed inset-0 z-50 items-center justify-center p-4">
  <div class="absolute inset-0 bg-black/50" onclick="closeBlockModal()"></div>
  <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-md p-6">
    <h2 class="font-semibold text-gray-800 mb-4">Block Unavailable Dates</h2>
    <form id="block-form" class="space-y-4">
      <div class="grid grid-cols-2 gap-4">
        <div><label class="text-xs font-medium text-gray-500">From</label><input required type="date" name="date_from" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm"></div>
        <div><label class="text-xs font-medium text-gray-500">To</label><input required type="date" name="date_to" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm"></div>
      </div>
      <div><label class="text-xs font-medium text-gray-500">Reason (optional)</label><input name="reason" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm"></div>
      <div class="flex justify-end gap-3 pt-2">
        <button type="button" onclick="closeBlockModal()" class="px-4 py-2.5 rounded-lg border border-gray-200 text-sm font-medium text-gray-600">Cancel</button>
        <button type="submit" class="px-5 py-2.5 rounded-lg bg-parish-700 hover:bg-parish-800 text-white text-sm font-medium">Block Dates</button>
      </div>
    </form>
  </div>
</div>

<script>
function openBlockModal(){ $('#block-modal').removeClass('hidden').addClass('flex'); }
function closeBlockModal(){ $('#block-modal').addClass('hidden').removeClass('flex'); }
function unblockDate(id){
  $.post('<?= site_url('priest/schedule/unblock_date/') ?>' + id, function(){ toastr.success('Removed.'); setTimeout(function(){ location.reload(); }, 500); });
}
$('#block-form').on('submit', function(e){
  e.preventDefault();
  $.post('<?= site_url('priest/schedule/block_date') ?>', $(this).serialize(), function(res){
    if(res.success){ toastr.success(res.message); setTimeout(function(){ location.reload(); }, 600); } else { toastr.error(res.message); }
  });
});
$(function(){
  document.getElementById('block-modal').querySelector('.relative').addEventListener('click', function(e){ e.stopPropagation(); });
});
</script>
