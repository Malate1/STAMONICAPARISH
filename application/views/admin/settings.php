<h1 class="text-2xl font-semibold text-gray-900 mb-1">System Settings</h1>
<p class="text-gray-500 text-sm mb-6">Parish information and GCash payment configuration.</p>

<form id="settings-form" enctype="multipart/form-data" class="max-w-2xl space-y-6">
  <div class="bg-white rounded-2xl border border-gray-100 p-6 space-y-4">
    <h2 class="font-semibold text-gray-800">Parish Information</h2>
    <div>
      <label class="text-xs font-medium text-gray-500">Parish Name</label>
      <input name="parish_name" value="<?= html_escape($settings['parish_name'] ?? '') ?>" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm">
    </div>
    <div>
      <label class="text-xs font-medium text-gray-500">Address</label>
      <input name="parish_address" value="<?= html_escape($settings['parish_address'] ?? '') ?>" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm">
    </div>
    <div>
      <label class="text-xs font-medium text-gray-500">Contact Number</label>
      <input name="parish_contact" value="<?= html_escape($settings['parish_contact'] ?? '') ?>" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm">
    </div>
  </div>

  <div class="bg-white rounded-2xl border border-gray-100 p-6 space-y-4">
    <div>
      <h2 class="font-semibold text-gray-800">Social Media</h2>
      <p class="text-xs text-gray-400 mt-1">Public social links shown in the website footer.</p>
    </div>
    <div>
      <label class="text-xs font-medium text-gray-500">Facebook Page URL</label>
      <input type="url" name="facebook_url" value="<?= html_escape($settings['facebook_url'] ?? '') ?>" placeholder="https://www.facebook.com/yourpage" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm">
      <p class="text-[11px] text-gray-400 mt-1">Leave blank to hide the Facebook link from the public footer.</p>
    </div>
  </div>

  <div class="bg-white rounded-2xl border border-gray-100 p-6 space-y-4">
    <h2 class="font-semibold text-gray-800">Booking Capacity</h2>
    <div>
      <label class="text-xs font-medium text-gray-500">Priests Available for Concurrent Bookings</label>
      <input type="number" min="1" max="10" name="priest_booking_capacity" value="<?= html_escape($settings['priest_booking_capacity'] ?? '2') ?>" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm">
      <p class="text-[11px] text-gray-400 mt-1">Current parish setup: 2 priests. The booking engine will never offer more simultaneous priest-required services than this capacity or the number of active priest accounts, whichever is lower.</p>
    </div>
  </div>

  <div class="bg-white rounded-2xl border border-gray-100 p-6 space-y-4">
    <h2 class="font-semibold text-gray-800">Mass Intention Workflow</h2>
    <div>
      <label class="text-xs font-medium text-gray-500">Online Submission Cutoff Before Mass (minutes)</label>
      <input type="number" min="0" max="1440" step="5" name="mass_intention_cutoff_minutes" value="<?= html_escape($settings['mass_intention_cutoff_minutes'] ?? '30') ?>" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm">
      <p class="text-[11px] text-gray-400 mt-1">Example: 30 means the selected Mass disappears from online intention choices 30 minutes before it begins, giving the secretary and commentator time to finalize the reader sheet.</p>
    </div>
  </div>

  <div class="bg-white rounded-2xl border border-gray-100 p-6 space-y-4">
    <h2 class="font-semibold text-gray-800">GCash Configuration</h2>
    <div>
      <label class="text-xs font-medium text-gray-500">Account Name</label>
      <input name="gcash_account_name" value="<?= html_escape($settings['gcash_account_name'] ?? '') ?>" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm">
    </div>
    <div>
      <label class="text-xs font-medium text-gray-500">Account Number</label>
      <input name="gcash_account_number" value="<?= html_escape($settings['gcash_account_number'] ?? '') ?>" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm">
    </div>
    <div>
      <label class="text-xs font-medium text-gray-500">QR Code Image</label>
      <?php if (!empty($settings['gcash_qr_image'])): ?>
        <img src="<?= base_url($settings['gcash_qr_image']) ?>" class="w-32 h-32 rounded-lg border border-gray-200 mt-2 mb-2">
      <?php endif; ?>
      <input type="file" name="gcash_qr_image" accept="image/*" class="mt-1 w-full text-sm">
    </div>
  </div>

  <button type="submit" class="px-6 py-3 rounded-lg bg-parish-700 hover:bg-parish-800 text-white text-sm font-medium">Save Settings</button>
</form>

<script>
$('#settings-form').on('submit', function(e){
  e.preventDefault();
  var formData = new FormData(this);
  $.ajax({
    url: '<?= site_url('admin/setting/store') ?>', type: 'POST', data: formData, processData: false, contentType: false,
  }).done(function(res){
    if(res.success){ toastr.success(res.message); } else { toastr.error(res.message); }
  });
});
</script>
