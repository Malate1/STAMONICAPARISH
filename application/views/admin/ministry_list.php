<div class="flex items-center justify-between mb-6">
  <div>
    <h1 class="text-2xl font-semibold text-gray-900">Ministries</h1>
    <p class="text-gray-500 text-sm mt-1">Manage the ministry directory and view "interested to join" submissions.</p>
  </div>
  <div class="flex gap-2">
    <button onclick="viewInterests()" class="px-4 py-2.5 rounded-lg border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">View Interests</button>
    <button onclick="resetForm(); openModal()" class="px-4 py-2.5 rounded-lg bg-parish-700 hover:bg-parish-800 text-white text-sm font-medium flex items-center gap-2"><i class="ph ph-plus"></i> New Ministry</button>
  </div>
</div>

<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
  <?php foreach ($all as $m): ?>
  <div class="bg-white rounded-2xl border border-gray-100 p-5">
    <div class="flex items-center justify-between">
      <h3 class="font-semibold text-gray-800"><?= html_escape($m['name']) ?></h3>
      <span class="px-2 py-0.5 rounded-full text-[11px] font-medium <?= $m['is_active'] ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500' ?>"><?= $m['is_active'] ? 'Active' : 'Inactive' ?></span>
    </div>
    <p class="text-xs text-gray-400 mt-1 line-clamp-2"><?= html_escape($m['description']) ?></p>
    <button onclick='editMinistry(<?= json_encode($m) ?>)' class="mt-4 w-full py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">Edit</button>
  </div>
  <?php endforeach; ?>
</div>

<div id="min-modal" class="hidden fixed inset-0 z-50 items-center justify-center p-4">
  <div class="absolute inset-0 bg-black/50" onclick="closeModal()"></div>
  <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
    <h2 class="font-semibold text-gray-800 mb-4" id="min-modal-title">New Ministry</h2>
    <form id="min-form" class="space-y-4">
      <input type="hidden" name="id" id="f-id">
      <div><label class="text-xs font-medium text-gray-500">Name</label><input required name="name" id="f-name" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm"></div>
      <div><label class="text-xs font-medium text-gray-500">Description</label><textarea name="description" id="f-desc" rows="3" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm"></textarea></div>
      <div><label class="text-xs font-medium text-gray-500">Contact Person</label><input name="contact_person" id="f-contact" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm"></div>
      <label class="flex items-center gap-2 text-sm text-gray-600"><input type="checkbox" name="is_active" id="f-active" value="1" checked class="rounded border-gray-300 text-parish-700"> Active / visible on public site</label>
      <div class="flex justify-end gap-3 pt-2">
        <button type="button" onclick="closeModal()" class="px-4 py-2.5 rounded-lg border border-gray-200 text-sm font-medium text-gray-600">Cancel</button>
        <button type="submit" class="px-5 py-2.5 rounded-lg bg-parish-700 hover:bg-parish-800 text-white text-sm font-medium">Save</button>
      </div>
    </form>
  </div>
</div>

<div id="interest-modal" class="hidden fixed inset-0 z-50 items-center justify-center p-4">
  <div class="absolute inset-0 bg-black/50" onclick="$('#interest-modal').addClass('hidden').removeClass('flex')"></div>
  <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 max-h-[80vh] overflow-y-auto">
    <h2 class="font-semibold text-gray-800 mb-4">Ministry Interest Submissions</h2>
    <div id="interest-list" class="space-y-3 text-sm"></div>
  </div>
</div>

<script>
function openModal(){ $('#min-modal').removeClass('hidden').addClass('flex'); }
function closeModal(){ $('#min-modal').addClass('hidden').removeClass('flex'); }
function resetForm(){ $('#min-form')[0].reset(); $('#f-id').val(''); $('#min-modal-title').text('New Ministry'); }
function editMinistry(m){
  resetForm();
  $('#f-id').val(m.id); $('#f-name').val(m.name); $('#f-desc').val(m.description); $('#f-contact').val(m.contact_person);
  $('#f-active').prop('checked', m.is_active == 1);
  $('#min-modal-title').text('Edit Ministry');
  openModal();
}
function viewInterests(){
  $.get('<?= site_url('admin/ministry/interests') ?>', function(res){
    var $list = $('#interest-list').empty();
    if(!res.data.length){ $list.append('<p class="text-gray-400">No submissions yet.</p>'); }
    res.data.forEach(function(i){
      $list.append(
        '<div class="p-3 rounded-lg border border-gray-100">' +
          '<div class="font-medium text-gray-700">'+ i.full_name +' — <span class="text-parish-700">'+ i.ministry_name +'</span></div>' +
          '<div class="text-xs text-gray-400">'+ i.contact_number +'</div>' +
          (i.message ? '<div class="text-xs text-gray-500 mt-1">'+ i.message +'</div>' : '') +
        '</div>'
      );
    });
    $('#interest-modal').removeClass('hidden').addClass('flex');
  });
}
$(function(){
  document.getElementById('min-modal').querySelector('.relative').addEventListener('click', function(e){ e.stopPropagation(); });
});
$('#min-form').on('submit', function(e){
  e.preventDefault();
  $.post('<?= site_url('admin/ministry/store') ?>', $(this).serialize(), function(res){
    if(res.success){ toastr.success(res.message); setTimeout(function(){ location.reload(); }, 700); } else { toastr.error(res.message); }
  });
});
</script>
