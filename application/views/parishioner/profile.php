<h1 class="text-2xl font-semibold text-gray-900 mb-6">My Profile</h1>

<div class="grid lg:grid-cols-2 gap-6">
  <div class="bg-white rounded-2xl border border-gray-100 p-6">
    <h2 class="font-semibold text-gray-800 mb-4">Profile Information</h2>
    <form id="profile-form" class="space-y-4">
      <div class="grid sm:grid-cols-2 gap-4">
        <div>
          <label class="text-xs font-medium text-gray-500">First Name</label>
          <input required name="first_name" value="<?= html_escape($current_user['first_name']) ?>" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm">
        </div>
        <div>
          <label class="text-xs font-medium text-gray-500">Last Name</label>
          <input required name="last_name" value="<?= html_escape($current_user['last_name']) ?>" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm">
        </div>
      </div>
      <div>
        <label class="text-xs font-medium text-gray-500">Email Address</label>
        <input disabled value="<?= html_escape($current_user['email']) ?>" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-100 bg-gray-50 text-sm text-gray-400">
      </div>
      <div>
        <label class="text-xs font-medium text-gray-500">Mobile Number</label>
        <input required name="mobile_number" value="<?= html_escape($current_user['mobile_number']) ?>" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm">
      </div>
      <div>
        <label class="text-xs font-medium text-gray-500">Address</label>
        <input name="address" value="<?= html_escape($current_user['address']) ?>" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm">
      </div>
      <button type="submit" class="px-5 py-2.5 rounded-lg bg-parish-700 hover:bg-parish-800 text-white text-sm font-medium">Save Changes</button>
    </form>
  </div>

  <div class="bg-white rounded-2xl border border-gray-100 p-6">
    <h2 class="font-semibold text-gray-800 mb-4">Change Password</h2>
    <form id="password-form" class="space-y-4">
      <div>
        <label class="text-xs font-medium text-gray-500">Current Password</label>
        <input required type="password" name="current_password" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm">
      </div>
      <div>
        <label class="text-xs font-medium text-gray-500">New Password</label>
        <input required type="password" minlength="8" name="new_password" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm">
      </div>
      <div>
        <label class="text-xs font-medium text-gray-500">Confirm New Password</label>
        <input required type="password" minlength="8" name="new_password_confirm" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm">
      </div>
      <button type="submit" class="px-5 py-2.5 rounded-lg bg-gray-800 hover:bg-gray-900 text-white text-sm font-medium">Update Password</button>
    </form>
  </div>
</div>

<script>
$('#profile-form').on('submit', function(e){
  e.preventDefault();
  $.post('<?= site_url('my/profile/update') ?>', $(this).serialize(), function(res){
    if(res.success){ toastr.success(res.message); } else { toastr.error(res.message); }
  });
});
$('#password-form').on('submit', function(e){
  e.preventDefault();
  $.post('<?= site_url('my/profile/change-password') ?>', $(this).serialize(), function(res){
    if(res.success){ toastr.success(res.message); $('#password-form')[0].reset(); } else { toastr.error(res.message); }
  });
});
</script>
