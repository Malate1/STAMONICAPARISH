<div class="flex items-center justify-between mb-6 flex-wrap gap-3">
  <div>
    <h1 class="text-2xl font-semibold text-gray-900">Service Bookings</h1>
    <p class="text-gray-500 text-sm mt-1">Review and process sacrament & service applications.</p>
  </div>
  <div class="flex gap-2">
    <select id="filter-status" class="px-3 py-2 rounded-lg border border-gray-200 text-sm">
      <option value="">All Statuses</option>
      <option value="submitted">Submitted</option>
      <option value="under_review">Under Review</option>
      <option value="missing_requirements">Missing Requirements</option>
      <option value="requirements_complete">Requirements Complete</option>
      <option value="awaiting_payment">Awaiting Payment</option>
      <option value="payment_verification">Payment Verification</option>
      <option value="approved">Approved</option>
      <option value="scheduled">Scheduled</option>
      <option value="completed">Completed</option>
      <option value="cancelled">Cancelled</option>
    </select>
    <select id="filter-service" class="px-3 py-2 rounded-lg border border-gray-200 text-sm">
      <option value="">All Services</option>
      <?php foreach ($service_types as $s): ?>
        <option value="<?= $s['id'] ?>"><?= html_escape($s['name']) ?></option>
      <?php endforeach; ?>
    </select>
  </div>
</div>

<div class="bg-white rounded-2xl border border-gray-100 p-6">
  <div class="overflow-x-auto">
    <table id="booking-table" class="w-full text-sm">
      <thead><tr><th>Code</th><th>Service</th><th>Applicant</th><th>Preferred Date</th><th>Status</th><th>Submitted</th><th></th></tr></thead>
      <tbody></tbody>
    </table>
  </div>
</div>

<script>
var table;
$(function(){
  table = $('#booking-table').DataTable({
    processing: true, serverSide: true,
    ajax: {
      url: '<?= site_url((strpos(uri_string(), 'staff') === 0 ? 'staff' : 'admin') . '/booking/datatable') ?>',
      type: 'POST',
      data: function(d){ d.status = $('#filter-status').val(); d.service_type_id = $('#filter-service').val(); }
    },
    columns: [
      { data: 'booking_code' }, { data: 'service_name' }, { data: 'applicant' }, { data: 'preferred_date' },
      { data: 'status', orderable: false }, { data: 'created_at' }, { data: 'actions', orderable: false, searchable: false }
    ],
    order: [[5, 'desc']],
    language: { search: '', searchPlaceholder: 'Search bookings…' }
  });
  $('#filter-status, #filter-service').on('change', function(){ table.ajax.reload(); });
});
</script>
