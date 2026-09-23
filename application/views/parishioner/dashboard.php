<div class="mb-6">
  <h1 class="text-2xl font-semibold text-gray-900">Welcome, <?= html_escape($current_user['first_name']) ?> 👋</h1>
  <p class="text-gray-500 text-sm mt-1">Here's what's happening with your parish requests.</p>
</div>

<div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
  <div class="bg-white rounded-2xl border border-gray-100 p-5">
    <div class="text-xs text-gray-400">Total Applications</div>
    <div class="text-2xl font-semibold text-gray-900 mt-1"><?= $stats['total_bookings'] ?></div>
  </div>
  <div class="bg-white rounded-2xl border border-gray-100 p-5">
    <div class="text-xs text-gray-400">Pending</div>
    <div class="text-2xl font-semibold text-amber-600 mt-1"><?= $stats['pending'] ?></div>
  </div>
  <div class="bg-white rounded-2xl border border-gray-100 p-5">
    <div class="text-xs text-gray-400">Completed</div>
    <div class="text-2xl font-semibold text-emerald-600 mt-1"><?= $stats['completed'] ?></div>
  </div>
  <div class="bg-white rounded-2xl border border-gray-100 p-5">
    <div class="text-xs text-gray-400">Certificate Requests</div>
    <div class="text-2xl font-semibold text-gray-900 mt-1"><?= $stats['certificates'] ?></div>
  </div>
</div>

<div class="grid lg:grid-cols-3 gap-6">
  <div class="lg:col-span-2 bg-white rounded-2xl border border-gray-100 p-6">
    <div class="flex items-center justify-between mb-4">
      <h2 class="font-semibold text-gray-800">Recent Applications</h2>
      <a href="<?= site_url('my/bookings') ?>" class="text-sm text-parish-700 hover:underline">View all →</a>
    </div>
    <?php if (empty($bookings)): ?>
      <div class="text-center py-10 text-gray-400 text-sm">
        No applications yet.
        <div class="mt-3"><a href="<?= site_url('my/bookings') ?>" class="text-parish-700 font-medium hover:underline">Book a service →</a></div>
      </div>
    <?php else: ?>
      <div class="divide-y divide-gray-50">
        <?php foreach ($bookings as $b): ?>
        <a href="<?= site_url('my/bookings/' . $b['id']) ?>" class="flex items-center justify-between py-3 hover:bg-gray-50 -mx-2 px-2 rounded-lg">
          <div>
            <div class="text-sm font-medium text-gray-800"><?= html_escape($b['service_name']) ?></div>
            <div class="text-xs text-gray-400"><?= html_escape($b['booking_code']) ?> · <?= format_date($b['created_at']) ?></div>
          </div>
          <span class="px-2.5 py-1 rounded-full text-xs font-medium <?= status_badge_class($b['status']) ?>"><?= status_label($b['status']) ?></span>
        </a>
        <?php endforeach; ?>
      </div>
    <?php endif; ?>
  </div>

  <div class="bg-white rounded-2xl border border-gray-100 p-6">
    <h2 class="font-semibold text-gray-800 mb-4">Announcements</h2>
    <div class="space-y-3">
      <?php if (empty($announcements)): ?>
        <p class="text-sm text-gray-400">No announcements yet.</p>
      <?php endif; ?>
      <?php foreach ($announcements as $a): ?>
      <a href="<?= site_url('announcements/' . $a['slug']) ?>" class="block text-sm">
        <div class="font-medium text-gray-700"><?= html_escape($a['title']) ?></div>
        <div class="text-xs text-gray-400"><?= format_date($a['publish_date']) ?></div>
      </a>
      <?php endforeach; ?>
    </div>
  </div>
</div>
