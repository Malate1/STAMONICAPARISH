<div class="max-w-2xl mx-auto">
  <a href="<?= site_url('my/certificates') ?>" class="text-sm text-parish-700 hover:underline">← Back to Certificates</a>

  <div class="bg-white rounded-2xl border border-gray-100 p-6 sm:p-8 mt-4">
    <h1 class="text-xl font-semibold text-gray-900 mb-1">Request a Certificate</h1>
    <p class="text-sm text-gray-400 mb-6">The parish office will search your record and notify you once found.</p>

    <form id="cert-form" class="space-y-4">
      <div>
        <label class="text-xs font-medium text-gray-500">Certificate Type</label>
        <select required name="certificate_type" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm">
          <option value="">Select type…</option>
          <option value="baptismal">Baptismal Certificate</option>
          <option value="confirmation">Confirmation Certificate</option>
          <option value="marriage">Marriage Certificate / Parish Record</option>
          <option value="no_record">Certificate of No Record</option>
          <option value="other">Other</option>
        </select>
      </div>
      <div>
        <label class="text-xs font-medium text-gray-500">Purpose</label>
        <input required name="purpose" placeholder="e.g. School requirement, employment, marriage requirement" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm">
      </div>
      <div class="grid sm:grid-cols-2 gap-4">
        <div>
          <label class="text-xs font-medium text-gray-500">Number of Copies</label>
          <input required type="number" min="1" value="1" name="number_of_copies" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm">
        </div>
        <div>
          <label class="text-xs font-medium text-gray-500">Release Method</label>
          <select name="release_method" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm">
            <option value="pickup">Parish Office Pickup</option>
            <option value="representative">Authorized Representative</option>
            <option value="digital">Secure Digital Copy</option>
          </select>
        </div>
      </div>
      <button type="submit" class="w-full py-3 rounded-lg bg-parish-700 hover:bg-parish-800 text-white text-sm font-medium">Submit Request</button>
    </form>
  </div>
</div>

<script>
$('#cert-form').on('submit', function(e){
  e.preventDefault();
  $.post('<?= site_url('my/certificates/store') ?>', $(this).serialize(), function(res){
    if(res.success){
      Swal.fire({ icon: 'success', title: 'Request Submitted', text: res.message, confirmButtonColor: '#235a38' })
        .then(function(){ window.location.href = res.redirect; });
    } else {
      Swal.fire({ icon: 'error', title: 'Please check your input', text: res.message, confirmButtonColor: '#235a38' });
    }
  });
});
</script>
