<h1 class="text-2xl font-semibold text-gray-900 mb-1">Mass Intentions</h1>
<p class="text-gray-500 text-sm mb-6">Approve submitted intentions and prepare printable lists per Mass.</p>

<div class="bg-white rounded-2xl border border-gray-100 divide-y divide-gray-50">
  <?php if (empty($intentions)): ?>
    <div class="text-center py-14 text-gray-400 text-sm">No mass intentions submitted yet.</div>
  <?php endif; ?>
  <?php foreach ($intentions as $i): ?>
  <div class="flex items-center justify-between p-5 flex-wrap gap-3">
    <div>
      <div class="text-sm font-medium text-gray-800"><?= html_escape($i['offered_for']) ?> <span class="text-gray-400 font-normal">(<?= ucfirst(str_replace('_',' ',$i['intention_type'])) ?>)</span></div>
      <div class="text-xs text-gray-400 mt-0.5">
        Requested by <?= html_escape($i['requestor_name']) ?>
        <?php if ($i['mass_title']): ?> · <?= day_name($i['day_of_week']) ?> <?= date('g:i A', strtotime($i['mass_time'])) ?><?php endif; ?>
        · <?= format_date($i['created_at']) ?>
      </div>
    </div>
    <div class="flex items-center gap-2">
      <span class="px-2.5 py-1 rounded-full text-xs font-medium <?= status_badge_class($i['status']) ?>"><?= status_label($i['status']) ?></span>
      <?php if ($i['status'] === 'pending'): ?>
      <button onclick="updateIntention(<?= $i['id'] ?>, 'approved')" class="text-xs text-emerald-600 hover:underline font-medium">Approve</button>
      <?php elseif ($i['status'] === 'approved'): ?>
      <button onclick="updateIntention(<?= $i['id'] ?>, 'listed')" class="text-xs text-parish-700 hover:underline font-medium">Mark Listed</button>
      <?php endif; ?>
    </div>
  </div>
  <?php endforeach; ?>
</div>

<script>
function updateIntention(id, status){
  $.post('<?= site_url('staff/mass_intention/update_status') ?>', { id: id, status: status }, function(res){
    toastr.success(res.message); setTimeout(function(){ location.reload(); }, 600);
  });
}
</script>
