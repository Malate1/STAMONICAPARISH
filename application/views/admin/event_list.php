<?php
$season_year_min = max(2020, (int)$default_season_year - 1);
$season_year_max = (int)$default_season_year + 4;
?>

<div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-6">
  <div>
    <div class="text-xs uppercase tracking-[.16em] text-gold-600 font-semibold">Public Content</div>
    <h1 class="text-2xl sm:text-3xl font-semibold text-gray-900 mt-1">Parish Events</h1>
    <p class="text-gray-500 text-sm mt-1">Manage regular activities and prepare yearly liturgical/seasonal highlights for the public website.</p>
  </div>
  <button onclick="resetForm(); openModal()" class="px-4 py-2.5 rounded-xl bg-parish-700 hover:bg-parish-800 text-white text-sm font-semibold flex items-center justify-center gap-2">
    <i class="ph ph-plus"></i> New Event
  </button>
</div>

<section class="bg-white rounded-2xl border border-gray-100 overflow-hidden mb-6">
  <div class="p-5 sm:p-6 border-b border-gray-100 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
    <div>
      <div class="flex items-center gap-2">
        <div class="w-9 h-9 rounded-xl bg-gold-50 text-gold-700 flex items-center justify-center"><i class="ph ph-calendar-star text-lg"></i></div>
        <div>
          <h2 class="font-semibold text-gray-900">Prebuilt Seasonal Events</h2>
          <p class="text-xs text-gray-400 mt-0.5">Prepare the year once, then replace the photo and edit exact parish schedules/details.</p>
        </div>
      </div>
    </div>

    <div class="flex items-center gap-2">
      <label for="season-year" class="text-xs font-semibold text-gray-500">Year</label>
      <select id="season-year" class="px-3 py-2 rounded-xl border border-gray-200 bg-white text-sm font-medium text-gray-700">
        <?php for ($y = $season_year_min; $y <= $season_year_max; $y++): ?>
          <option value="<?= $y ?>" <?= $y === (int)$default_season_year ? 'selected' : '' ?>><?= $y ?></option>
        <?php endfor; ?>
      </select>
    </div>
  </div>

  <?php if (!$seasonal_schema_ready): ?>
    <div class="m-5 sm:m-6 rounded-2xl border border-amber-200 bg-amber-50 p-4 sm:p-5">
      <div class="flex gap-3">
        <i class="ph ph-warning-circle text-xl text-amber-700 mt-0.5"></i>
        <div>
          <div class="font-semibold text-amber-900 text-sm">Seasonal Events database upgrade required</div>
          <p class="text-xs text-amber-800/80 mt-1">Run <code class="font-mono">database/migrations/20260924_seasonal_events.sql</code> in phpMyAdmin. Regular events can still be managed below.</p>
        </div>
      </div>
    </div>
  <?php else: ?>
    <div class="p-5 sm:p-6">
      <div class="rounded-xl bg-blue-50/60 border border-blue-100 p-4 mb-5">
        <div class="flex gap-3">
          <i class="ph ph-info text-blue-700 text-lg mt-0.5"></i>
          <p class="text-xs text-blue-900/80 leading-relaxed">
            Seasonal dates are starter defaults, not locked schedules. <strong>Prepare</strong> creates that year as a Draft; then edit its image, description, start/end dates, event time, homepage promotion dates and status. This avoids recreating Lent, Christmas, Rosary Month, UNDAS, New Year and Fiesta every year.
          </p>
        </div>
      </div>

      <div id="seasonal-cards" class="grid sm:grid-cols-2 xl:grid-cols-3 gap-4">
        <div class="sm:col-span-2 xl:col-span-3 text-center py-8 text-sm text-gray-400">
          <i class="ph ph-spinner-gap animate-spin mr-1"></i> Loading seasonal events…
        </div>
      </div>
    </div>
  <?php endif; ?>
</section>

<section class="bg-white rounded-2xl border border-gray-100 p-5 sm:p-6">
  <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-5">
    <div>
      <h2 class="font-semibold text-gray-900">All Events</h2>
      <p class="text-xs text-gray-400 mt-1">Published, draft, completed and seasonal event records.</p>
    </div>
  </div>
  <div class="overflow-x-auto">
    <table id="event-table" class="w-full text-sm">
      <thead>
        <tr>
          <th>Title</th>
          <th>Category</th>
          <th>Date / Duration</th>
          <th>Registrations</th>
          <th>Status</th>
          <th class="text-center">Actions</th>
        </tr>
      </thead>
      <tbody></tbody>
    </table>
  </div>
</section>

<div id="event-modal" class="hidden fixed inset-0 z-50 items-center justify-center p-3 sm:p-5">
  <div class="absolute inset-0 bg-black/55 backdrop-blur-[1px]" onclick="closeModal()"></div>

  <div class="relative bg-white rounded-[1.5rem] shadow-2xl w-full max-w-4xl max-h-[94vh] flex flex-col overflow-hidden">
    <div class="flex-shrink-0 px-5 sm:px-6 py-4 border-b border-gray-100 flex items-center justify-between gap-4">
      <div>
        <div class="text-[10px] uppercase tracking-[.17em] text-gold-600 font-bold" id="event-modal-kicker">Event editor</div>
        <h2 class="font-semibold text-gray-900 text-lg mt-0.5" id="event-modal-title">New Event</h2>
      </div>
      <button type="button" onclick="closeModal()" class="w-9 h-9 rounded-xl hover:bg-gray-100 text-gray-500 flex items-center justify-center" aria-label="Close">
        <i class="ph ph-x text-xl"></i>
      </button>
    </div>

    <form id="event-form" class="flex-1 min-h-0 flex flex-col" enctype="multipart/form-data">
      <div class="flex-1 min-h-0 overflow-y-auto p-5 sm:p-6 space-y-6">
        <input type="hidden" name="id" id="f-id">

        <div id="seasonal-editor-note" class="hidden rounded-2xl border border-gold-200 bg-gold-50/70 p-4">
          <div class="flex gap-3">
            <i class="ph ph-calendar-star text-gold-700 text-xl mt-0.5"></i>
            <div>
              <div class="font-semibold text-gold-900 text-sm">Yearly seasonal event</div>
              <p class="text-xs text-gold-800/80 mt-1">This is a prebuilt yearly record. Update the photo and exact schedule/details for this year. Your edits affect only this event year.</p>
            </div>
          </div>
        </div>

        <section>
          <h3 class="text-sm font-semibold text-gray-800 mb-4">Event Information</h3>
          <div class="space-y-4">
            <div>
              <label class="text-xs font-medium text-gray-500">Title</label>
              <input required name="title" id="f-title" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200 text-sm focus:border-parish-300 focus:ring-2 focus:ring-parish-100 outline-none">
            </div>

            <div>
              <label class="text-xs font-medium text-gray-500">Description / Schedule Details</label>
              <textarea name="description" id="f-desc" rows="6" placeholder="Add this year's Mass schedules, processions, prayer activities, instructions and important reminders…" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200 text-sm leading-relaxed focus:border-parish-300 focus:ring-2 focus:ring-parish-100 outline-none"></textarea>
              <p class="text-[11px] text-gray-400 mt-1">For seasonal events, include the actual schedule and visitor information for that specific year.</p>
            </div>

            <div class="grid sm:grid-cols-2 gap-4">
              <div>
                <label class="text-xs font-medium text-gray-500">Category</label>
                <input name="category" id="f-category" placeholder="e.g. Liturgical Season, Fiesta" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200 text-sm">
              </div>
              <div>
                <label class="text-xs font-medium text-gray-500">Location</label>
                <input name="location" id="f-location" placeholder="Sta. Monica Parish Church" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200 text-sm">
              </div>
            </div>
          </div>
        </section>

        <section class="rounded-2xl border border-gray-100 bg-gray-50/50 p-4 sm:p-5">
          <div class="grid lg:grid-cols-[.85fr_1.15fr] gap-5 items-start">
            <div>
              <label class="text-xs font-semibold text-gray-600">Event Photo</label>
              <div id="cover-preview" class="mt-2 aspect-[16/10] rounded-2xl overflow-hidden border border-dashed border-gray-300 bg-white flex items-center justify-center text-center">
                <div id="cover-placeholder" class="px-5 text-gray-400">
                  <i class="ph ph-image text-3xl"></i>
                  <div class="text-xs mt-2">Upload this year's event graphic or parish photo</div>
                </div>
                <img id="cover-preview-img" src="" alt="Event cover preview" class="hidden w-full h-full object-cover">
              </div>
            </div>
            <div>
              <label class="text-xs font-semibold text-gray-600">Replace Photo</label>
              <input type="file" accept=".jpg,.jpeg,.png,.webp,image/jpeg,image/png,image/webp" name="cover_image" id="f-cover" class="mt-2 block w-full text-sm text-gray-500 file:mr-3 file:px-4 file:py-2.5 file:rounded-xl file:border-0 file:bg-parish-50 file:text-parish-700 file:font-semibold hover:file:bg-parish-100">
              <p class="text-[11px] text-gray-400 mt-2 leading-relaxed">JPG, PNG or WebP, up to 5 MB. Landscape graphics around 16:9 or 3:2 work best for the homepage feature and event cards.</p>
              <div class="mt-4 rounded-xl bg-white border border-gray-100 p-3 text-xs text-gray-500">
                <strong class="text-gray-700">Tip:</strong> Use the parish's own decorations, procession, church interior, fiesta or seasonal artwork whenever possible so the public site feels local to Alburquerque.
              </div>
            </div>
          </div>
        </section>

        <section>
          <div class="flex items-center justify-between gap-3 mb-4">
            <div>
              <h3 class="text-sm font-semibold text-gray-800">Date, Time & Duration</h3>
              <p class="text-[11px] text-gray-400 mt-1">Change the End Date to control how long the event or season runs.</p>
            </div>
            <span id="duration-badge" class="px-3 py-1.5 rounded-full bg-parish-50 text-parish-700 text-xs font-semibold">1 day</span>
          </div>

          <div class="grid sm:grid-cols-3 gap-4">
            <div>
              <label class="text-xs font-medium text-gray-500">Start Date</label>
              <input required type="date" name="event_date" id="f-date" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200 text-sm">
            </div>
            <div>
              <label class="text-xs font-medium text-gray-500">End Date</label>
              <input type="date" name="end_date" id="f-end" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200 text-sm">
            </div>
            <div>
              <label class="text-xs font-medium text-gray-500">Primary Time</label>
              <input type="time" name="event_time" id="f-time" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200 text-sm">
              <p class="text-[10px] text-gray-400 mt-1">Optional; detailed multi-day schedules belong in Description.</p>
            </div>
          </div>
        </section>

        <section id="homepage-highlight-section" class="rounded-2xl border border-parish-100 bg-parish-50/50 p-4 sm:p-5">
          <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">
            <div>
              <div class="flex items-center gap-2 text-sm font-semibold text-parish-900"><i class="ph ph-star"></i> Public Homepage Highlight</div>
              <p class="text-xs text-gray-500 mt-1 max-w-2xl">When enabled, the event appears as a large seasonal feature on the homepage only during the promotion window below. It disappears automatically afterward.</p>
            </div>
            <label class="inline-flex items-center gap-2 text-sm font-semibold text-parish-800 whitespace-nowrap">
              <input type="checkbox" name="highlight_on_home" id="f-highlight" value="1" class="rounded border-gray-300 text-parish-700">
              Highlight
            </label>
          </div>

          <div class="grid sm:grid-cols-2 gap-4 mt-4">
            <div>
              <label class="text-xs font-medium text-gray-500">Show on Homepage From</label>
              <input type="date" name="highlight_start" id="f-highlight-start" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200 bg-white text-sm">
            </div>
            <div>
              <label class="text-xs font-medium text-gray-500">Hide After</label>
              <input type="date" name="highlight_end" id="f-highlight-end" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200 bg-white text-sm">
            </div>
          </div>
        </section>

        <section class="grid md:grid-cols-2 gap-5">
          <div class="rounded-2xl border border-gray-100 p-4 sm:p-5">
            <div class="text-sm font-semibold text-gray-800">Registration</div>
            <label class="mt-3 flex items-start gap-2 text-sm text-gray-600">
              <input type="checkbox" name="allow_registration" id="f-allow" value="1" class="mt-0.5 rounded border-gray-300 text-parish-700">
              <span><strong class="block text-gray-800">Allow online registration</strong><span class="text-xs text-gray-400">Useful for formation, youth and parish activities.</span></span>
            </label>
            <div class="mt-3">
              <label class="text-xs font-medium text-gray-500">Registration Limit</label>
              <input type="number" min="1" name="registration_limit" id="f-limit" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200 text-sm">
            </div>
          </div>

          <div class="rounded-2xl border border-gray-100 p-4 sm:p-5">
            <div class="text-sm font-semibold text-gray-800">Publishing</div>
            <div class="mt-3">
              <label class="text-xs font-medium text-gray-500">Status</label>
              <select name="status" id="f-status" class="mt-1.5 w-full px-4 py-3 rounded-xl border border-gray-200 bg-white text-sm">
                <option value="draft">Draft</option>
                <option value="published">Published</option>
                <option value="cancelled">Cancelled</option>
                <option value="completed">Completed</option>
              </select>
              <p class="text-[11px] text-gray-400 mt-1">Seasonal templates start as Draft so last year's details are never shown accidentally.</p>
            </div>
          </div>
        </section>
      </div>

      <div class="flex-shrink-0 px-5 sm:px-6 py-4 bg-white border-t border-gray-100 flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-3 shadow-[0_-8px_24px_rgba(15,23,42,0.04)]">
        <button type="button" onclick="closeModal()" class="w-full sm:w-auto px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">Cancel</button>
        <button type="submit" id="event-save-btn" class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-parish-700 hover:bg-parish-800 text-white text-sm font-semibold">
          <i class="ph ph-floppy-disk mr-1"></i> Save Event
        </button>
      </div>
    </form>
  </div>
</div>

<script>
var table;
var seasonalReady = <?= $seasonal_schema_ready ? 'true' : 'false' ?>;

function openModal(){
  $('#event-modal').removeClass('hidden').addClass('flex');
  $('body').addClass('overflow-hidden');
}

function closeModal(){
  $('#event-modal').addClass('hidden').removeClass('flex');
  $('body').removeClass('overflow-hidden');
}

function resetForm(){
  $('#event-form')[0].reset();
  $('#f-id').val('');
  $('#f-status').val('published');
  $('#event-modal-title').text('New Event');
  $('#event-modal-kicker').text('Event editor');
  $('#seasonal-editor-note').addClass('hidden');
  $('#cover-preview-img').attr('src','').addClass('hidden');
  $('#cover-placeholder').removeClass('hidden');
  updateDuration();
}

function updateDuration(){
  var start = $('#f-date').val();
  var end = $('#f-end').val() || start;
  if(!start){
    $('#duration-badge').text('Set dates');
    return;
  }

  var a = new Date(start + 'T00:00:00');
  var b = new Date(end + 'T00:00:00');
  if(isNaN(a.getTime()) || isNaN(b.getTime()) || b < a){
    $('#duration-badge').text('Check dates').removeClass('bg-parish-50 text-parish-700').addClass('bg-red-50 text-red-600');
    return;
  }

  var days = Math.floor((b - a) / 86400000) + 1;
  $('#duration-badge')
    .text(days + ' day' + (days === 1 ? '' : 's'))
    .removeClass('bg-red-50 text-red-600')
    .addClass('bg-parish-50 text-parish-700');
}

function setCoverPreview(url){
  if(url){
    $('#cover-preview-img').attr('src', url).removeClass('hidden');
    $('#cover-placeholder').addClass('hidden');
  } else {
    $('#cover-preview-img').attr('src','').addClass('hidden');
    $('#cover-placeholder').removeClass('hidden');
  }
}

function editEvent(id){
  $.get('<?= site_url('admin/event/get/') ?>' + id, function(res){
    if(!res.success){
      toastr.error(res.message || 'Event not found.');
      return;
    }

    var d = res.data;
    resetForm();
    $('#f-id').val(d.id);
    $('#f-title').val(d.title);
    $('#f-desc').val(d.description);
    $('#f-category').val(d.category);
    $('#f-location').val(d.location);
    $('#f-date').val(d.event_date);
    $('#f-end').val(d.end_date);
    $('#f-time').val(d.event_time ? String(d.event_time).slice(0,5) : '');
    $('#f-allow').prop('checked', parseInt(d.allow_registration,10) === 1);
    $('#f-limit').val(d.registration_limit);
    $('#f-status').val(d.status);
    $('#f-highlight').prop('checked', parseInt(d.highlight_on_home || 0,10) === 1);
    $('#f-highlight-start').val(d.highlight_start || '');
    $('#f-highlight-end').val(d.highlight_end || '');
    setCoverPreview(d.cover_url || '');

    if(parseInt(d.is_seasonal || 0,10) === 1){
      $('#event-modal-kicker').text('Seasonal event · ' + (d.season_year || ''));
      $('#event-modal-title').text('Edit ' + d.title);
      $('#seasonal-editor-note').removeClass('hidden');
    } else {
      $('#event-modal-title').text('Edit Event');
    }

    updateDuration();
    openModal();
  }).fail(function(){
    toastr.error('Unable to load event.');
  });
}

function deleteEvent(id){
  Swal.fire({
    icon:'warning',
    title:'Delete this event?',
    text:'This removes the event and its uploaded event photo.',
    showCancelButton:true,
    confirmButtonText:'Delete',
    confirmButtonColor:'#dc2626'
  }).then(function(r){
    if(!r.isConfirmed) return;
    $.post('<?= site_url('admin/event/delete/') ?>' + id, function(res){
      if(res.success){
        toastr.success(res.message);
        table.ajax.reload(null,false);
        loadSeasonalTemplates($('#season-year').val());
      } else {
        toastr.error(res.message || 'Could not delete event.');
      }
    });
  });
}

function seasonalTheme(key){
  var map = {
    lent: ['#4b2b57','#bca2ca'],
    fiesta: ['#7a2f25','#e1b649'],
    rosary_month: ['#234d74','#c8d9eb'],
    undas: ['#303030','#d1b17c'],
    christmas: ['#174a39','#e2bc54'],
    new_year: ['#203450','#dac178']
  };
  return map[key] || ['#235a38','#d7bd76'];
}

function loadSeasonalTemplates(year){
  if(!seasonalReady) return;

  $('#seasonal-cards').html('<div class="sm:col-span-2 xl:col-span-3 text-center py-8 text-sm text-gray-400"><i class="ph ph-spinner-gap animate-spin mr-1"></i> Loading seasonal events…</div>');

  $.get('<?= site_url('admin/event/seasonal-templates') ?>', {year:year}, function(res){
    if(!res.success){
      $('#seasonal-cards').html('<div class="sm:col-span-2 xl:col-span-3 rounded-xl bg-amber-50 border border-amber-100 p-4 text-sm text-amber-800">' + $('<div>').text(res.message || 'Unable to load seasonal templates.').html() + '</div>');
      return;
    }

    $('#seasonal-cards').empty();
    res.templates.forEach(function(item){
      var event = item.event || null;
      var colors = seasonalTheme(item.key);
      var status = event ? String(event.status || 'draft') : 'not prepared';
      var statusClass = status === 'published' ? 'bg-emerald-100 text-emerald-700' : (status === 'draft' ? 'bg-amber-100 text-amber-700' : 'bg-gray-100 text-gray-600');
      var buttonLabel = event ? 'Edit ' + res.year : 'Prepare ' + res.year;
      var action = event
        ? 'editEvent(' + parseInt(event.id,10) + ')'
        : "prepareSeasonal('" + item.key + "'," + parseInt(res.year,10) + ")";

      var eventStart = event && event.event_date ? event.event_date : item.event_date;
      var eventEnd = event && event.end_date ? event.end_date : item.end_date;
      var eventTitle = event && event.title ? event.title : item.label;
      var cover = event && event.cover_image
        ? '<?= base_url() ?>' + String(event.cover_image).replace(/^\//,'')
        : '';

      var html =
        '<article class="rounded-2xl overflow-hidden border border-gray-100 bg-white">' +
          '<div class="relative h-36 p-4 text-white flex flex-col justify-between overflow-hidden" style="background:linear-gradient(135deg,' + colors[0] + ',' + colors[0] + 'dd)">' +
            (cover ? '<img src="' + cover + '" alt="" class="absolute inset-0 w-full h-full object-cover opacity-35">' : '') +
            '<div class="absolute inset-0" style="background:linear-gradient(90deg,' + colors[0] + 'ee,' + colors[0] + '88)"></div>' +
            '<div class="relative flex items-start justify-between gap-3">' +
              '<div class="w-10 h-10 rounded-xl bg-white/15 backdrop-blur flex items-center justify-center"><i class="ph ' + item.icon + ' text-xl"></i></div>' +
              '<span class="px-2 py-1 rounded-full text-[10px] font-bold uppercase tracking-wide ' + statusClass + '">' + $('<div>').text(status).html() + '</span>' +
            '</div>' +
            '<div class="relative">' +
              '<div class="text-[10px] uppercase tracking-[.14em] font-bold" style="color:' + colors[1] + '">Seasonal template</div>' +
              '<div class="font-semibold text-lg leading-tight mt-1">' + $('<div>').text(eventTitle).html() + '</div>' +
            '</div>' +
          '</div>' +
          '<div class="p-4">' +
            '<div class="text-xs text-gray-500 flex items-center gap-2"><i class="ph ph-calendar-blank text-parish-700"></i>' + formatIsoDate(eventStart) + ' – ' + formatIsoDate(eventEnd) + '</div>' +
            '<div class="mt-4 flex items-center justify-between gap-3">' +
              '<span class="text-[11px] text-gray-400">' + $('<div>').text(item.category).html() + '</span>' +
              '<button type="button" onclick="' + action + '" class="px-3 py-2 rounded-lg bg-parish-50 hover:bg-parish-100 text-parish-700 text-xs font-semibold">' + buttonLabel + '</button>' +
            '</div>' +
          '</div>' +
        '</article>';

      $('#seasonal-cards').append(html);
    });
  });
}

function formatIsoDate(value){
  if(!value) return 'TBA';
  var d = new Date(value + 'T00:00:00');
  return d.toLocaleDateString(undefined,{month:'short',day:'numeric',year:'numeric'});
}

function prepareSeasonal(key, year){
  Swal.fire({
    icon:'question',
    title:'Prepare this seasonal event?',
    text:'A Draft event will be created for ' + year + '. You can then add the year’s photo and exact parish schedule.',
    showCancelButton:true,
    confirmButtonText:'Prepare Event',
    confirmButtonColor:'#235a38'
  }).then(function(result){
    if(!result.isConfirmed) return;

    $.post('<?= site_url('admin/event/prepare-seasonal') ?>', {
      season_key:key,
      season_year:year
    }, function(res){
      if(!res.success){
        Swal.fire({icon:'error',title:'Could not prepare event',text:res.message || 'Please try again.',confirmButtonColor:'#235a38'});
        return;
      }

      toastr.success(res.message);
      table.ajax.reload(null,false);
      loadSeasonalTemplates(year);
      editEvent(res.id);
    }).fail(function(){
      Swal.fire({icon:'error',title:'Could not prepare event',text:'The server did not accept the request.',confirmButtonColor:'#235a38'});
    });
  });
}

$('#f-date, #f-end').on('change', updateDuration);

$('#f-cover').on('change', function(){
  var file = this.files && this.files[0];
  if(!file) return;
  var reader = new FileReader();
  reader.onload = function(e){ setCoverPreview(e.target.result); };
  reader.readAsDataURL(file);
});

$('#event-form').on('submit', function(e){
  e.preventDefault();

  var $btn = $('#event-save-btn');
  var original = $btn.html();
  $btn.prop('disabled',true).addClass('opacity-60').html('<i class="ph ph-spinner-gap animate-spin mr-1"></i> Saving…');

  var data = new FormData(this);
  $.ajax({
    url:'<?= site_url('admin/event/store') ?>',
    type:'POST',
    data:data,
    processData:false,
    contentType:false,
    success:function(res){
      if(res.success){
        toastr.success(res.message);
        closeModal();
        table.ajax.reload(null,false);
        if(seasonalReady) loadSeasonalTemplates($('#season-year').val());
      } else {
        Swal.fire({icon:'error',title:'Could not save event',text:res.message || 'Please review the form.',confirmButtonColor:'#235a38'});
      }
    },
    error:function(){
      Swal.fire({icon:'error',title:'Could not save event',text:'The server did not accept the update.',confirmButtonColor:'#235a38'});
    },
    complete:function(){
      $btn.prop('disabled',false).removeClass('opacity-60').html(original);
    }
  });
});

$('#season-year').on('change', function(){
  loadSeasonalTemplates($(this).val());
});

$(function(){
  table = $('#event-table').DataTable({
    processing:true,
    serverSide:true,
    ajax:{url:'<?= site_url('admin/event/datatable') ?>',type:'POST'},
    columns:[
      {data:'title'},
      {data:'category'},
      {data:'event_date'},
      {data:'registrations'},
      {data:'status',orderable:false},
      {data:'actions',orderable:false,searchable:false,className:'whitespace-nowrap'}
    ],
    order:[[2,'desc']],
    language:{search:'',searchPlaceholder:'Search events…'}
  });

  if(seasonalReady){
    loadSeasonalTemplates($('#season-year').val());
  }
});
</script>
