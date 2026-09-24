<?php if (!$schema_ready): ?>
<div class="mb-6 rounded-2xl border border-amber-200 bg-amber-50 p-4 sm:p-5">
  <div class="flex gap-3">
    <i class="ph ph-warning-circle text-xl text-amber-700 mt-0.5"></i>
    <div>
      <div class="font-semibold text-amber-900 text-sm">Projects database upgrade required</div>
      <p class="text-xs text-amber-800/80 mt-1">Run <code class="font-mono">database/migrations/20260924_projects_donations.sql</code> in phpMyAdmin, then reload this page.</p>
    </div>
  </div>
</div>
<?php endif; ?>

<div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-6">
  <div>
    <div class="text-xs uppercase tracking-[.16em] text-gold-600 font-semibold">Stewardship</div>
    <h1 class="text-2xl sm:text-3xl font-semibold text-gray-900 mt-1">Parish Projects</h1>
    <p class="text-gray-500 text-sm mt-1">Publish parish improvement, outreach and ministry projects that people can support through donations.</p>
  </div>
  <button type="button" onclick="resetProjectForm(); openProjectModal()" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-parish-700 hover:bg-parish-800 text-white text-sm font-semibold">
    <i class="ph ph-plus"></i> New Project
  </button>
</div>

<section class="bg-white rounded-2xl border border-gray-100 p-5 sm:p-6">
  <div class="overflow-x-auto">
    <table id="project-table" class="w-full text-sm">
      <thead>
        <tr>
          <th>Project</th>
          <th>Category</th>
          <th>Funding Progress</th>
          <th>Target</th>
          <th>Status</th>
          <th class="text-center">Actions</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>
</section>

<div id="project-modal" class="hidden fixed inset-0 z-50 items-center justify-center p-3 sm:p-5">
  <div class="absolute inset-0 bg-black/55 backdrop-blur-[1px]" onclick="closeProjectModal()"></div>
  <div class="relative bg-white rounded-[1.5rem] shadow-2xl w-full max-w-4xl max-h-[94vh] flex flex-col overflow-hidden">
    <div class="flex-shrink-0 px-5 sm:px-6 py-4 border-b border-gray-100 flex items-center justify-between">
      <div>
        <div class="text-[10px] uppercase tracking-[.17em] text-gold-600 font-bold">Project editor</div>
        <h2 id="project-modal-title" class="font-semibold text-gray-900 text-lg mt-0.5">New Project</h2>
      </div>
      <button type="button" onclick="closeProjectModal()" class="w-9 h-9 rounded-xl hover:bg-gray-100 text-gray-500 flex items-center justify-center"><i class="ph ph-x text-xl"></i></button>
    </div>

    <form id="project-form" enctype="multipart/form-data" class="flex-1 min-h-0 flex flex-col">
      <div class="flex-1 overflow-y-auto p-5 sm:p-6 space-y-6">
        <input type="hidden" name="id" id="p-id">

        <section>
          <h3 class="text-sm font-semibold text-gray-800 mb-4">Project Information</h3>
          <div class="space-y-4">
            <div>
              <label class="text-xs font-medium text-gray-500">Project Title</label>
              <input required name="title" id="p-title" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200 text-sm">
            </div>

            <div>
              <label class="text-xs font-medium text-gray-500">Short Description</label>
              <input maxlength="300" name="short_description" id="p-short" placeholder="A concise summary used on public project cards." class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200 text-sm">
            </div>

            <div>
              <label class="text-xs font-medium text-gray-500">Full Project Story / Details</label>
              <textarea name="description" id="p-description" rows="7" placeholder="Explain why the project matters, what will be improved, who benefits, and how parishioners can help." class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200 text-sm leading-relaxed"></textarea>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
              <div>
                <label class="text-xs font-medium text-gray-500">Category</label>
                <input name="category" id="p-category" placeholder="e.g. Church Restoration, Outreach" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200 text-sm">
              </div>
              <div>
                <label class="text-xs font-medium text-gray-500">Location</label>
                <input name="location" id="p-location" placeholder="Sta. Monica Parish Church" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200 text-sm">
              </div>
            </div>
          </div>
        </section>

        <section class="rounded-2xl border border-gray-100 bg-gray-50/50 p-4 sm:p-5">
          <div class="grid lg:grid-cols-[.85fr_1.15fr] gap-5 items-start">
            <div>
              <label class="text-xs font-semibold text-gray-600">Project Photo</label>
              <div class="mt-2 aspect-[16/10] rounded-2xl overflow-hidden border border-dashed border-gray-300 bg-white flex items-center justify-center text-center">
                <div id="project-cover-placeholder" class="px-5 text-gray-400"><i class="ph ph-image text-3xl"></i><div class="text-xs mt-2">Project or parish photo</div></div>
                <img id="project-cover-preview" class="hidden w-full h-full object-cover" alt="Project preview">
              </div>
            </div>
            <div>
              <label class="text-xs font-semibold text-gray-600">Upload / Replace Photo</label>
              <input type="file" name="cover_image" id="p-cover" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" class="mt-2 block w-full text-sm text-gray-500 file:mr-3 file:px-4 file:py-2.5 file:rounded-xl file:border-0 file:bg-parish-50 file:text-parish-700 file:font-semibold">
              <p class="text-[11px] text-gray-400 mt-2">JPG, PNG or WebP up to 5 MB. Use a real parish/project photo when possible.</p>
            </div>
          </div>
        </section>

        <section>
          <h3 class="text-sm font-semibold text-gray-800 mb-4">Funding & Timeline</h3>
          <div class="grid sm:grid-cols-2 gap-4">
            <div>
              <label class="text-xs font-medium text-gray-500">Fundraising Goal (₱)</label>
              <input type="number" min="0" step="0.01" name="goal_amount" id="p-goal" placeholder="0.00" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200 text-sm">
              <p class="text-[10px] text-gray-400 mt-1">Leave blank for projects without a fixed fundraising target.</p>
            </div>
            <div>
              <label class="text-xs font-medium text-gray-500">Display Order</label>
              <input type="number" min="0" name="display_order" id="p-order" value="0" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200 text-sm">
            </div>
            <div>
              <label class="text-xs font-medium text-gray-500">Start Date</label>
              <input type="date" name="start_date" id="p-start" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200 text-sm">
            </div>
            <div>
              <label class="text-xs font-medium text-gray-500">Target / Completion Date</label>
              <input type="date" name="target_date" id="p-target" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200 text-sm">
            </div>
          </div>
        </section>

        <section class="grid md:grid-cols-2 gap-5">
          <label class="rounded-2xl border border-gold-100 bg-gold-50/50 p-4 flex items-start gap-3">
            <input type="checkbox" name="is_featured" id="p-featured" value="1" class="mt-1 rounded border-gray-300 text-parish-700">
            <span><strong class="block text-sm text-gray-800">Featured Project</strong><span class="text-xs text-gray-500">Prioritize this project on the public Projects and Donation pages.</span></span>
          </label>

          <div class="rounded-2xl border border-gray-100 p-4">
            <label class="text-xs font-medium text-gray-500">Project Status</label>
            <select name="status" id="p-status" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200 bg-white text-sm">
              <option value="active">Active</option>
              <option value="closed">Closed / Completed</option>
            </select>
          </div>
        </section>
      </div>

      <div class="flex-shrink-0 px-5 sm:px-6 py-4 border-t border-gray-100 bg-white flex flex-col-reverse sm:flex-row sm:justify-end gap-3">
        <button type="button" onclick="closeProjectModal()" class="px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-medium text-gray-600">Cancel</button>
        <button type="submit" id="project-save-btn" class="px-5 py-2.5 rounded-xl bg-parish-700 hover:bg-parish-800 text-white text-sm font-semibold"><i class="ph ph-floppy-disk mr-1"></i> Save Project</button>
      </div>
    </form>
  </div>
</div>

<script>
var projectTable;

function openProjectModal(){
  $('#project-modal').removeClass('hidden').addClass('flex');
  $('body').addClass('overflow-hidden');
}
function closeProjectModal(){
  $('#project-modal').addClass('hidden').removeClass('flex');
  $('body').removeClass('overflow-hidden');
}
function resetProjectForm(){
  $('#project-form')[0].reset();
  $('#p-id').val('');
  $('#p-status').val('active');
  $('#p-order').val(0);
  $('#project-modal-title').text('New Project');
  setProjectCover('');
}
function setProjectCover(url){
  if(url){
    $('#project-cover-preview').attr('src',url).removeClass('hidden');
    $('#project-cover-placeholder').addClass('hidden');
  }else{
    $('#project-cover-preview').attr('src','').addClass('hidden');
    $('#project-cover-placeholder').removeClass('hidden');
  }
}
function editProject(id){
  $.get('<?= site_url('admin/project/get/') ?>'+id,function(res){
    if(!res.success){ toastr.error(res.message || 'Project not found.'); return; }
    var d=res.data;
    resetProjectForm();
    $('#p-id').val(d.id);
    $('#p-title').val(d.title);
    $('#p-short').val(d.short_description || '');
    $('#p-description').val(d.description || '');
    $('#p-category').val(d.category || '');
    $('#p-location').val(d.location || '');
    $('#p-goal').val(d.goal_amount || '');
    $('#p-order').val(d.display_order || 0);
    $('#p-start').val(d.start_date || '');
    $('#p-target').val(d.target_date || '');
    $('#p-featured').prop('checked',parseInt(d.is_featured || 0,10)===1);
    $('#p-status').val(d.status || 'active');
    $('#project-modal-title').text('Edit Project');
    setProjectCover(d.cover_url || '');
    openProjectModal();
  });
}
function deleteProject(id){
  Swal.fire({
    icon:'warning',
    title:'Delete this project?',
    text:'Projects with donation history cannot be deleted and should be closed instead.',
    showCancelButton:true,
    confirmButtonText:'Delete',
    confirmButtonColor:'#dc2626'
  }).then(function(r){
    if(!r.isConfirmed) return;
    $.post('<?= site_url('admin/project/delete/') ?>'+id,function(res){
      if(res.success){ toastr.success(res.message); projectTable.ajax.reload(null,false); }
      else Swal.fire({icon:'error',title:'Could not delete',text:res.message || 'Please try again.',confirmButtonColor:'#235a38'});
    });
  });
}

$('#p-cover').on('change',function(){
  var file=this.files && this.files[0];
  if(!file) return;
  var reader=new FileReader();
  reader.onload=function(e){ setProjectCover(e.target.result); };
  reader.readAsDataURL(file);
});

$('#project-form').on('submit',function(e){
  e.preventDefault();
  var btn=$('#project-save-btn');
  var original=btn.html();
  btn.prop('disabled',true).addClass('opacity-60').html('<i class="ph ph-spinner-gap animate-spin mr-1"></i> Saving…');

  $.ajax({
    url:'<?= site_url('admin/project/store') ?>',
    type:'POST',
    data:new FormData(this),
    processData:false,
    contentType:false,
    success:function(res){
      if(res.success){
        toastr.success(res.message);
        closeProjectModal();
        projectTable.ajax.reload(null,false);
      } else {
        Swal.fire({icon:'error',title:'Could not save project',text:res.message || 'Please review the form.',confirmButtonColor:'#235a38'});
      }
    },
    error:function(){
      Swal.fire({icon:'error',title:'Could not save project',text:'The server did not accept the update.',confirmButtonColor:'#235a38'});
    },
    complete:function(){ btn.prop('disabled',false).removeClass('opacity-60').html(original); }
  });
});

$(function(){
  projectTable=$('#project-table').DataTable({
    processing:true,
    serverSide:true,
    ajax:{url:'<?= site_url('admin/project/datatable') ?>',type:'POST'},
    columns:[
      {data:'title'},
      {data:'category'},
      {data:'goal',orderable:false},
      {data:'target'},
      {data:'status',orderable:false},
      {data:'actions',orderable:false,searchable:false,className:'whitespace-nowrap'}
    ],
    language:{search:'',searchPlaceholder:'Search projects…'}
  });
});
</script>
