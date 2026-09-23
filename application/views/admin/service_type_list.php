<h1 class="text-2xl font-semibold text-gray-900 mb-1">Service Configuration</h1>
<p class="text-gray-500 text-sm mb-6">Set fees, descriptions, and requirement checklists for each service.</p>

<div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
  <?php foreach ($service_types as $s): ?>
  <div class="bg-white rounded-2xl border border-gray-100 p-5">
    <div class="flex items-center justify-between">
      <h3 class="font-semibold text-gray-800"><?= html_escape($s['name']) ?></h3>
      <span class="px-2 py-0.5 rounded-full text-[11px] font-medium <?= $s['is_active'] ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500' ?>"><?= $s['is_active'] ? 'Active' : 'Inactive' ?></span>
    </div>
    <p class="text-xs text-gray-400 mt-1 line-clamp-2"><?= html_escape($s['description']) ?></p>
    <div class="text-sm font-medium text-gold-600 mt-3"><?= peso($s['base_fee']) ?></div>
    <button onclick="editService(<?= $s['id'] ?>)" class="mt-4 w-full py-2 rounded-lg border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">Configure</button>
  </div>
  <?php endforeach; ?>
</div>

<div id="svc-modal" class="hidden fixed inset-0 z-50 items-center justify-center p-4">
  <div class="absolute inset-0 bg-black/50" onclick="closeModal()"></div>
  <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
    <h2 class="font-semibold text-gray-800 mb-4">Configure Service</h2>
    <form id="svc-form" class="space-y-4">
      <input type="hidden" name="id" id="f-id">
      <div>
        <label class="text-xs font-medium text-gray-500">Name</label>
        <input required name="name" id="f-name" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm">
      </div>
      <div>
        <label class="text-xs font-medium text-gray-500">Description</label>
        <textarea name="description" id="f-desc" rows="2" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm"></textarea>
      </div>
      <div class="grid sm:grid-cols-2 gap-4">
        <div>
          <label class="text-xs font-medium text-gray-500">Base Fee (₱)</label>
          <input required type="number" step="0.01" name="base_fee" id="f-fee" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm">
        </div>
        <div>
          <label class="text-xs font-medium text-gray-500">Duration (minutes)</label>
          <input type="number" name="duration_minutes" id="f-duration" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm">
        </div>
      </div>
      <label class="flex items-center gap-2 text-sm text-gray-600">
        <input type="checkbox" name="requires_approval_workflow" id="f-workflow" value="1" class="rounded border-gray-300 text-parish-700"> Requires multi-step approval workflow (e.g. wedding)
      </label>
      <label class="flex items-center gap-2 text-sm text-gray-600">
        <input type="checkbox" name="is_active" id="f-active" value="1" class="rounded border-gray-300 text-parish-700"> Active / visible to parishioners
      </label>

      <div class="border-t border-gray-100 pt-4">
        <div class="flex items-center justify-between mb-2">
          <label class="text-xs font-medium text-gray-500">Requirements</label>
          <button type="button" onclick="addRequirement()" class="text-xs text-parish-700 hover:underline font-medium">+ Add</button>
        </div>
        <div id="requirements-list" class="space-y-2"></div>
      </div>

      <div class="flex justify-end gap-3 pt-2">
        <button type="button" onclick="closeModal()" class="px-4 py-2.5 rounded-lg border border-gray-200 text-sm font-medium text-gray-600">Cancel</button>
        <button type="submit" class="px-5 py-2.5 rounded-lg bg-parish-700 hover:bg-parish-800 text-white text-sm font-medium">Save</button>
      </div>
    </form>
  </div>
</div>

<script>
function closeModal(){ $('#svc-modal').addClass('hidden').removeClass('flex'); }
function editService(id){
  $.get('<?= site_url('admin/service_type/get/') ?>' + id, function(res){
    var d = res.data;
    $('#f-id').val(d.id); $('#f-name').val(d.name); $('#f-desc').val(d.description);
    $('#f-fee').val(d.base_fee); $('#f-duration').val(d.duration_minutes);
    $('#f-workflow').prop('checked', d.requires_approval_workflow == 1);
    $('#f-active').prop('checked', d.is_active == 1);

    var $list = $('#requirements-list').empty();
    (d.requirements || []).forEach(function(r){
      $list.append(
        '<div class="flex items-center gap-2 text-sm">' +
          '<span class="flex-1 text-gray-600">'+ r.label + (r.is_required == 1 ? ' <span class="text-red-500">*</span>' : '') +'</span>' +
          '<button type="button" onclick="removeRequirement(' + r.id + ', this)" class="text-red-500 text-xs hover:underline">Remove</button>' +
        '</div>'
      );
    });
    $('#svc-modal').removeClass('hidden').addClass('flex');
  });
}
function addRequirement(){
  Swal.fire({ title: 'New Requirement', input: 'text', inputPlaceholder: 'e.g. Birth Certificate (PSA)', showCancelButton: true, confirmButtonColor: '#235a38' })
    .then(function(r){
      if(r.isConfirmed && r.value){
        $.post('<?= site_url('admin/service_type/add_requirement') ?>', { service_type_id: $('#f-id').val(), label: r.value, is_required: 1 }, function(res){
          toastr.success(res.message);
          editService($('#f-id').val());
        });
      }
    });
}
function removeRequirement(id, btn){
  $.post('<?= site_url('admin/service_type/delete_requirement/') ?>' + id, function(){ $(btn).closest('div').remove(); });
}
$(function(){
  document.getElementById('svc-modal').querySelector('.relative').addEventListener('click', function(e){ e.stopPropagation(); });
});
$('#svc-form').on('submit', function(e){
  e.preventDefault();
  $.post('<?= site_url('admin/service_type/store') ?>', $(this).serialize(), function(res){
    if(res.success){ toastr.success(res.message); setTimeout(function(){ location.reload(); }, 700); } else { toastr.error(res.message); }
  });
});
</script>
