<h1 class="text-2xl font-semibold text-gray-900 mb-1">Parish Office — Daily Queue</h1>
<p class="text-gray-500 text-sm mb-6">Welcome, <?= html_escape($current_user['first_name']) ?>. Here's today's work.</p>

<div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
  <a href="<?= site_url('staff/booking') ?>?status=submitted" class="bg-white rounded-2xl border border-gray-100 p-5 hover:border-parish-300 transition">
    <div class="text-xs text-gray-400">Awaiting Verification</div>
    <div class="text-2xl font-semibold text-amber-600 mt-1"><?= $needs_attention['awaiting_verification'] ?></div>
  </a>
  <div class="bg-white rounded-2xl border border-gray-100 p-5">
    <div class="text-xs text-gray-400">Incomplete Requirements</div>
    <div class="text-2xl font-semibold text-red-500 mt-1"><?= $needs_attention['incomplete_requirements'] ?></div>
  </div>
  <a href="<?= site_url('staff/payment') ?>" class="bg-white rounded-2xl border border-gray-100 p-5 hover:border-parish-300 transition">
    <div class="text-xs text-gray-400">Payments Awaiting</div>
    <div class="text-2xl font-semibold text-orange-500 mt-1"><?= $needs_attention['payments_awaiting'] ?></div>
  </a>
  <a href="<?= site_url('staff/certificate') ?>" class="bg-white rounded-2xl border border-gray-100 p-5 hover:border-parish-300 transition">
    <div class="text-xs text-gray-400">Certificates Ready</div>
    <div class="text-2xl font-semibold text-emerald-600 mt-1"><?= $needs_attention['certificates_ready'] ?></div>
  </a>
</div>

<div class="grid lg:grid-cols-2 gap-6">
  <div class="bg-white rounded-2xl border border-gray-100 p-6">
    <h2 class="font-semibold text-gray-800 mb-4">Today's Confirmed Services</h2>
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
    <h2 class="font-semibold text-gray-800 mb-4">Quick Actions</h2>
    <div class="grid grid-cols-2 gap-3">
      <a href="<?= site_url('staff/walkin') ?>" class="flex flex-col items-center gap-2 p-4 rounded-xl border border-gray-100 hover:border-parish-300 text-center">
        <i class="ph ph-user-plus text-2xl text-parish-700"></i>
        <span class="text-xs font-medium text-gray-700">Record Walk-in</span>
      </a>
      <a href="<?= site_url('staff/booking') ?>" class="flex flex-col items-center gap-2 p-4 rounded-xl border border-gray-100 hover:border-parish-300 text-center">
        <i class="ph ph-calendar-check text-2xl text-parish-700"></i>
        <span class="text-xs font-medium text-gray-700">Review Bookings</span>
      </a>
      <a href="<?= site_url('staff/mass_intention') ?>" class="flex flex-col items-center gap-2 p-4 rounded-xl border border-gray-100 hover:border-parish-300 text-center">
        <i class="ph ph-hands-praying text-2xl text-parish-700"></i>
        <span class="text-xs font-medium text-gray-700">Mass Intentions</span>
      </a>
      <a href="<?= site_url('staff/record') ?>" class="flex flex-col items-center gap-2 p-4 rounded-xl border border-gray-100 hover:border-parish-300 text-center">
        <i class="ph ph-archive text-2xl text-parish-700"></i>
        <span class="text-xs font-medium text-gray-700">Sacramental Records</span>
      </a>
    </div>
  </div>
</div>
