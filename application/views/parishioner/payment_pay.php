<div class="max-w-xl mx-auto">
  <a href="<?= $type === 'service_booking' ? site_url('my/bookings/' . $id) : site_url('my/certificates/' . $id) ?>" class="text-sm text-parish-700 hover:underline">← Back</a>

  <div class="bg-white rounded-2xl border border-gray-100 p-6 sm:p-8 mt-4">
    <h1 class="text-xl font-semibold text-gray-900 mb-1">Pay via GCash</h1>
    <p class="text-sm text-gray-400 mb-6"><?= html_escape($title) ?></p>

    <div class="flex items-center justify-between p-4 rounded-xl bg-parish-50/60 border border-parish-100 mb-6">
      <span class="text-sm text-gray-600">Amount Due</span>
      <span class="text-xl font-semibold text-parish-700"><?= peso($amount) ?></span>
    </div>

    <div class="text-center mb-6">
      <?php if (!empty($gcash['gcash_qr_image'])): ?>
        <img src="<?= base_url($gcash['gcash_qr_image']) ?>" alt="GCash QR" class="w-48 h-48 mx-auto rounded-xl border border-gray-200 object-cover">
      <?php else: ?>
        <div class="w-48 h-48 mx-auto rounded-xl border border-gray-200 bg-gray-50 flex items-center justify-center text-gray-300"><i class="ph ph-qr-code" style="font-size:4rem"></i></div>
      <?php endif; ?>
      <div class="text-sm text-gray-600 mt-3">
        <?= html_escape($gcash['gcash_account_name'] ?? 'Sta. Monica Parish Church') ?><br>
        <span class="text-gray-400"><?= html_escape($gcash['gcash_account_number'] ?? '') ?></span>
      </div>
    </div>

    <ol class="text-xs text-gray-500 space-y-1.5 mb-6 list-decimal list-inside">
      <li>Scan the QR code above using your GCash app.</li>
      <li>Pay the exact amount shown.</li>
      <li>Enter your GCash reference number and upload a screenshot of the payment below.</li>
    </ol>

    <form id="payment-form" enctype="multipart/form-data" class="space-y-4">
      <input type="hidden" name="payable_type" value="<?= $type ?>">
      <input type="hidden" name="payable_id" value="<?= $id ?>">
      <div>
        <label class="text-xs font-medium text-gray-500">GCash Reference Number</label>
        <input required name="gcash_reference_no" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm" placeholder="e.g. 1234567890123">
      </div>
      <div>
        <label class="text-xs font-medium text-gray-500">Proof of Payment (screenshot)</label>
        <input required type="file" name="proof" accept=".jpg,.jpeg,.png,.pdf" class="mt-1 w-full text-sm">
      </div>
      <button type="submit" class="w-full py-3 rounded-lg bg-parish-700 hover:bg-parish-800 text-white text-sm font-medium">Submit Payment</button>
    </form>
  </div>
</div>

<script>
$('#payment-form').on('submit', function(e){
  e.preventDefault();
  var formData = new FormData(this);
  $.ajax({
    url: '<?= site_url('my/payments/submit') ?>', type: 'POST', data: formData, processData: false, contentType: false,
  }).done(function(res){
    if(res.success){
      Swal.fire({ icon: 'success', title: 'Payment Submitted', text: res.message, confirmButtonColor: '#235a38' })
        .then(function(){ window.location.href = res.redirect; });
    } else {
      Swal.fire({ icon: 'error', title: 'Please check your input', text: res.message, confirmButtonColor: '#235a38' });
    }
  });
});
</script>
