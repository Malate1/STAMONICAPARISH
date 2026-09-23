<div class="flex items-center justify-between mb-6">
  <div>
    <h1 class="text-2xl font-semibold text-gray-900">My Bookings</h1>
    <p class="text-gray-500 text-sm mt-1">Apply for a church service or check your application status.</p>
  </div>
</div>

<div class="grid sm:grid-cols-3 lg:grid-cols-4 gap-3 mb-8">
  <?php
    $icons = ['baptism' => 'ph-drop', 'wedding' => 'ph-heart', 'funeral' => 'ph-cross', 'confirmation' => 'ph-sparkle', 'house_blessing' => 'ph-house-line', 'vehicle_blessing' => 'ph-car', 'counseling' => 'ph-chats-circle'];
    foreach ($service_types as $s):
      $icon = $icons[$s['service_key']] ?? 'ph-hand-heart';
  ?>
  <a href="<?= site_url('my/bookings/new/' . $s['service_key']) ?>" class="flex items-center gap-3 p-4 rounded-xl border border-gray-100 hover:border-parish-300 hover:shadow-sm transition bg-white">
    <div class="w-10 h-10 rounded-lg bg-parish-50 text-parish-700 flex items-center justify-center text-lg flex-shrink-0"><i class="ph <?= $icon ?>"></i></div>
    <div class="text-sm font-medium text-gray-700"><?= html_escape($s['name']) ?></div>
  </a>
  <?php endforeach; ?>
</div>

<div class="bg-white rounded-2xl border border-gray-100 p-6">
  <h2 class="font-semibold text-gray-800 mb-4">Application History</h2>
  <div class="overflow-x-auto">
    <table id="bookings-table" class="w-full text-sm">
      <thead>
        <tr>
          <th>Code</th><th>Service</th><th>Reserved Schedule</th><th>Status</th><th>Submitted</th><th></th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>
</div>

<script>
$(function(){
  $('#bookings-table').DataTable({
    processing: true,
    serverSide: true,
    ajax: { url: '<?= site_url('my/bookings/datatable') ?>', type: 'POST' },
    columns: [
      { data: 'booking_code' }, { data: 'service_name' }, { data: 'preferred_date' },
      { data: 'status', orderable: false }, { data: 'created_at' }, { data: 'actions', orderable: false, searchable: false }
    ],
    order: [[4, 'desc']],
    language: { search: '', searchPlaceholder: 'Search applications…', emptyTable: 'No applications found.' }
  });
});
</script>
