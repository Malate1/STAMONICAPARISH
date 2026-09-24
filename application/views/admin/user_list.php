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
      <thead><tr><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Joined</th><th class="text-center">Actions</th></tr></thead>
      <tbody></tbody>
    </table>
  </div>
</div>

<div id="user-modal" class="hidden fixed inset-0 z-50 items-center justify-center p-4">
  <div class="absolute inset-0 bg-black/50" onclick="closeModal()"></div>
  <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-2xl p-6 max-h-[90vh] overflow-y-auto">
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
      <div id="priest-fields" class="hidden space-y-4 rounded-2xl border border-parish-100 bg-parish-50/50 p-4">
        <div class="flex items-center gap-2 text-sm font-semibold text-parish-800">
          <i class="ph ph-church"></i> Public Priest Profile
        </div>
        <div class="grid sm:grid-cols-2 gap-4">
          <div>
            <label class="text-xs font-medium text-gray-500">Clergy Title</label>
            <input name="priest_title" id="f-priest-title" value="Rev. Fr." placeholder="Rev. Fr." class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm bg-white">
          </div>
          <div>
            <label class="text-xs font-medium text-gray-500">Position</label>
            <select name="position" id="f-position" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm bg-white">
              <option value="Parish Priest">Parish Priest</option>
              <option value="Parochial Vicar">Parochial Vicar</option>
              <option value="Assistant Priest">Assistant Priest</option>
              <option value="Resident Priest">Resident Priest</option>
              <option value="Visiting Priest">Visiting Priest</option>
            </select>
          </div>
        </div>
        <div>
          <label class="text-xs font-medium text-gray-500">Short Biography</label>
          <textarea name="priest_bio" id="f-priest-bio" rows="4" placeholder="Brief pastoral role, ministry background, or message to parishioners..." class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm bg-white"></textarea>
        </div>
        <div>
          <label class="text-xs font-medium text-gray-500">Photo URL or Site Path <span class="font-normal text-gray-400">(optional)</span></label>
          <input name="avatar" id="f-avatar" placeholder="https://... or uploads/priests/fr-name.jpg" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm bg-white">
          <p class="text-[11px] text-gray-400 mt-1">If empty, the public site shows a styled priest/church placeholder.</p>
        </div>
        <div class="grid sm:grid-cols-2 gap-4 items-end">
          <div>
            <label class="text-xs font-medium text-gray-500">Display Order</label>
            <input type="number" min="0" name="priest_display_order" id="f-priest-order" value="0" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm bg-white">
          </div>
          <label class="flex items-center gap-2 text-sm text-gray-600 pb-2">
            <input type="checkbox" name="priest_is_public" id="f-priest-public" value="1" checked class="rounded border-gray-300 text-parish-700">
            Show on public Priests page
          </label>
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
function togglePriestFields(){
  var isPriest = parseInt($('#f-role').val(), 10) === <?= ROLE_PRIEST ?>;
  $('#priest-fields').toggleClass('hidden', !isPriest);
}
function resetForm(){
  $('#user-form')[0].reset();
  $('#f-id').val('');
  $('#f-priest-title').val('Rev. Fr.');
  $('#f-position').val('Assistant Priest');
  $('#f-priest-order').val(0);
  $('#f-priest-public').prop('checked', true);
  $('#user-modal-title').text('Add Account');
  $('#password-field').show();
  togglePriestFields();
}
function editUser(id){
  $.get('<?= site_url('admin/users/get/') ?>' + id, function(res){
    var d = res.data;
    resetForm();
    $('#f-id').val(d.id); $('#f-first').val(d.first_name); $('#f-last').val(d.last_name);
    $('#f-email').val(d.email); $('#f-mobile').val(d.mobile_number); $('#f-role').val(d.role_id);
    $('#f-priest-title').val(d.priest_title || 'Rev. Fr.');
    $('#f-position').val(d.priest_position || 'Assistant Priest');
    $('#f-priest-bio').val(d.priest_bio || '');
    $('#f-avatar').val(d.avatar || '');
    $('#f-priest-order').val(d.priest_display_order || 0);
    $('#f-priest-public').prop('checked', d.priest_is_public === null || parseInt(d.priest_is_public, 10) === 1);
    togglePriestFields();
    $('#password-field').hide();
    $('#user-modal-title').text('Edit Account');
    openModal();
  });
}
function resetUserPassword(id){
  Swal.fire({
    icon:'warning',
    title:'Reset this account password?',
    text:'A new temporary password will be generated. The previous password will stop working immediately.',
    showCancelButton:true,
    confirmButtonText:'Reset Password',
    confirmButtonColor:'#235a38'
  }).then(function(result){
    if(!result.isConfirmed) return;

    $.post('<?= site_url('admin/users/reset-password/') ?>' + id, function(res){
      if(!res.success){
        Swal.fire({icon:'error', title:'Could not reset password', text:res.message || 'Please try again.', confirmButtonColor:'#235a38'});
        return;
      }

      var temp = String(res.temporary_password || '');
      Swal.fire({
        icon:'success',
        title:'Password Reset',
        html:
          '<p class="text-sm text-gray-500 mb-3">Give this temporary password directly to the account owner.</p>' +
          '<div class="rounded-xl border border-gray-200 bg-gray-50 p-4">' +
            '<div class="text-[11px] uppercase tracking-wider text-gray-400 mb-1">Temporary Password</div>' +
            '<div id="temporary-password-value" class="font-mono text-lg font-bold text-gray-900 select-all">' + temp + '</div>' +
          '</div>' +
          '<p class="text-xs text-amber-700 mt-3">For security, do not send this password in a public or shared channel.</p>',
        confirmButtonText:'Done',
        confirmButtonColor:'#235a38',
        showDenyButton:true,
        denyButtonText:'Copy Password',
        denyButtonColor:'#4b5563'
      }).then(function(copyResult){
        if(copyResult.isDenied && navigator.clipboard){
          navigator.clipboard.writeText(temp).then(function(){ toastr.success('Temporary password copied.'); });
        }
      });
    }).fail(function(){
      Swal.fire({icon:'error', title:'Could not reset password', text:'The server did not accept the request.', confirmButtonColor:'#235a38'});
    });
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
  $('#f-role').on('change', togglePriestFields);
  togglePriestFields();
  document.getElementById('user-modal').querySelector('.relative').addEventListener('click', function(e){ e.stopPropagation(); });
  table = $('#user-table').DataTable({
    processing: true, serverSide: true,
    ajax: { url: '<?= site_url('admin/users/datatable') ?>', type: 'POST' },
    columns: [
      { data: 'name' }, { data: 'email' }, { data: 'role' }, { data: 'status', orderable: false },
      { data: 'created_at' }, { data: 'actions', orderable: false, searchable: false, className: 'whitespace-nowrap' }
    ],
    language: { search: '', searchPlaceholder: 'Search accounts…' }
  });
});
</script>
