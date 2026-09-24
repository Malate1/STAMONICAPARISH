<div class="flex items-center justify-between mb-6">
  <div>
    <h1 class="text-2xl font-semibold text-gray-900">Parish Events</h1>
    <p class="text-gray-500 text-sm mt-1">Manage the parish activities calendar.</p>
  </div>
  <button onclick="resetForm(); openModal()" class="px-4 py-2.5 rounded-lg bg-parish-700 hover:bg-parish-800 text-white text-sm font-medium flex items-center gap-2"><i class="ph ph-plus"></i> New Event</button>
</div>

<div class="bg-white rounded-2xl border border-gray-100 p-6">
  <div class="overflow-x-auto">
    <table id="event-table" class="w-full text-sm">
      <thead><tr><th>Title</th><th>Category</th><th>Date</th><th>Registrations</th><th>Status</th><th class="text-center">Actions</th></tr></thead>
      <tbody></tbody>
    </table>
  </div>
</div>

<div id="event-modal" class="hidden fixed inset-0 z-50 items-center justify-center p-4">
  <div class="absolute inset-0 bg-black/50" onclick="closeModal()"></div>
  <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-xl p-6 max-h-[90vh] overflow-y-auto">
    <h2 class="font-semibold text-gray-800 mb-4" id="event-modal-title">New Event</h2>
    <form id="event-form" class="space-y-4">
      <input type="hidden" name="id" id="f-id">
      <div><label class="text-xs font-medium text-gray-500">Title</label><input required name="title" id="f-title" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm"></div>
      <div><label class="text-xs font-medium text-gray-500">Description</label><textarea name="description" id="f-desc" rows="3" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm"></textarea></div>
      <div class="grid sm:grid-cols-2 gap-4">
        <div><label class="text-xs font-medium text-gray-500">Category</label><input name="category" id="f-category" placeholder="e.g. Feast, Youth, Outreach" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm"></div>
        <div><label class="text-xs font-medium text-gray-500">Location</label><input name="location" id="f-location" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm"></div>
      </div>
      <div class="grid sm:grid-cols-3 gap-4">
        <div><label class="text-xs font-medium text-gray-500">Event Date</label><input required type="date" name="event_date" id="f-date" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm"></div>
        <div><label class="text-xs font-medium text-gray-500">End Date</label><input type="date" name="end_date" id="f-end" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm"></div>
        <div><label class="text-xs font-medium text-gray-500">Time</label><input type="time" name="event_time" id="f-time" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm"></div>
      </div>
      <label class="flex items-center gap-2 text-sm text-gray-600"><input type="checkbox" name="allow_registration" id="f-allow" value="1" class="rounded border-gray-300 text-parish-700"> Allow online registration</label>
      <div><label class="text-xs font-medium text-gray-500">Registration Limit (optional)</label><input type="number" name="registration_limit" id="f-limit" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm"></div>
      <div><label class="text-xs font-medium text-gray-500">Status</label>
        <select name="status" id="f-status" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm">
          <option value="published">Published</option><option value="draft">Draft</option><option value="cancelled">Cancelled</option><option value="completed">Completed</option>
        </select>
      </div>
      <div class="flex justify-end gap-3 pt-2">
        <button type="button" onclick="closeModal()" class="px-4 py-2.5 rounded-lg border border-gray-200 text-sm font-medium text-gray-600">Cancel</button>
        <button type="submit" class="px-5 py-2.5 rounded-lg bg-parish-700 hover:bg-parish-800 text-white text-sm font-medium">Save Event</button>
      </div>
    </form>
  </div>
</div>

<script>
var table;
function openModal(){ $('#event-modal').removeClass('hidden').addClass('flex'); }
function closeModal(){ $('#event-modal').addClass('hidden').removeClass('flex'); }
function resetForm(){ $('#event-form')[0].reset(); $('#f-id').val(''); $('#event-modal-title').text('New Event'); }
function editEvent(id){
  $.get('<?= site_url('admin/event/get/') ?>' + id, function(res){
    var d = res.data;
    resetForm();
    $('#f-id').val(d.id); $('#f-title').val(d.title); $('#f-desc').val(d.description);
    $('#f-category').val(d.category); $('#f-location').val(d.location);
    $('#f-date').val(d.event_date); $('#f-end').val(d.end_date); $('#f-time').val(d.event_time);
    $('#f-allow').prop('checked', d.allow_registration == 1); $('#f-limit').val(d.registration_limit); $('#f-status').val(d.status);
    $('#event-modal-title').text('Edit Event');
    openModal();
  });
}
function deleteEvent(id){
  Swal.fire({ icon:'warning', title:'Delete this event?', showCancelButton:true, confirmButtonText:'Delete', confirmButtonColor:'#dc2626' })
    .then(function(r){ if(r.isConfirmed){ $.post('<?= site_url('admin/event/delete/') ?>' + id, function(res){ toastr.success(res.message); table.ajax.reload(); }); }});
}
$('#event-form').on('submit', function(e){
  e.preventDefault();
  $.post('<?= site_url('admin/event/store') ?>', $(this).serialize(), function(res){
    if(res.success){ toastr.success(res.message); closeModal(); table.ajax.reload(); } else { toastr.error(res.message); }
  });
});
$(function(){
  document.getElementById('event-modal').querySelector('.relative').addEventListener('click', function(e){ e.stopPropagation(); });
  table = $('#event-table').DataTable({
    processing: true, serverSide: true,
    ajax: { url: '<?= site_url('admin/event/datatable') ?>', type: 'POST' },
    columns: [
      { data: 'title' }, { data: 'category' }, { data: 'event_date' }, { data: 'registrations' },
      { data: 'status', orderable: false }, { data: 'actions', orderable: false, searchable: false }
    ],
    order: [[2, 'desc']],
    language: { search: '', searchPlaceholder: 'Search events…' }
  });
});
</script>
