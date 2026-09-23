<div class="max-w-3xl mx-auto">
  <a href="<?= site_url('my/bookings') ?>" class="text-sm text-parish-700 hover:underline">← Back to My Bookings</a>

  <div class="bg-white rounded-2xl border border-gray-100 p-6 sm:p-8 mt-4">
    <div class="flex items-center gap-3 mb-6">
      <div class="w-11 h-11 rounded-xl bg-parish-50 text-parish-700 flex items-center justify-center text-xl"><i class="ph ph-file-text"></i></div>
      <div>
        <h1 class="text-xl font-semibold text-gray-900"><?= html_escape($service['name']) ?> Application</h1>
        <p class="text-xs text-gray-400">Fee: <?= peso($service['base_fee']) ?><?= $service['requires_approval_workflow'] ? ' · Requires review process' : '' ?></p>
      </div>
    </div>

    <form id="booking-form" enctype="multipart/form-data" class="space-y-5">
      <input type="hidden" name="service_type_id" value="<?= $service['id'] ?>">

      <div class="grid sm:grid-cols-2 gap-4">
        <div>
          <label class="text-xs font-medium text-gray-500">Preferred Date</label>
          <input required type="date" name="preferred_date" min="<?= date('Y-m-d') ?>" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:ring-2 focus:ring-parish-300 outline-none">
        </div>
        <div>
          <label class="text-xs font-medium text-gray-500">Alternative Date (optional)</label>
          <input type="date" name="alternative_date" min="<?= date('Y-m-d') ?>" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:ring-2 focus:ring-parish-300 outline-none">
        </div>
      </div>

      <?php if ($service['service_key'] === 'baptism'): ?>
        <div class="border-t border-gray-100 pt-5">
          <h3 class="text-sm font-semibold text-gray-700 mb-3">Child Information</h3>
          <div class="grid sm:grid-cols-2 gap-4">
            <input required name="child_name" placeholder="Child's Full Name" class="px-4 py-2.5 rounded-lg border border-gray-200 text-sm">
            <input required type="date" name="child_birth_date" class="px-4 py-2.5 rounded-lg border border-gray-200 text-sm">
          </div>
        </div>
        <div>
          <h3 class="text-sm font-semibold text-gray-700 mb-3">Parents' Information</h3>
          <div class="grid sm:grid-cols-2 gap-4">
            <input required name="father_name" placeholder="Father's Full Name" class="px-4 py-2.5 rounded-lg border border-gray-200 text-sm">
            <input required name="mother_name" placeholder="Mother's Full Name" class="px-4 py-2.5 rounded-lg border border-gray-200 text-sm">
          </div>
        </div>
        <div>
          <h3 class="text-sm font-semibold text-gray-700 mb-3">Godparents / Sponsors</h3>
          <textarea name="sponsors" rows="2" placeholder="List of godparents (one per line)" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm"></textarea>
        </div>

      <?php elseif ($service['service_key'] === 'wedding'): ?>
        <div class="border-t border-gray-100 pt-5">
          <h3 class="text-sm font-semibold text-gray-700 mb-3">Bride & Groom Information</h3>
          <div class="grid sm:grid-cols-2 gap-4">
            <input required name="bride_name" placeholder="Bride's Full Name" class="px-4 py-2.5 rounded-lg border border-gray-200 text-sm">
            <input required name="groom_name" placeholder="Groom's Full Name" class="px-4 py-2.5 rounded-lg border border-gray-200 text-sm">
          </div>
        </div>
        <div>
          <h3 class="text-sm font-semibold text-gray-700 mb-3">Additional Details</h3>
          <textarea name="sponsor_information" rows="2" placeholder="Principal sponsors, notes" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm"></textarea>
        </div>
        <div class="bg-amber-50 border border-amber-100 text-amber-800 text-xs rounded-lg p-3">
          <i class="ph ph-info mr-1"></i> Wedding applications go through requirements review, canonical interview, and priest review before a date is confirmed.
        </div>

      <?php elseif ($service['service_key'] === 'funeral'): ?>
        <div class="border-t border-gray-100 pt-5">
          <h3 class="text-sm font-semibold text-gray-700 mb-3">Deceased Information</h3>
          <div class="grid sm:grid-cols-2 gap-4">
            <input required name="deceased_name" placeholder="Name of Deceased" class="px-4 py-2.5 rounded-lg border border-gray-200 text-sm">
            <input required type="date" name="date_of_death" class="px-4 py-2.5 rounded-lg border border-gray-200 text-sm">
            <input name="funeral_home" placeholder="Funeral Home" class="px-4 py-2.5 rounded-lg border border-gray-200 text-sm">
            <input name="cemetery" placeholder="Cemetery" class="px-4 py-2.5 rounded-lg border border-gray-200 text-sm">
          </div>
        </div>

      <?php elseif ($service['service_key'] === 'confirmation'): ?>
        <div class="border-t border-gray-100 pt-5">
          <input required name="confirmand_name" placeholder="Confirmand's Full Name" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm">
        </div>

      <?php elseif (in_array($service['service_key'], ['house_blessing', 'vehicle_blessing'])): ?>
        <div class="border-t border-gray-100 pt-5">
          <textarea required name="blessing_details" rows="2" placeholder="Address / Vehicle details" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm"></textarea>
        </div>

      <?php else: ?>
        <div class="border-t border-gray-100 pt-5">
          <textarea name="notes" rows="3" placeholder="Additional notes for the parish office" class="w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm"></textarea>
        </div>
      <?php endif; ?>

      <?php if (!empty($requirements)): ?>
      <div class="border-t border-gray-100 pt-5">
        <h3 class="text-sm font-semibold text-gray-700 mb-3">Requirements</h3>
        <div class="space-y-3">
          <?php foreach ($requirements as $req): ?>
          <div class="flex items-center gap-3 p-3 rounded-lg border border-gray-100">
            <i class="ph ph-paperclip text-gray-400 text-lg flex-shrink-0"></i>
            <div class="flex-1">
              <div class="text-sm text-gray-700"><?= html_escape($req['label']) ?><?= $req['is_required'] ? ' <span class="text-red-500">*</span>' : ' <span class="text-gray-300 text-xs">(optional)</span>' ?></div>
            </div>
            <input type="file" name="documents[]" data-req="<?= $req['id'] ?>" class="text-xs" <?= $req['is_required'] ? '' : '' ?>>
            <input type="hidden" name="requirement_id[]" value="<?= $req['id'] ?>">
          </div>
          <?php endforeach; ?>
        </div>
        <p class="text-xs text-gray-400 mt-2">Accepted formats: JPG, PNG, PDF. You can also submit physical copies at the parish office.</p>
      </div>
      <?php endif; ?>

      <button type="submit" class="w-full py-3 rounded-lg bg-parish-700 hover:bg-parish-800 text-white text-sm font-medium">Submit Application</button>
    </form>
  </div>
</div>

<script>
$('#booking-form').on('submit', function(e){
  e.preventDefault();
  var formData = new FormData(this);
  $.ajax({
    url: '<?= site_url('my/bookings/store') ?>',
    type: 'POST', data: formData, processData: false, contentType: false,
  }).done(function(res){
    if(res.success){
      Swal.fire({ icon: 'success', title: 'Application Submitted', text: 'The parish office will review your application shortly.', confirmButtonColor: '#235a38' })
        .then(function(){ window.location.href = res.redirect; });
    } else {
      Swal.fire({ icon: 'error', title: 'Please check your input', text: res.message, confirmButtonColor: '#235a38' });
    }
  });
});
</script>
