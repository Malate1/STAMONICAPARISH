<div class="flex items-center justify-between mb-6">
  <div>
    <h1 class="text-2xl font-semibold text-gray-900">Mass Schedule</h1>
    <p class="text-gray-500 text-sm mt-1">Manage weekly and special Mass schedules.</p>
  </div>
  <button onclick="resetForm(); openScheduleModal()" class="px-4 py-2.5 rounded-lg bg-parish-700 hover:bg-parish-800 text-white text-sm font-medium flex items-center gap-2"><i class="ph ph-plus"></i> Add Schedule</button>
</div>

<div class="bg-white rounded-2xl border border-gray-100 p-6">
  <div class="overflow-x-auto">
    <table id="schedule-table" class="w-full text-sm">
      <thead><tr><th>Title</th><th>Recurs</th><th>Time</th><th>Location</th><th>Presider</th><th>Status</th><th class="text-center">Actions</th></tr></thead>
      <tbody></tbody>
    </table>
  </div>
</div>

<div id="schedule-modal" class="hidden fixed inset-0 z-50 items-center justify-center p-4">
  <div class="absolute inset-0 bg-black/50" onclick="closeScheduleModal()"></div>
  <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
    <h2 class="font-semibold text-gray-800 mb-4" id="schedule-modal-title">Add Mass Schedule</h2>
    <form id="schedule-form" class="space-y-4">
      <input type="hidden" name="id" id="f-id">
      <div>
        <label class="text-xs font-medium text-gray-500">Title</label>
        <input required name="title" id="f-title" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm">
      </div>
      <div>
        <label class="text-xs font-medium text-gray-500">Recurrence</label>
        <select name="recurrence_type" id="f-recurrence" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm" onchange="toggleRecurrence()">
          <option value="weekly">Weekly (recurring day)</option>
          <option value="specific">Specific Date (special schedule)</option>
        </select>
      </div>
      <div id="weekly-field">
        <label class="text-xs font-medium text-gray-500">Day of Week</label>
        <select name="day_of_week" id="f-day" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm">
          <option value="0">Sunday</option><option value="1">Monday</option><option value="2">Tuesday</option>
          <option value="3">Wednesday</option><option value="4">Thursday</option><option value="5">Friday</option><option value="6">Saturday</option>
        </select>
      </div>
      <div id="specific-field" class="hidden">
        <label class="text-xs font-medium text-gray-500">Specific Date</label>
        <input type="date" name="specific_date" id="f-date" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm">
      </div>
      <div class="grid sm:grid-cols-2 gap-4">
        <div>
          <label class="text-xs font-medium text-gray-500">Mass Time</label>
          <input required type="time" name="mass_time" id="f-time" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm">
        </div>
        <div>
          <label class="text-xs font-medium text-gray-500">Schedule Type</label>
          <select name="schedule_type" id="f-type" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm">
            <option value="regular">Regular</option><option value="first_friday">First Friday</option><option value="holy_day">Holy Day</option>
            <option value="fiesta">Fiesta</option><option value="christmas">Christmas</option><option value="holy_week">Holy Week</option>
            <option value="simbang_gabi">Simbang Gabi</option><option value="special">Special</option><option value="memorial">Memorial</option><option value="healing">Healing</option>
          </select>
        </div>
      </div>
      <div class="grid sm:grid-cols-2 gap-4">
        <div>
          <label class="text-xs font-medium text-gray-500">Location</label>
          <input name="location" id="f-location" value="Main Church" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm">
        </div>
        <div>
          <label class="text-xs font-medium text-gray-500">Presider</label>
          <select name="presider_id" id="f-presider" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm">
            <option value="">— None —</option>
            <?php foreach ($priests as $p): ?>
              <option value="<?= $p['id'] ?>"><?= html_escape($p['first_name'] . ' ' . $p['last_name']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div class="flex justify-end gap-3 pt-2">
        <button type="button" onclick="closeScheduleModal()" class="px-4 py-2.5 rounded-lg border border-gray-200 text-sm font-medium text-gray-600">Cancel</button>
        <button type="submit" class="px-5 py-2.5 rounded-lg bg-parish-700 hover:bg-parish-800 text-white text-sm font-medium">Save Schedule</button>
      </div>
    </form>
  </div>
</div>

<script>
var table;
function openScheduleModal(){ $('#schedule-modal').removeClass('hidden').addClass('flex'); }
function closeScheduleModal(){ $('#schedule-modal').addClass('hidden').removeClass('flex'); }
function toggleRecurrence(){
  var v = $('#f-recurrence').val();
  $('#weekly-field').toggleClass('hidden', v !== 'weekly');
  $('#specific-field').toggleClass('hidden', v !== 'specific');
}
function resetForm(){
  $('#schedule-form')[0].reset();
  $('#f-id').val('');
  $('#schedule-modal-title').text('Add Mass Schedule');
  toggleRecurrence();
}
function editSchedule(id){
  $.get('<?= site_url('admin/mass_schedule/get/') ?>' + id, function(res){
    var d = res.data;
    resetForm();
    $('#f-id').val(d.id);
    $('#f-title').val(d.title);
    $('#f-time').val(d.mass_time.substring(0,5));
    $('#f-type').val(d.schedule_type);
    $('#f-location').val(d.location);
    $('#f-presider').val(d.presider_id || '');
    if (d.specific_date) {
      $('#f-recurrence').val('specific');
      $('#f-date').val(d.specific_date);
    } else {
      $('#f-recurrence').val('weekly');
      $('#f-day').val(d.day_of_week);
    }
    toggleRecurrence();
    $('#schedule-modal-title').text('Edit Mass Schedule');
    openScheduleModal();
  });
}
function deleteSchedule(id){
  Swal.fire({ icon:'warning', title:'Delete this schedule?', showCancelButton:true, confirmButtonText:'Delete', confirmButtonColor:'#dc2626' })
    .then(function(r){ if(r.isConfirmed){
      $.post('<?= site_url('admin/mass_schedule/delete/') ?>' + id, function(res){ toastr.success(res.message); table.ajax.reload(); });
    }});
}
$('#schedule-form').on('submit', function(e){
  e.preventDefault();
  $.post('<?= site_url('admin/mass_schedule/store') ?>', $(this).serialize(), function(res){
    if(res.success){ toastr.success(res.message); closeScheduleModal(); table.ajax.reload(); }
    else { toastr.error(res.message); }
  });
});
$(function(){
  document.getElementById('schedule-modal').querySelector('.relative').addEventListener('click', function(e){ e.stopPropagation(); });
  table = $('#schedule-table').DataTable({
    processing: true, serverSide: true,
    ajax: { url: '<?= site_url('admin/mass_schedule/datatable') ?>', type: 'POST' },
    columns: [
      { data: 'title' }, { data: 'when' }, { data: 'mass_time' }, { data: 'location' }, { data: 'priest' },
      { data: 'status', orderable: false }, { data: 'actions', orderable: false, searchable: false }
    ],
    language: { search: '', searchPlaceholder: 'Search…' }
  });
});
</script>
