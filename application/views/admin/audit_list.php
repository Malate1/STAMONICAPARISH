<div class="max-w-7xl mx-auto">
  <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-6">
    <div>
      <div class="text-xs uppercase tracking-[.16em] text-gold-600 font-semibold">Administration</div>
      <h1 class="text-2xl sm:text-3xl font-semibold text-gray-900 mt-1">Audit Trail</h1>
      <p class="text-sm text-gray-500 mt-1 max-w-3xl">Review security-sensitive and operational actions performed inside Parish Connect. Audit entries are read-only from this screen.</p>
    </div>
  </div>

  <div class="rounded-2xl border border-blue-100 bg-blue-50/60 p-4 mb-5">
    <div class="flex gap-3">
      <i class="ph ph-shield-check text-blue-700 text-xl mt-0.5"></i>
      <div class="text-xs text-blue-900/80 leading-relaxed"><strong class="text-blue-900">Use this trail for accountability.</strong> It is especially useful when reviewing payment verification, registry changes, certificate processing, account changes and booking workflow decisions.</div>
    </div>
  </div>

  <div class="bg-white rounded-2xl border border-gray-100 p-5 mb-5">
    <div class="grid sm:grid-cols-2 xl:grid-cols-4 gap-4">
      <div>
        <label class="text-xs font-semibold text-gray-500">Module</label>
        <select id="audit-module" class="mt-1.5 w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm">
          <option value="">All modules</option>
          <?php foreach ($modules as $module): ?>
            <option value="<?= html_escape($module) ?>"><?= html_escape(ucwords(str_replace(['_','-'],' ',$module))) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="text-xs font-semibold text-gray-500">Staff Account</label>
        <select id="audit-user" class="mt-1.5 w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm">
          <option value="">All staff</option>
          <?php foreach ($users as $user): ?>
            <option value="<?= (int)$user['id'] ?>"><?= html_escape(trim($user['first_name'].' '.$user['last_name'])) ?> · <?= html_escape($user['email']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div>
        <label class="text-xs font-semibold text-gray-500">From Date</label>
        <input id="audit-from" type="date" class="mt-1.5 w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm">
      </div>
      <div>
        <label class="text-xs font-semibold text-gray-500">To Date</label>
        <input id="audit-to" type="date" class="mt-1.5 w-full px-3 py-2.5 rounded-xl border border-gray-200 text-sm">
      </div>
    </div>
    <div class="mt-4 flex justify-end">
      <button type="button" id="audit-reset" class="px-4 py-2.5 rounded-xl border border-gray-200 text-gray-600 text-xs font-semibold hover:bg-gray-50"><i class="ph ph-arrow-counter-clockwise mr-1"></i>Reset Filters</button>
    </div>
  </div>

  <div class="bg-white rounded-2xl border border-gray-100 p-5 sm:p-6">
    <div class="overflow-x-auto">
      <table id="audit-table" class="w-full text-sm">
        <thead>
          <tr>
            <th>When</th>
            <th>User</th>
            <th>Module</th>
            <th>Action / Description</th>
            <th>IP Address</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
  </div>
</div>

<script>
$(function(){
  var table = $('#audit-table').DataTable({
    processing:true,
    serverSide:true,
    order:[],
    ajax:{
      url:'<?= site_url('admin/audit/datatable') ?>',
      type:'POST',
      data:function(d){
        d.module=$('#audit-module').val();
        d.user_id=$('#audit-user').val();
        d.date_from=$('#audit-from').val();
        d.date_to=$('#audit-to').val();
      }
    },
    columns:[
      {data:'when',orderable:false},
      {data:'user',orderable:false},
      {data:'module',orderable:false},
      {data:'action',orderable:false},
      {data:'ip',orderable:false}
    ],
    pageLength:25,
    language:{search:'',searchPlaceholder:'Search actions, users, IP…'}
  });

  $('#audit-module,#audit-user,#audit-from,#audit-to').on('change',function(){ table.ajax.reload(); });
  $('#audit-reset').on('click',function(){
    $('#audit-module,#audit-user').val('').trigger('change.select2');
    $('#audit-from,#audit-to').val('');
    table.ajax.reload();
  });
});
</script>
