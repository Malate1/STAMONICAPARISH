<h1 class="text-2xl font-semibold text-gray-900 mb-1">GCash Payments</h1>
<p class="text-gray-500 text-sm mb-6">Verify submitted GCash payments and issue official receipts.</p>

<div class="bg-white rounded-2xl border border-gray-100 p-6">
  <div class="overflow-x-auto">
    <table id="pay-table" class="w-full text-sm">
      <thead><tr><th>Code</th><th>Payer</th><th>For</th><th>Amount</th><th>Reference</th><th>Status</th><th></th></tr></thead>
      <tbody></tbody>
    </table>
  </div>
</div>

<div id="pay-modal" class="hidden fixed inset-0 z-50 items-center justify-center p-4">
  <div class="absolute inset-0 bg-black/50" onclick="closePayModal()"></div>
  <div class="relative bg-white rounded-2xl shadow-xl w-full max-w-lg p-6 max-h-[90vh] overflow-y-auto">
    <h2 class="font-semibold text-gray-800 mb-4">Payment Details</h2>
    <div id="pay-details" class="space-y-3 text-sm"></div>
    <div class="mt-5 flex gap-3" id="pay-actions"></div>
  </div>
</div>

<script>
var table;
var base = '<?= strpos(uri_string(), 'staff') === 0 ? 'staff' : 'admin' ?>';
function closePayModal(){ $('#pay-modal').addClass('hidden').removeClass('flex'); }
function viewPayment(id){
  $.get('<?= site_url('/') ?>' + base + '/payment/get/' + id, function(res){
    var p = res.data;
    var html = '' +
      '<div class="flex justify-between"><span class="text-gray-400">Payer</span><span class="font-medium">'+ p.first_name +' '+ p.last_name +'</span></div>' +
      '<div class="flex justify-between"><span class="text-gray-400">Amount</span><span class="font-medium">₱'+ Number(p.amount).toLocaleString(undefined,{minimumFractionDigits:2}) +'</span></div>' +
      '<div class="flex justify-between"><span class="text-gray-400">Reference No.</span><span class="font-medium">'+ (p.gcash_reference_no || '—') +'</span></div>' +
      '<div class="flex justify-between"><span class="text-gray-400">Status</span><span class="font-medium">'+ p.status +'</span></div>';
    if (p.proof_of_payment) {
      html += '<a href="<?= base_url() ?>' + p.proof_of_payment + '" target="_blank" class="block mt-2"><img src="<?= base_url() ?>' + p.proof_of_payment + '" class="rounded-lg border border-gray-200 max-h-64 mx-auto" onerror="this.replaceWith(\'View proof of payment (PDF) →\')"></a>';
    }
    $('#pay-details').html(html);

    var actions = '';
    if (p.status === 'submitted') {
      actions = '<button onclick="verifyPayment('+p.id+')" class="flex-1 px-4 py-2.5 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium">Verify Payment</button>' +
                 '<button onclick="rejectPayment('+p.id+')" class="flex-1 px-4 py-2.5 rounded-lg bg-red-50 hover:bg-red-100 text-red-600 text-sm font-medium">Reject</button>';
    } else {
      actions = '<div class="text-xs text-gray-400 text-center w-full">No further action needed.</div>';
    }
    $('#pay-actions').html(actions);
    $('#pay-modal').removeClass('hidden').addClass('flex');
  });
}
function verifyPayment(id){
  Swal.fire({ icon:'question', title:'Verify this payment?', text:'An official receipt number will be generated.', showCancelButton:true, confirmButtonText:'Verify', confirmButtonColor:'#059669' })
    .then(function(r){ if(r.isConfirmed){
      $.post('<?= site_url('/') ?>' + base + '/payment/verify', { id: id }, function(res){
        toastr.success(res.message); closePayModal(); table.ajax.reload();
      });
    }});
}
function rejectPayment(id){
  Swal.fire({ title:'Reason for rejection', input:'text', showCancelButton:true, confirmButtonText:'Reject', confirmButtonColor:'#dc2626' })
    .then(function(r){ if(r.isConfirmed){
      $.post('<?= site_url('/') ?>' + base + '/payment/reject', { id: id, reason: r.value || 'Not specified' }, function(res){
        toastr.success(res.message); closePayModal(); table.ajax.reload();
      });
    }});
}
$(function(){
  document.getElementById('pay-modal').querySelector('.relative').addEventListener('click', function(e){ e.stopPropagation(); });
  table = $('#pay-table').DataTable({
    processing: true, serverSide: true,
    ajax: { url: '<?= site_url('/') ?>' + base + '/payment/datatable', type: 'POST' },
    columns: [
      { data: 'payment_code' }, { data: 'payer' }, { data: 'for' }, { data: 'amount' },
      { data: 'reference' }, { data: 'status', orderable: false }, { data: 'actions', orderable: false, searchable: false }
    ],
    language: { search: '', searchPlaceholder: 'Search payments…' }
  });
});
</script>
