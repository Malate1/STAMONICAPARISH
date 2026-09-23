<div class="grid lg:grid-cols-2 gap-6">
  <div class="bg-white rounded-2xl border border-gray-100 p-6">
    <h1 class="text-xl font-semibold text-gray-900 mb-1">Submit Mass Intention</h1>
    <p class="text-sm text-gray-400 mb-6">Offer a Mass for thanksgiving, healing, or the souls of the departed.</p>

    <form id="intention-form" class="space-y-4">
      <div>
        <label class="text-xs font-medium text-gray-500">Intention Type</label>
        <select required name="intention_type" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm">
          <option value="">Select…</option>
          <option value="thanksgiving">Thanksgiving</option>
          <option value="birthday">Birthday</option>
          <option value="healing">Healing</option>
          <option value="special">Special Intention</option>
          <option value="safe_travel">Safe Travel</option>
          <option value="souls_departed">Souls of the Faithful Departed</option>
          <option value="anniversary">Wedding Anniversary</option>
          <option value="other">Other</option>
        </select>
      </div>
      <div>
        <label class="text-xs font-medium text-gray-500">Offered For</label>
        <input required name="offered_for" placeholder="Name(s) of intention" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm">
      </div>
      <div>
        <label class="text-xs font-medium text-gray-500">Mass Schedule</label>
        <select required name="mass_schedule_id" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm">
          <option value="">Select a Mass…</option>
          <?php foreach ($masses as $m): ?>
            <option value="<?= $m['id'] ?>"><?= day_name($m['day_of_week']) ?> · <?= date('g:i A', strtotime($m['mass_time'])) ?> — <?= html_escape($m['title']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <button type="submit" class="w-full py-3 rounded-lg bg-parish-700 hover:bg-parish-800 text-white text-sm font-medium">Submit Intention</button>
    </form>
  </div>

  <div class="bg-white rounded-2xl border border-gray-100 p-6">
    <h2 class="font-semibold text-gray-800 mb-4">My Mass Intentions</h2>
    <div class="divide-y divide-gray-50">
      <?php if (empty($intentions)): ?>
        <p class="text-sm text-gray-400 text-center py-10">No mass intentions submitted yet.</p>
      <?php endif; ?>
      <?php foreach ($intentions as $i): ?>
      <div class="py-3">
        <div class="flex items-center justify-between">
          <div class="text-sm font-medium text-gray-800"><?= ucfirst(str_replace('_',' ',$i['intention_type'])) ?></div>
          <span class="px-2 py-0.5 rounded-full text-[11px] font-medium <?= status_badge_class($i['status']) ?>"><?= status_label($i['status']) ?></span>
        </div>
        <div class="text-xs text-gray-500 mt-0.5"><?= html_escape($i['offered_for']) ?></div>
        <div class="text-xs text-gray-300 mt-0.5"><?= format_date($i['created_at']) ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>

<script>
$('#intention-form').on('submit', function(e){
  e.preventDefault();
  $.post('<?= site_url('my/mass-intentions/store') ?>', $(this).serialize(), function(res){
    if(res.success){ toastr.success(res.message); setTimeout(function(){ location.reload(); }, 900); }
    else { toastr.error(res.message); }
  });
});
</script>
