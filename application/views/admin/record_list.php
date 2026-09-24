<?php $base = strpos(uri_string(), 'staff') === 0 ? 'staff' : 'admin'; ?>
<div class="flex items-center justify-between mb-6 flex-wrap gap-3">
  <div>
    <h1 class="text-2xl font-semibold text-gray-900">Sacramental Records</h1>
    <p class="text-gray-500 text-sm mt-1">Digitized parish registry — baptism, confirmation, marriage &amp; funeral records.</p>
  </div>
  <div class="flex gap-2">
    <select id="filter-type" class="px-3 py-2 rounded-lg border border-gray-200 text-sm">
      <option value="">All Types</option>
      <option value="baptism">Baptism</option><option value="confirmation">Confirmation</option>
      <option value="communion">Communion</option><option value="marriage">Marriage</option><option value="funeral">Funeral</option>
    </select>
    <button onclick="resetForm(); openModal()" class="px-4 py-2.5 rounded-lg bg-parish-700 hover:bg-parish-800 text-white text-sm font-medium flex items-center gap-2"><i class="ph ph-plus"></i> Add Record</button>
  </div>
</div>

<div class="bg-white rounded-2xl border border-gray-100 p-6">
  <div class="overflow-x-auto">
    <table id="rec-table" class="w-full text-sm">
      <thead><tr><th>Type</th><th>Name</th><th>Date</th><th>Parents</th><th>Registry</th><th class="text-center">Actions</th></tr></thead>
      <tbody></tbody>
    </table>
  </div>
</div>

<div id="rec-modal" class="hidden fixed inset-0 z-50 items-center justify-center p-4">
  <div class="absolute inset-0 bg-black/50" onclick="closeModal()"></div>
  <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-xl p-6 max-h-[90vh] overflow-y-auto">
    <h2 class="font-semibold text-gray-800 mb-4" id="rec-modal-title">Add Record</h2>
    <form id="rec-form" class="space-y-4">
      <input type="hidden" name="id" id="f-id">
      <div class="grid sm:grid-cols-2 gap-4">
        <div>
          <label class="text-xs font-medium text-gray-500">Record Type</label>
          <select required name="record_type" id="f-type" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm">
            <option value="baptism">Baptism</option><option value="confirmation">Confirmation</option>
            <option value="communion">Communion</option><option value="marriage">Marriage</option><option value="funeral">Funeral</option>
          </select>
        </div>
        <div>
          <label class="text-xs font-medium text-gray-500">Sacrament / Event Date</label>
          <input type="date" name="sacrament_date" id="f-sdate" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm">
        </div>
      </div>
      <div><label class="text-xs font-medium text-gray-500">Full Name</label><input required name="full_name" id="f-name" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm"></div>
      <div class="grid sm:grid-cols-2 gap-4">
        <div><label class="text-xs font-medium text-gray-500">Birth Date</label><input type="date" name="birth_date" id="f-bdate" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm"></div>
        <div><label class="text-xs font-medium text-gray-500">Minister</label><input name="minister_name" id="f-minister" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm"></div>
      </div>
      <div class="grid sm:grid-cols-2 gap-4">
        <div><label class="text-xs font-medium text-gray-500">Father's Name</label><input name="father_name" id="f-father" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm"></div>
        <div><label class="text-xs font-medium text-gray-500">Mother's Name</label><input name="mother_name" id="f-mother" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm"></div>
      </div>
      <div><label class="text-xs font-medium text-gray-500">Spouse Name (marriage only)</label><input name="spouse_name" id="f-spouse" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm"></div>
      <div class="grid sm:grid-cols-3 gap-4">
        <div><label class="text-xs font-medium text-gray-500">Book No.</label><input name="registry_book" id="f-book" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm"></div>
        <div><label class="text-xs font-medium text-gray-500">Page No.</label><input name="registry_page" id="f-page" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm"></div>
        <div><label class="text-xs font-medium text-gray-500">Entry No.</label><input name="registry_entry_no" id="f-entry" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm"></div>
      </div>
      <div><label class="text-xs font-medium text-gray-500">Remarks</label><textarea name="remarks" id="f-remarks" rows="2" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm"></textarea></div>
      <div class="flex justify-end gap-3 pt-2">
        <button type="button" onclick="closeModal()" class="px-4 py-2.5 rounded-lg border border-gray-200 text-sm font-medium text-gray-600">Cancel</button>
        <button type="submit" class="px-5 py-2.5 rounded-lg bg-parish-700 hover:bg-parish-800 text-white text-sm font-medium">Save Record</button>
      </div>
    </form>
  </div>
</div>

<script>
var table;
var base = '<?= $base ?>';
function openModal(){ $('#rec-modal').removeClass('hidden').addClass('flex'); }
function closeModal(){ $('#rec-modal').addClass('hidden').removeClass('flex'); }
function resetForm(){ $('#rec-form')[0].reset(); $('#f-id').val(''); $('#rec-modal-title').text('Add Record'); }
function editRecord(id){
  $.get('<?= site_url('/') ?>' + base + '/record/get/' + id, function(res){
    var d = res.data;
    resetForm();
    $('#f-id').val(d.id); $('#f-type').val(d.record_type); $('#f-name').val(d.full_name);
    $('#f-sdate').val(d.sacrament_date); $('#f-bdate').val(d.birth_date); $('#f-minister').val(d.minister_name);
    $('#f-father').val(d.father_name); $('#f-mother').val(d.mother_name); $('#f-spouse').val(d.spouse_name);
    $('#f-book').val(d.registry_book); $('#f-page').val(d.registry_page); $('#f-entry').val(d.registry_entry_no);
    $('#f-remarks').val(d.remarks);
    $('#rec-modal-title').text('Edit Record');
    openModal();
  });
}
$('#rec-form').on('submit', function(e){
  e.preventDefault();
  $.post('<?= site_url('/') ?>' + base + '/record/store', $(this).serialize(), function(res){
    if(res.success){ toastr.success(res.message); closeModal(); table.ajax.reload(); } else { toastr.error(res.message); }
  });
});
$(function(){
  document.getElementById('rec-modal').querySelector('.relative').addEventListener('click', function(e){ e.stopPropagation(); });
  table = $('#rec-table').DataTable({
    processing: true, serverSide: true,
    ajax: { url: '<?= site_url('/') ?>' + base + '/record/datatable', type: 'POST', data: function(d){ d.record_type = $('#filter-type').val(); } },
    columns: [
      { data: 'type' }, { data: 'full_name' }, { data: 'sacrament_date' }, { data: 'parents' },
      { data: 'registry' }, { data: 'actions', orderable: false, searchable: false }
    ],
    language: { search: '', searchPlaceholder: 'Search records…' }
  });
  $('#filter-type').on('change', function(){ table.ajax.reload(); });
});
</script>
