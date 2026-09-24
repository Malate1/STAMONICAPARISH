<div class="flex items-center justify-between mb-6">
  <div>
    <h1 class="text-2xl font-semibold text-gray-900">Announcements</h1>
    <p class="text-gray-500 text-sm mt-1">Publish parish notices, schedule changes, and updates.</p>
  </div>
  <button onclick="resetForm(); openModal()" class="px-4 py-2.5 rounded-lg bg-parish-700 hover:bg-parish-800 text-white text-sm font-medium flex items-center gap-2"><i class="ph ph-plus"></i> New Announcement</button>
</div>

<div class="bg-white rounded-2xl border border-gray-100 p-6">
  <div class="overflow-x-auto">
    <table id="ann-table" class="w-full text-sm">
      <thead><tr><th>Title</th><th>Category</th><th>Publish</th><th>Expires</th><th>Status</th><th class="text-center">Actions</th></tr></thead>
      <tbody></tbody>
    </table>
  </div>
</div>

<div id="ann-modal" class="hidden fixed inset-0 z-50 items-center justify-center p-4">
  <div class="absolute inset-0 bg-black/50" onclick="closeModal()"></div>
  <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-xl p-6 max-h-[90vh] overflow-y-auto">
    <h2 class="font-semibold text-gray-800 mb-4" id="ann-modal-title">New Announcement</h2>
    <form id="ann-form" class="space-y-4">
      <input type="hidden" name="id" id="f-id">
      <div>
        <label class="text-xs font-medium text-gray-500">Title</label>
        <input required name="title" id="f-title" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm">
      </div>
      <div>
        <label class="text-xs font-medium text-gray-500">Content</label>
        <textarea required name="body" id="f-body" rows="5" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm"></textarea>
      </div>
      <div class="grid sm:grid-cols-2 gap-4">
        <div>
          <label class="text-xs font-medium text-gray-500">Category</label>
          <select name="category" id="f-category" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm">
            <option value="notice">Notice</option><option value="schedule_change">Schedule Change</option><option value="fiesta">Fiesta</option>
            <option value="activity">Activity</option><option value="ministry">Ministry</option><option value="emergency">Emergency</option>
            <option value="diocesan">Diocesan</option><option value="holiday">Holiday</option>
          </select>
        </div>
        <div>
          <label class="text-xs font-medium text-gray-500">Status</label>
          <select name="status" id="f-status" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm">
            <option value="published">Published</option><option value="draft">Draft</option><option value="archived">Archived</option>
          </select>
        </div>
      </div>
      <div class="grid sm:grid-cols-2 gap-4">
        <div>
          <label class="text-xs font-medium text-gray-500">Publish Date</label>
          <input required type="datetime-local" name="publish_date" id="f-publish" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm">
        </div>
        <div>
          <label class="text-xs font-medium text-gray-500">Expiration Date (optional)</label>
          <input type="datetime-local" name="expiration_date" id="f-expiry" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm">
        </div>
      </div>
      <label class="flex items-center gap-2 text-sm text-gray-600">
        <input type="checkbox" name="is_pinned" id="f-pinned" value="1" class="rounded border-gray-300 text-parish-700"> Pin to top
      </label>
      <div class="flex justify-end gap-3 pt-2">
        <button type="button" onclick="closeModal()" class="px-4 py-2.5 rounded-lg border border-gray-200 text-sm font-medium text-gray-600">Cancel</button>
        <button type="submit" class="px-5 py-2.5 rounded-lg bg-parish-700 hover:bg-parish-800 text-white text-sm font-medium">Save</button>
      </div>
    </form>
  </div>
</div>

<script>
var table;
function openModal(){ $('#ann-modal').removeClass('hidden').addClass('flex'); }
function closeModal(){ $('#ann-modal').addClass('hidden').removeClass('flex'); }
function resetForm(){ $('#ann-form')[0].reset(); $('#f-id').val(''); $('#ann-modal-title').text('New Announcement'); }
function editAnnouncement(id){
  $.get('<?= site_url('admin/announcement/get/') ?>' + id, function(res){
    var d = res.data;
    resetForm();
    $('#f-id').val(d.id); $('#f-title').val(d.title); $('#f-body').val(d.body);
    $('#f-category').val(d.category); $('#f-status').val(d.status);
    $('#f-publish').val(d.publish_date.replace(' ', 'T').substring(0,16));
    if (d.expiration_date) $('#f-expiry').val(d.expiration_date.replace(' ', 'T').substring(0,16));
    $('#f-pinned').prop('checked', d.is_pinned == 1);
    $('#ann-modal-title').text('Edit Announcement');
    openModal();
  });
}
function deleteAnnouncement(id){
  Swal.fire({ icon:'warning', title:'Delete this announcement?', showCancelButton:true, confirmButtonText:'Delete', confirmButtonColor:'#dc2626' })
    .then(function(r){ if(r.isConfirmed){
      $.post('<?= site_url('admin/announcement/delete/') ?>' + id, function(res){ toastr.success(res.message); table.ajax.reload(); });
    }});
}
$('#ann-form').on('submit', function(e){
  e.preventDefault();
  $.post('<?= site_url('admin/announcement/store') ?>', $(this).serialize(), function(res){
    if(res.success){ toastr.success(res.message); closeModal(); table.ajax.reload(); } else { toastr.error(res.message); }
  });
});
$(function(){
  document.getElementById('ann-modal').querySelector('.relative').addEventListener('click', function(e){ e.stopPropagation(); });
  table = $('#ann-table').DataTable({
    processing: true, serverSide: true,
    ajax: { url: '<?= site_url('admin/announcement/datatable') ?>', type: 'POST' },
    columns: [
      { data: 'title' }, { data: 'category' }, { data: 'publish_date' }, { data: 'expiration_date' },
      { data: 'status', orderable: false }, { data: 'actions', orderable: false, searchable: false }
    ],
    order: [[2, 'desc']],
    language: { search: '', searchPlaceholder: 'Search…' }
  });
});
</script>
