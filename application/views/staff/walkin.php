<div class="max-w-xl mx-auto">
  <h1 class="text-2xl font-semibold text-gray-900 mb-1">Record Walk-in Transaction</h1>
  <p class="text-gray-500 text-sm mb-6">For parishioners who visited the parish office in person.</p>

  <div class="bg-white rounded-2xl border border-gray-100 p-6 sm:p-8">
    <form id="walkin-form" class="space-y-4">
      <div class="grid sm:grid-cols-2 gap-4">
        <div>
          <label class="text-xs font-medium text-gray-500">Full Name</label>
          <input required name="full_name" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm">
        </div>
        <div>
          <label class="text-xs font-medium text-gray-500">Email Address</label>
          <input required type="email" name="email" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm">
        </div>
      </div>
      <div>
        <label class="text-xs font-medium text-gray-500">Mobile Number</label>
        <input name="mobile_number" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm">
      </div>
      <div>
        <label class="text-xs font-medium text-gray-500">Service</label>
        <select required name="service_type_id" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm">
          <option value="">Select service…</option>
          <?php foreach ($service_types as $s): ?>
            <option value="<?= $s['id'] ?>"><?= html_escape($s['name']) ?> — <?= peso($s['base_fee']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="text-xs font-medium text-gray-500">Preferred Date</label>
        <input required type="date" name="preferred_date" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm">
      </div>
      <p class="text-xs text-gray-400">If this email already has an account, the transaction will be linked to it. Otherwise, a new parishioner account will be created automatically.</p>
      <button type="submit" class="w-full py-3 rounded-lg bg-parish-700 hover:bg-parish-800 text-white text-sm font-medium">Record Transaction</button>
    </form>
  </div>
</div>

<script>
$('#walkin-form').on('submit', function(e){
  e.preventDefault();
  $.post('<?= site_url('staff/walkin/store') ?>', $(this).serialize(), function(res){
    if(res.success){
      Swal.fire({icon:'success', title:'Recorded', text: res.message, confirmButtonColor:'#235a38'}).then(function(){ window.location.href = res.redirect; });
    } else { toastr.error(res.message); }
  });
});
</script>
