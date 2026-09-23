<h1 class="text-2xl font-semibold text-gray-900 mb-1">Parish Office Dashboard</h1>
<p class="text-gray-500 text-sm mb-6">Overview of today's operations.</p>

<div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
  <div class="bg-white rounded-2xl border border-gray-100 p-5">
    <div class="text-xs text-gray-400">Awaiting Verification</div>
    <div class="text-2xl font-semibold text-amber-600 mt-1"><?= $needs_attention['awaiting_verification'] ?></div>
  </div>
  <div class="bg-white rounded-2xl border border-gray-100 p-5">
    <div class="text-xs text-gray-400">Incomplete Requirements</div>
    <div class="text-2xl font-semibold text-red-500 mt-1"><?= $needs_attention['incomplete_requirements'] ?></div>
  </div>
  <div class="bg-white rounded-2xl border border-gray-100 p-5">
    <div class="text-xs text-gray-400">Payments Awaiting</div>
    <div class="text-2xl font-semibold text-orange-500 mt-1"><?= $needs_attention['payments_awaiting'] ?></div>
  </div>
  <div class="bg-white rounded-2xl border border-gray-100 p-5">
    <div class="text-xs text-gray-400">Certificates Ready</div>
    <div class="text-2xl font-semibold text-emerald-600 mt-1"><?= $needs_attention['certificates_ready'] ?></div>
  </div>
</div>

<div class="grid lg:grid-cols-3 gap-6 mb-8">
  <div class="bg-white rounded-2xl border border-gray-100 p-6">
    <div class="text-xs text-gray-400">Parishioners</div>
    <div class="text-2xl font-semibold text-gray-900 mt-1"><?= $user_counts['parishioners'] ?></div>
  </div>
  <div class="bg-white rounded-2xl border border-gray-100 p-6">
    <div class="text-xs text-gray-400">Priests / Secretaries</div>
    <div class="text-2xl font-semibold text-gray-900 mt-1"><?= $user_counts['priests'] ?> / <?= $user_counts['secretaries'] ?></div>
  </div>
  <div class="bg-white rounded-2xl border border-gray-100 p-6">
    <div class="text-xs text-gray-400">Revenue This Month</div>
    <div class="text-2xl font-semibold text-gold-600 mt-1"><?= peso($revenue_this_month) ?></div>
  </div>
</div>

<div class="grid lg:grid-cols-2 gap-6">
  <div class="bg-white rounded-2xl border border-gray-100 p-6">
    <h2 class="font-semibold text-gray-800 mb-4">Today's Queue</h2>
    <?php if (empty($today_summary)): ?>
      <p class="text-sm text-gray-400">No confirmed services today.</p>
    <?php else: ?>
      <div class="space-y-2">
        <?php foreach ($today_summary as $t): ?>
        <div class="flex items-center justify-between text-sm py-2 border-b border-gray-50 last:border-0">
          <span class="text-gray-700"><?= html_escape($t['service_name']) ?></span>
          <span class="font-medium text-parish-700"><?= $t['total'] ?></span>
        </div>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <div class="bg-white rounded-2xl border border-gray-100 p-6">
    <h2 class="font-semibold text-gray-800 mb-4">Recent Activity</h2>
    <div class="space-y-3 max-h-72 overflow-y-auto">
      <?php foreach ($recent_activity as $a): ?>
      <div class="text-sm">
        <span class="text-gray-700"><?= html_escape(($a['first_name'] ?? 'System') . ' ' . ($a['last_name'] ?? '')) ?></span>
        <span class="text-gray-400">— <?= html_escape($a['action']) ?><?= $a['description'] ? ': ' . html_escape($a['description']) : '' ?></span>
        <div class="text-xs text-gray-300"><?= format_datetime($a['created_at']) ?></div>
      </div>
      <?php endforeach; ?>
    </div>
  </div>
</div>
