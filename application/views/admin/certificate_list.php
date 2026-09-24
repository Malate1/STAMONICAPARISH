<div class="flex items-center justify-between mb-6">
  <div>
    <h1 class="text-2xl font-semibold text-gray-900">Certificate Requests</h1>
    <p class="text-gray-500 text-sm mt-1">Search records, verify payments, and release certificates.</p>
  </div>
</div>

<div class="bg-white rounded-2xl border border-gray-100 p-6">
  <div class="overflow-x-auto">
    <table id="cert-table" class="w-full text-sm">
      <thead><tr><th>Code</th><th>Type</th><th>Requestor</th><th>Copies</th><th>Status</th><th>Date</th><th class="text-center">Actions</th></tr></thead>
      <tbody></tbody>
    </table>
  </div>
</div>

<script>
$(function(){
  $('#cert-table').DataTable({
    processing: true, serverSide: true,
    ajax: { url: '<?= site_url((strpos(uri_string(), 'staff') === 0 ? 'staff' : 'admin') . '/certificate/datatable') ?>', type: 'POST' },
    columns: [
      { data: 'request_code' }, { data: 'type' }, { data: 'requestor' }, { data: 'copies' },
      { data: 'status', orderable: false }, { data: 'created_at' }, { data: 'actions', orderable: false, searchable: false }
    ],
    order: [[5, 'desc']],
    language: { search: '', searchPlaceholder: 'Search requests…' }
  });
});
</script>
