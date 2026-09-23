<h1 class="text-2xl font-semibold text-gray-900 mb-1">Welcome, Fr. <?= html_escape($current_user['first_name']) ?></h1>
<p class="text-gray-500 text-sm mb-6">Your upcoming sacramental and pastoral assignments.</p>

<div class="bg-white rounded-2xl border border-gray-100 p-6">
  <h2 class="font-semibold text-gray-800 mb-4">This Week</h2>
  <?php if (empty($this_week)): ?>
    <p class="text-sm text-gray-400 text-center py-10">No assignments scheduled this week.</p>
  <?php else: ?>
    <div class="divide-y divide-gray-50">
      <?php foreach ($this_week as $a): ?>
      <a href="<?= site_url('priest/schedule/view/' . $a['id']) ?>" class="flex items-center justify-between py-3 hover:bg-gray-50 -mx-2 px-2 rounded-lg">
        <div>
          <div class="text-sm font-medium text-gray-800"><?= html_escape($a['service_name']) ?> — <?= html_escape($a['first_name'] . ' ' . $a['last_name']) ?></div>
          <div class="text-xs text-gray-400"><?= format_datetime($a['confirmed_date']) ?></div>
        </div>
        <span class="px-2.5 py-1 rounded-full text-xs font-medium <?= status_badge_class($a['status']) ?>"><?= status_label($a['status']) ?></span>
      </a>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<div class="mt-6">
  <a href="<?= site_url('priest/schedule') ?>" class="text-sm text-parish-700 font-medium hover:underline">View full schedule →</a>
</div>
