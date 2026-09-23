<div class="flex items-center justify-between mb-6">
  <div>
    <h1 class="text-2xl font-semibold text-gray-900">Accounts</h1>
    <p class="text-gray-500 text-sm mt-1">Manage parishioner, secretary, priest, and admin accounts.</p>
  </div>
  <button onclick="resetForm(); openModal()" class="px-4 py-2.5 rounded-lg bg-parish-700 hover:bg-parish-800 text-white text-sm font-medium flex items-center gap-2"><i class="ph ph-user-plus"></i> Add Account</button>
</div>

<div class="bg-white rounded-2xl border border-gray-100 p-6">
  <div class="overflow-x-auto">
    <table id="user-table" class="w-full text-sm">
      <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Joined</th><th></th></tr></thead>
      <tbody></tbody>
    </table>
  </div>
</div>

<div id="user-modal" class="hidden fixed inset-0 z-50 items-center justify-center p-4">
  <div class="absolute inset-0 bg-black/50" onclick="closeModal()"></div>
  <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
    <h2 class="font-semibold text-gray-800 mb-4" id="user-modal-title">Add Account</h2>
    <form id="user-form" class="space-y-4">
      <input type="hidden" name="id" id="f-id">
      <div class="grid sm:grid-cols-2 gap-4">
        <div>
          <label class="text-xs font-medium text-gray-500">First Name</label>
          <input required name="first_name" id="f-first" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm">
        </div>
        <div>
          <label class="text-xs font-medium text-gray-500">Last Name</label>
          <input required name="last_name" id="f-last" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm">
        </div>
      </div>
      <div>
        <label class="text-xs font-medium text-gray-500">Email Address</label>
        <input required type="email" name="email" id="f-email" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm">
      </div>
      <div class="grid sm:grid-cols-2 gap-4">
        <div>
          <label class="text-xs font-medium text-gray-500">Mobile Number</label>
          <input name="mobile_number" id="f-mobile" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm">
        </div>
        <div>
          <label class="text-xs font-medium text-gray-500">Role</label>
          <select required name="role_id" id="f-role" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm">
            <option value="1">Administrator</option>
            <option value="2">Parish Secretary</option>
            <option value="3">Priest</option>
            <option value="4">Parishioner</option>
          </select>
        </div>
      </div>
      <div id="password-field">
        <label class="text-xs font-medium text-gray-500">Temporary Password (leave blank to auto-generate)</label>
        <input type="text" name="password" id="f-password" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm">
      </div>
      <div class="flex justify-end gap-3 pt-2">
        <button type="button" onclick="closeModal()" class="px-4 py-2.5 rounded-lg border border-gray-200 text-sm font-medium text-gray-600">Cancel</button>
        <button type="submit" class="px-5 py-2.5 rounded-lg bg-parish-700 hover:bg-parish-800 text-white text-sm font-medium">Save Account</button>
      </div>
    </form>
  </div>
</div>

<script>
var table;
function openModal(){ $('#user-modal').removeClass('hidden').addClass('flex'); }
function closeModal(){ $('#user-modal').addClass('hidden').removeClass('flex'); }
function resetForm(){ $('#user-form')[0].reset(); $('#f-id').val(''); $('#user-modal-title').text('Add Account'); $('#password-field').show(); }
function editUser(id){
  $.get('<?= site_url('admin/users/get/') ?>' + id, function(res){
    var d = res.data;
    resetForm();
    $('#f-id').val(d.id); $('#f-first').val(d.first_name); $('#f-last').val(d.last_name);
    $('#f-email').val(d.email); $('#f-mobile').val(d.mobile_number); $('#f-role').val(d.role_id);
    $('#password-field').hide();
    $('#user-modal-title').text('Edit Account');
    openModal();
  });
}
function toggleUser(id, status){
  var action = status === 'active' ? 'deactivate' : 'activate';
  Swal.fire({ icon:'question', title:'Are you sure you want to ' + action + ' this account?', showCancelButton:true, confirmButtonColor:'#235a38' })
    .then(function(r){ if(r.isConfirmed){
      $.post('<?= site_url('admin/users/toggle/') ?>' + id, function(){ toastr.success('Account updated.'); table.ajax.reload(); });
    }});
}
$('#user-form').on('submit', function(e){
  e.preventDefault();
  $.post('<?= site_url('admin/users/store') ?>', $(this).serialize(), function(res){
    if(res.success){ Swal.fire({icon:'success', title:'Saved', text: res.message, confirmButtonColor:'#235a38'}); closeModal(); table.ajax.reload(); }
    else { toastr.error(res.message); }
  });
});
$(function(){
  document.getElementById('user-modal').querySelector('.relative').addEventListener('click', function(e){ e.stopPropagation(); });
  table = $('#user-table').DataTable({
    processing: true, serverSide: true,
    ajax: { url: '<?= site_url('admin/users/datatable') ?>', type: 'POST' },
    columns: [
      { data: 'name' }, { data: 'email' }, { data: 'role' }, { data: 'status', orderable: false },
      { data: 'created_at' }, { data: 'actions', orderable: false, searchable: false }
    ],
    language: { search: '', searchPlaceholder: 'Search accounts…' }
  });
});
</script>
