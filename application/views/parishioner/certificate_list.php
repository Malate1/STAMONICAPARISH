<div class="flex items-center justify-between mb-6">
  <div>
    <h1 class="text-2xl font-semibold text-gray-900">My Certificates</h1>
    <p class="text-gray-500 text-sm mt-1">Request baptismal, confirmation, or marriage certificates.</p>
  </div>
  <a href="<?= site_url('my/certificates/new') ?>" class="px-4 py-2.5 rounded-lg bg-parish-700 hover:bg-parish-800 text-white text-sm font-medium flex items-center gap-2"><i class="ph ph-plus"></i> Request Certificate</a>
</div>

<div class="bg-white rounded-2xl border border-gray-100 divide-y divide-gray-50">
  <?php if (empty($certificates)): ?>
    <div class="text-center py-14 text-gray-400 text-sm">No certificate requests yet.</div>
  <?php endif; ?>
  <?php foreach ($certificates as $c): ?>
  <a href="<?= site_url('my/certificates/' . $c['id']) ?>" class="flex items-center justify-between p-5 hover:bg-gray-50">
    <div class="flex items-center gap-3">
      <div class="w-10 h-10 rounded-lg bg-gold-50 text-gold-600 flex items-center justify-center text-lg"><i class="ph ph-scroll"></i></div>
      <div>
        <div class="text-sm font-medium text-gray-800"><?= ucfirst($c['certificate_type']) ?> Certificate</div>
        <div class="text-xs text-gray-400"><?= html_escape($c['request_code']) ?> · <?= format_date($c['created_at']) ?></div>
      </div>
    </div>
    <span class="px-2.5 py-1 rounded-full text-xs font-medium <?= status_badge_class($c['status']) ?>"><?= status_label($c['status']) ?></span>
  </a>
  <?php endforeach; ?>
</div>
