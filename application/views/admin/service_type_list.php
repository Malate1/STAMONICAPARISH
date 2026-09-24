<?php $service_config_base = ((int) $current_user['role_id'] === ROLE_SECRETARY) ? 'staff/service-config' : 'admin/service_type'; ?>
<div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-6">
  <div>
    <h1 class="text-2xl font-semibold text-gray-900">Service Configuration</h1>
    <p class="text-gray-500 text-sm mt-1">Manage service fees, availability rules, regular/free schedules, special-booking hours, and requirements.</p>
  </div>
  <div class="inline-flex items-center gap-2 text-xs text-gray-500 bg-white border border-gray-200 rounded-full px-4 py-2 self-start">
    <i class="ph ph-calendar-check text-parish-700"></i>
    Availability is checked across all Main Church bookings
  </div>
</div>

<div class="mb-6 grid md:grid-cols-2 xl:grid-cols-4 gap-3">
  <div class="rounded-2xl border border-parish-100 bg-parish-50/50 p-4">
    <div class="flex items-center gap-2 text-sm font-semibold text-parish-800"><i class="ph ph-calendar-heart"></i> Regular Schedule</div>
    <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">Recurring parish dates such as 2nd &amp; 4th Thursday. Each rule can have its own time, fee, and capacity.</p>
  </div>
  <div class="rounded-2xl border border-gold-100 bg-gold-50/50 p-4">
    <div class="flex items-center gap-2 text-sm font-semibold text-gold-800"><i class="ph ph-sparkle"></i> Special Booking</div>
    <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">Dates outside the regular schedule. The special fee and allowed hours are configured separately.</p>
  </div>
  <div class="rounded-2xl border border-blue-100 bg-blue-50/50 p-4">
    <div class="flex items-center gap-2 text-sm font-semibold text-blue-800"><i class="ph ph-clock"></i> Protected Time Window</div>
    <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">Preparation before + ceremony duration + clearance after are all protected from another Main Church booking.</p>
  </div>
  <div class="rounded-2xl border border-gray-200 bg-white p-4">
    <div class="flex items-center gap-2 text-sm font-semibold text-gray-800"><i class="ph ph-church"></i> Main Church</div>
    <p class="text-xs text-gray-500 mt-1.5 leading-relaxed">Services marked Main Church cannot overlap another Main Church booking on the same date and time.</p>
  </div>
</div>

<div class="grid sm:grid-cols-2 xl:grid-cols-3 gap-5">
  <?php foreach ($service_types as $s): ?>
  <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
    <div class="flex items-start justify-between gap-3">
      <div>
        <h3 class="font-semibold text-gray-900"><?= html_escape($s['name']) ?></h3>
        <div class="text-[11px] uppercase tracking-wide text-gray-400 mt-1"><?= html_escape($s['category']) ?></div>
      </div>
      <span class="px-2 py-0.5 rounded-full text-[11px] font-medium <?= $s['is_active'] ? 'bg-emerald-100 text-emerald-700' : 'bg-gray-100 text-gray-500' ?>">
        <?= $s['is_active'] ? 'Active' : 'Inactive' ?>
      </span>
    </div>

    <p class="text-xs text-gray-500 mt-3 line-clamp-3 min-h-[48px]"><?= html_escape($s['description']) ?></p>

    <div class="mt-4 grid grid-cols-2 gap-2 text-xs">
      <div class="rounded-xl bg-gray-50 p-3">
        <div class="text-gray-400">Base fee</div>
        <div class="font-semibold text-gray-800 mt-1"><?= peso($s['base_fee']) ?></div>
      </div>
      <div class="rounded-xl bg-gold-50 p-3">
        <div class="text-gold-700/70">Special fee</div>
        <div class="font-semibold text-gold-800 mt-1"><?= !empty($s['allow_special_booking']) ? peso($s['special_fee']) : 'Disabled' ?></div>
      </div>
    </div>

    <div class="mt-3 flex flex-wrap gap-1.5">
      <?php if (!empty($s['uses_main_church'])): ?><span class="px-2 py-1 rounded-full bg-parish-50 text-parish-700 text-[10px] font-medium">Main Church</span><?php endif; ?>
      <?php if (!empty($s['requires_priest'])): ?><span class="px-2 py-1 rounded-full bg-blue-50 text-blue-700 text-[10px] font-medium">Priest required</span><?php endif; ?>
      <span class="px-2 py-1 rounded-full bg-gray-50 text-gray-500 text-[10px]"><?= (int)($s['booking_buffer_before_minutes'] ?? 0) ?> + <?= (int)$s['duration_minutes'] ?> + <?= (int)$s['booking_buffer_minutes'] ?> min protected</span>
      <?php if (!empty($s['allow_special_booking'])): ?><span class="px-2 py-1 rounded-full bg-gold-50 text-gold-700 text-[10px]">Special booking enabled</span><?php endif; ?>
    </div>

    <button onclick="editService(<?= (int)$s['id'] ?>)" class="mt-5 w-full py-2.5 rounded-xl border border-gray-200 text-sm font-medium text-gray-700 hover:bg-parish-50 hover:border-parish-200 hover:text-parish-800 transition">
      <i class="ph ph-sliders-horizontal mr-1"></i> Configure Availability
    </button>
  </div>
  <?php endforeach; ?>
</div>

<!-- Service configuration modal -->
<div id="svc-modal" class="hidden fixed inset-0 z-50 items-center justify-center p-3 sm:p-4">
  <div class="absolute inset-0 bg-black/50" onclick="closeModal()"></div>
  <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-5xl max-h-[94vh] overflow-hidden flex flex-col">
    <div class="flex items-center justify-between px-6 py-5 border-b border-gray-100">
      <div>
        <div class="text-[10px] uppercase tracking-[.14em] font-bold text-gold-600">Service configuration</div>
        <h2 class="font-semibold text-gray-900 text-lg mt-1" id="svc-title">Configure Service</h2>
      </div>
      <button type="button" onclick="closeModal()" class="w-9 h-9 rounded-full hover:bg-gray-100 text-gray-500 flex items-center justify-center"><i class="ph ph-x text-xl"></i></button>
    </div>

    <form id="svc-form" class="flex-1 min-h-0 flex flex-col">
      <div class="overflow-y-auto p-6 sm:p-6 space-y-6">
        <input type="hidden" name="id" id="f-id">

        <section class="rounded-2xl border border-gray-100 p-5">
          <div class="mb-4">
            <div class="text-xs font-bold uppercase tracking-wide text-parish-700">Basic settings</div>
            <p class="text-xs text-gray-400 mt-1">These values control how much calendar time the service uses and how far in advance parishioners may book.</p>
          </div>

          <div class="mb-5 rounded-xl bg-blue-50 border border-blue-100 p-4 text-xs text-blue-900 leading-relaxed">
            <div class="font-semibold mb-1"><i class="ph ph-info mr-1"></i> How these fields affect availability</div>
            <p><strong>Preparation Before</strong> reserves the church before the ceremony, <strong>Duration</strong> covers the ceremony itself, and <strong>Clearance After</strong> protects time for photos, guest exit, cleanup, or setup for the next service. The system also checks priest availability before offering a slot.</p>
          </div>

          <div class="grid md:grid-cols-2 gap-4">
            <div>
              <label class="text-xs font-medium text-gray-500">Name</label>
              <input required name="name" id="f-name" class="mt-1 w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm">
            </div>
            <div>
              <label class="text-xs font-medium text-gray-500">Default / Base Fee (₱)</label>
              <input required type="number" min="0" step="0.01" name="base_fee" id="f-fee" class="mt-1 w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm">
              <p class="text-[11px] text-gray-400 mt-1">General fallback fee. A Regular Schedule rule or Special Booking fee can replace this amount.</p>
            </div>
            <div class="md:col-span-2">
              <label class="text-xs font-medium text-gray-500">Description</label>
              <textarea name="description" id="f-desc" rows="2" class="mt-1 w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm"></textarea>
            </div>
          </div>

          <div class="grid sm:grid-cols-2 lg:grid-cols-5 gap-4 mt-4">
            <div>
              <label class="text-xs font-medium text-gray-500">Preparation Before</label>
              <input type="number" min="0" step="5" name="booking_buffer_before_minutes" id="f-buffer-before" class="mt-1 w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm">
              <p class="text-[11px] text-gray-400 mt-1">Minutes reserved before the ceremony for arrival, setup, procession, or staging.</p>
            </div>
            <div>
              <label class="text-xs font-medium text-gray-500">Service Duration</label>
              <input type="number" min="15" step="15" name="duration_minutes" id="f-duration" class="mt-1 w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm">
              <p class="text-[11px] text-gray-400 mt-1">How long the ceremony or parish service itself normally lasts.</p>
            </div>
            <div>
              <label class="text-xs font-medium text-gray-500">Clearance After</label>
              <input type="number" min="0" step="5" name="booking_buffer_minutes" id="f-buffer" class="mt-1 w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm">
              <p class="text-[11px] text-gray-400 mt-1">Protected time after the ceremony for pictorials, guest exit, cleanup, or reset.</p>
            </div>
            <div>
              <label class="text-xs font-medium text-gray-500">Minimum Advance Days</label>
              <input type="number" min="0" name="min_advance_days" id="f-min-days" class="mt-1 w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm">
              <p class="text-[11px] text-gray-400 mt-1">Example: 7 means parishioners must book at least 7 days before the service.</p>
            </div>
            <div>
              <label class="text-xs font-medium text-gray-500">Maximum Advance Days</label>
              <input type="number" min="1" name="max_advance_days" id="f-max-days" class="mt-1 w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm">
              <p class="text-[11px] text-gray-400 mt-1">Example: 180 means dates beyond 6 months are not shown yet.</p>
            </div>
          </div>

          <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-3 mt-4">
            <label class="flex items-start gap-2 rounded-xl border border-gray-100 p-3 text-sm text-gray-600">
              <input type="checkbox" name="uses_main_church" id="f-main-church" value="1" class="mt-0.5 rounded border-gray-300 text-parish-700">
              <span><strong class="block text-gray-800">Uses Main Church</strong><span class="text-xs">Prevents overlapping weddings, baptisms, funerals, etc.</span></span>
            </label>
            <label class="flex items-start gap-2 rounded-xl border border-gray-100 p-3 text-sm text-gray-600">
              <input type="checkbox" name="requires_priest" id="f-requires-priest" value="1" class="mt-0.5 rounded border-gray-300 text-parish-700">
              <span><strong class="block text-gray-800">Requires Priest</strong><span class="text-xs">Only offer a slot when at least one of the active parish priests is free.</span></span>
            </label>
            <label class="flex items-start gap-2 rounded-xl border border-gray-100 p-3 text-sm text-gray-600">
              <input type="checkbox" name="requires_approval_workflow" id="f-workflow" value="1" class="mt-0.5 rounded border-gray-300 text-parish-700">
              <span><strong class="block text-gray-800">Approval workflow</strong><span class="text-xs">Useful for weddings and complex services.</span></span>
            </label>
            <label class="flex items-start gap-2 rounded-xl border border-gray-100 p-3 text-sm text-gray-600">
              <input type="checkbox" name="is_active" id="f-active" value="1" class="mt-0.5 rounded border-gray-300 text-parish-700">
              <span><strong class="block text-gray-800">Active</strong><span class="text-xs">Visible and bookable by parishioners.</span></span>
            </label>
          </div>
        </section>

        <section class="rounded-2xl border border-gold-100 bg-gold-50/35 p-5">
          <div class="flex flex-col md:flex-row md:items-start md:justify-between gap-3 mb-4">
            <div>
              <div class="text-xs font-bold uppercase tracking-wide text-gold-700">Special booking</div>
              <p class="text-xs text-gray-500 mt-1">Applies to dates outside the regular parish schedule. Fee and hours are fully configurable.</p>
            </div>
            <label class="inline-flex items-center gap-2 text-sm text-gray-700">
              <input type="checkbox" name="allow_special_booking" id="f-special-enabled" value="1" class="rounded border-gray-300 text-gold-600">
              Enable special booking
            </label>
          </div>

          <div class="mb-4 rounded-xl bg-white/80 border border-gold-100 p-4 text-xs text-gray-600 leading-relaxed">
            <strong class="text-gold-800">Special Booking legend:</strong>
            the fee is charged only for Special bookings; the start/end time defines the full daily protected church window; preparation and clearance must fit inside it; the interval controls how often ceremony start times are generated.
          </div>

          <div id="special-settings" class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
              <label class="text-xs font-medium text-gray-500">Special Booking Fee (₱)</label>
              <input type="number" min="0" step="0.01" name="special_fee" id="f-special-fee" class="mt-1 w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-white text-sm">
              <p class="text-[11px] text-gray-400 mt-1">Amount charged for dates outside the regular parish schedule.</p>
            </div>
            <div>
              <label class="text-xs font-medium text-gray-500">Special Hours Start</label>
              <input type="time" name="special_start_time" id="f-special-start" class="mt-1 w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-white text-sm">
              <p class="text-[11px] text-gray-400 mt-1">Earliest time the church can be reserved, including preparation before the ceremony.</p>
            </div>
            <div>
              <label class="text-xs font-medium text-gray-500">Special Hours End</label>
              <input type="time" name="special_end_time" id="f-special-end" class="mt-1 w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-white text-sm">
              <p class="text-[11px] text-gray-400 mt-1">Latest time the protected church window may end, including clearance after the ceremony.</p>
            </div>
            <div>
              <label class="text-xs font-medium text-gray-500">Start-Time Interval</label>
              <select name="slot_interval_minutes" id="f-slot-interval" class="mt-1 w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-white text-sm">
                <option value="15">15 minutes</option>
                <option value="30">30 minutes</option>
                <option value="45">45 minutes</option>
                <option value="60">1 hour</option>
                <option value="90">1.5 hours</option>
                <option value="120">2 hours</option>
              </select>
              <p class="text-[11px] text-gray-400 mt-1">Example: 60 minutes creates 8:00, 9:00, 10:00… start choices.</p>
            </div>
          </div>
        </section>

        <section class="rounded-2xl border border-parish-100 bg-parish-50/30 p-5">
          <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 mb-4">
            <div>
              <div class="text-xs font-bold uppercase tracking-wide text-parish-700">Regular / Free Schedule Rules</div>
              <p class="text-xs text-gray-500 mt-1">Create recurring schedules such as “2nd &amp; 4th Thursday at 6:00 AM — Free”.</p>
            </div>
            <button type="button" onclick="openRuleEditor()" class="inline-flex items-center gap-1.5 px-3.5 py-2 rounded-xl bg-parish-700 hover:bg-parish-800 text-white text-xs font-semibold self-start">
              <i class="ph ph-plus"></i> Add Schedule Rule
            </button>
          </div>

          <div id="schedule-rules-list" class="space-y-3"></div>
          <div id="schedule-rules-empty" class="hidden rounded-xl border border-dashed border-parish-200 p-5 text-center text-xs text-gray-500">
            No regular schedule rules yet.
          </div>
        </section>

        <section class="rounded-2xl border border-gray-100 p-5">
          <div class="flex items-center justify-between gap-3 mb-4">
            <div>
              <div class="text-xs font-bold uppercase tracking-wide text-gray-600">Requirements</div>
              <p class="text-xs text-gray-400 mt-1">Documents parishioners may upload with this application.</p>
            </div>
            <button type="button" onclick="addRequirement()" class="text-xs text-parish-700 hover:underline font-medium">+ Add Requirement</button>
          </div>
          <div id="requirements-list" class="space-y-2"></div>
        </section>

      </div>

      <div class="flex-shrink-0 px-5 sm:px-6 py-4 bg-white border-t border-gray-100 flex flex-col-reverse sm:flex-row sm:items-center sm:justify-end gap-3 shadow-[0_-8px_24px_rgba(15,23,42,0.04)]">
        <button type="button" onclick="closeModal()" class="w-full sm:w-auto px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-medium text-gray-600 hover:bg-gray-50">Cancel</button>
        <button type="submit" id="save-service-settings" class="w-full sm:w-auto px-5 py-2.5 rounded-xl bg-parish-700 hover:bg-parish-800 text-white text-sm font-semibold">
          <i class="ph ph-floppy-disk mr-1"></i> Save Service Settings
        </button>
      </div>
    </form>
  </div>
</div>

<!-- Regular schedule rule modal -->
<div id="rule-modal" class="hidden fixed inset-0 z-[60] items-center justify-center p-4">
  <div class="absolute inset-0 bg-black/40" onclick="closeRuleModal()"></div>
  <div class="relative bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[92vh] overflow-y-auto p-6">
    <div class="flex items-center justify-between mb-5">
      <div>
        <div class="text-[10px] uppercase tracking-wider font-bold text-gold-600">Recurring schedule</div>
        <h3 class="font-semibold text-gray-900 mt-1" id="rule-title">Add Schedule Rule</h3>
      </div>
      <button type="button" onclick="closeRuleModal()" class="w-9 h-9 rounded-full hover:bg-gray-100 text-gray-500 flex items-center justify-center"><i class="ph ph-x"></i></button>
    </div>

    <form id="rule-form" class="space-y-4">
      <input type="hidden" name="id" id="r-id">
      <input type="hidden" name="service_type_id" id="r-service-id">

      <div class="rounded-xl bg-parish-50 border border-parish-100 p-4 text-xs text-gray-600 leading-relaxed">
        <div class="font-semibold text-parish-800 mb-1"><i class="ph ph-calendar-dots mr-1"></i> How a regular schedule rule works</div>
        <p>Pick the weekday and which occurrence(s) of that weekday are allowed each month. Example: <strong>Thursday + 2,4 + 6:00 AM</strong> means every 2nd and 4th Thursday at 6:00 AM. Set Fee to <strong>0</strong> for a free schedule.</p>
      </div>

      <div>
        <label class="text-xs font-medium text-gray-500">Rule Name</label>
        <input required name="rule_name" id="r-name" placeholder="e.g. Free Wedding - 2nd & 4th Thursday" class="mt-1 w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm">
        <p class="text-[11px] text-gray-400 mt-1">A staff-friendly label shown in the configuration and to parishioners.</p>
      </div>

      <div class="grid sm:grid-cols-2 gap-4">
        <div>
          <label class="text-xs font-medium text-gray-500">Day of Week</label>
          <select name="day_of_week" id="r-day" required class="mt-1 w-full px-4 py-2.5 rounded-xl border border-gray-200 bg-white text-sm">
            <option value="0">Sunday</option>
            <option value="1">Monday</option>
            <option value="2">Tuesday</option>
            <option value="3">Wednesday</option>
            <option value="4">Thursday</option>
            <option value="5">Friday</option>
            <option value="6">Saturday</option>
          </select>
          <p class="text-[11px] text-gray-400 mt-1">The weekday on which this recurring schedule happens.</p>
        </div>
        <div>
          <label class="text-xs font-medium text-gray-500">Occurrences in Month</label>
          <input required name="week_numbers" id="r-weeks" placeholder="2,4" class="mt-1 w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm">
          <p class="text-[11px] text-gray-400 mt-1">Use comma-separated numbers: 1 = 1st, 2 = 2nd, 3 = 3rd, 4 = 4th, 5 = 5th. Example: <strong>2,4</strong>.</p>
        </div>
      </div>

      <div class="grid sm:grid-cols-3 gap-4">
        <div>
          <label class="text-xs font-medium text-gray-500">Start Time</label>
          <input required type="time" name="start_time" id="r-time" class="mt-1 w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm">
          <p class="text-[11px] text-gray-400 mt-1">The exact start time parishioners may select.</p>
        </div>
        <div>
          <label class="text-xs font-medium text-gray-500">Regular Schedule Fee (₱)</label>
          <input required type="number" min="0" step="0.01" name="fee_amount" id="r-fee" value="0" class="mt-1 w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm">
          <p class="text-[11px] text-gray-400 mt-1">Enter 0.00 when this recurring schedule is free.</p>
        </div>
        <div>
          <label class="text-xs font-medium text-gray-500">Capacity</label>
          <input required type="number" min="1" name="capacity" id="r-capacity" value="1" class="mt-1 w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm">
          <p class="text-[11px] text-gray-400 mt-1">How many bookings may share this exact regular slot. Use 1 for weddings.</p>
        </div>
      </div>

      <div class="grid sm:grid-cols-3 gap-4">
        <div>
          <label class="text-xs font-medium text-gray-500">Valid From <span class="font-normal text-gray-400">(optional)</span></label>
          <input type="date" name="valid_from" id="r-valid-from" class="mt-1 w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm">
          <p class="text-[11px] text-gray-400 mt-1">Leave blank to make the rule effective immediately.</p>
        </div>
        <div>
          <label class="text-xs font-medium text-gray-500">Valid Until <span class="font-normal text-gray-400">(optional)</span></label>
          <input type="date" name="valid_until" id="r-valid-until" class="mt-1 w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm">
          <p class="text-[11px] text-gray-400 mt-1">Leave blank if this recurring rule has no planned end date.</p>
        </div>
        <div>
          <label class="text-xs font-medium text-gray-500">Display Order</label>
          <input type="number" name="display_order" id="r-order" value="0" class="mt-1 w-full px-4 py-2.5 rounded-xl border border-gray-200 text-sm">
          <p class="text-[11px] text-gray-400 mt-1">Lower numbers appear first when several regular rules exist.</p>
        </div>
      </div>

      <label class="flex items-center gap-2 text-sm text-gray-600">
        <input type="checkbox" name="is_active" id="r-active" value="1" checked class="rounded border-gray-300 text-parish-700">
        Active / publish this schedule
      </label>

      <div class="flex justify-end gap-3 pt-2">
        <button type="button" onclick="closeRuleModal()" class="px-4 py-2.5 rounded-xl border border-gray-200 text-sm font-medium text-gray-600">Cancel</button>
        <button type="submit" class="px-5 py-2.5 rounded-xl bg-parish-700 hover:bg-parish-800 text-white text-sm font-semibold">Save Schedule Rule</button>
      </div>
    </form>
  </div>
</div>

<script>
var currentService = null;
var currentRules = [];

function closeModal(){
  $('#svc-modal').addClass('hidden').removeClass('flex');
}
function closeRuleModal(){
  $('#rule-modal').addClass('hidden').removeClass('flex');
}
function weekdayLabel(day){
  return ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'][parseInt(day,10)] || '';
}
function formatTime(t){
  if(!t) return 'Time not configured';
  var parts = t.split(':');
  var h = parseInt(parts[0],10);
  var m = parts[1];
  var ap = h >= 12 ? 'PM' : 'AM';
  h = h % 12 || 12;
  return h + ':' + m + ' ' + ap;
}
function escapeHtml(text){
  return $('<div>').text(text == null ? '' : text).html();
}
function renderRules(){
  var $list = $('#schedule-rules-list').empty();
  $('#schedule-rules-empty').toggleClass('hidden', currentRules.length > 0);

  currentRules.forEach(function(r){
    var weeks = String(r.week_numbers || '').split(',').filter(Boolean).map(function(w){
      return ({'1':'1st','2':'2nd','3':'3rd','4':'4th','5':'5th'})[w] || w;
    }).join(' & ');

    var status = parseInt(r.is_active,10) === 1
      ? '<span class="px-2 py-1 rounded-full bg-emerald-100 text-emerald-700 text-[10px] font-semibold">Active</span>'
      : '<span class="px-2 py-1 rounded-full bg-gray-100 text-gray-500 text-[10px] font-semibold">Inactive</span>';

    $list.append(
      '<div class="rounded-xl border border-parish-100 bg-white p-4">' +
        '<div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3">' +
          '<div class="min-w-0">' +
            '<div class="flex items-center gap-2 flex-wrap">' +
              '<div class="font-semibold text-gray-800">' + escapeHtml(r.rule_name) + '</div>' + status +
            '</div>' +
            '<div class="text-xs text-gray-500 mt-1">' + escapeHtml(weeks + ' ' + weekdayLabel(r.day_of_week)) + ' · ' + escapeHtml(formatTime(r.start_time)) + '</div>' +
            '<div class="mt-2 flex flex-wrap gap-2 text-[11px]">' +
              '<span class="px-2 py-1 rounded-full bg-gold-50 text-gold-700">Fee: ₱' + Number(r.fee_amount || 0).toLocaleString('en-PH',{minimumFractionDigits:2}) + '</span>' +
              '<span class="px-2 py-1 rounded-full bg-gray-50 text-gray-500">Capacity: ' + escapeHtml(r.capacity) + '</span>' +
            '</div>' +
          '</div>' +
          '<div class="flex gap-2">' +
            '<button type="button" class="text-xs font-semibold text-parish-700 hover:underline" onclick="openRuleEditor(' + parseInt(r.id,10) + ')">Edit</button>' +
            '<button type="button" class="text-xs font-semibold text-red-500 hover:underline" onclick="deleteRule(' + parseInt(r.id,10) + ')">Delete</button>' +
          '</div>' +
        '</div>' +
      '</div>'
    );
  });
}

function toggleSpecialSettings(){
  var enabled = $('#f-special-enabled').is(':checked');
  $('#special-settings').toggleClass('opacity-40 pointer-events-none', !enabled);
}

function editService(id){
  $.get('<?= site_url($service_config_base . '/get/') ?>' + id, function(res){
    if(!res.success){ toastr.error(res.message || 'Unable to load service.'); return; }
    var d = res.data;
    currentService = d;
    currentRules = d.schedule_rules || [];

    $('#f-id').val(d.id);
    $('#svc-title').text('Configure ' + d.name);
    $('#f-name').val(d.name);
    $('#f-desc').val(d.description);
    $('#f-fee').val(d.base_fee);
    $('#f-duration').val(d.duration_minutes || 60);
    $('#f-buffer-before').val(d.booking_buffer_before_minutes || 0);
    $('#f-buffer').val(d.booking_buffer_minutes || 0);
    $('#f-min-days').val(d.min_advance_days || 0);
    $('#f-max-days').val(d.max_advance_days || 365);
    $('#f-main-church').prop('checked', parseInt(d.uses_main_church,10) === 1);
    $('#f-requires-priest').prop('checked', parseInt(d.requires_priest == null ? 1 : d.requires_priest,10) === 1);
    $('#f-workflow').prop('checked', parseInt(d.requires_approval_workflow,10) === 1);
    $('#f-active').prop('checked', parseInt(d.is_active,10) === 1);

    $('#f-special-enabled').prop('checked', parseInt(d.allow_special_booking,10) === 1);
    $('#f-special-fee').val(d.special_fee || 0);
    $('#f-special-start').val(d.special_start_time ? String(d.special_start_time).slice(0,5) : '');
    $('#f-special-end').val(d.special_end_time ? String(d.special_end_time).slice(0,5) : '');
    $('#f-slot-interval').val(String(d.slot_interval_minutes || 60));
    toggleSpecialSettings();

    var $list = $('#requirements-list').empty();
    (d.requirements || []).forEach(function(r){
      $list.append(
        '<div class="flex items-center gap-3 rounded-xl border border-gray-100 px-3 py-2.5">' +
          '<i class="ph ph-paperclip text-gray-400"></i>' +
          '<span class="flex-1 text-sm text-gray-600">' + escapeHtml(r.label) + (parseInt(r.is_required,10) === 1 ? ' <span class="text-red-500">*</span>' : '') + '</span>' +
          '<button type="button" onclick="removeRequirement(' + parseInt(r.id,10) + ', this)" class="text-red-500 text-xs hover:underline">Remove</button>' +
        '</div>'
      );
    });

    renderRules();
    $('#svc-modal').removeClass('hidden').addClass('flex');
  });
}

function openRuleEditor(id){
  if(!currentService) return;
  var rule = id ? currentRules.find(function(r){ return parseInt(r.id,10) === parseInt(id,10); }) : null;

  $('#rule-form')[0].reset();
  $('#r-id').val(rule ? rule.id : '');
  $('#r-service-id').val(currentService.id);
  $('#rule-title').text(rule ? 'Edit Schedule Rule' : 'Add Schedule Rule');

  if(rule){
    $('#r-name').val(rule.rule_name);
    $('#r-day').val(String(rule.day_of_week));
    $('#r-weeks').val(rule.week_numbers);
    $('#r-time').val(rule.start_time ? String(rule.start_time).slice(0,5) : '');
    $('#r-fee').val(rule.fee_amount || 0);
    $('#r-capacity').val(rule.capacity || 1);
    $('#r-valid-from').val(rule.valid_from || '');
    $('#r-valid-until').val(rule.valid_until || '');
    $('#r-order').val(rule.display_order || 0);
    $('#r-active').prop('checked', parseInt(rule.is_active,10) === 1);
  } else {
    $('#r-fee').val(0);
    $('#r-capacity').val(1);
    $('#r-order').val(0);
    $('#r-active').prop('checked', true);
  }

  $('#rule-modal').removeClass('hidden').addClass('flex');
}

function addRequirement(){
  Swal.fire({
    title: 'New Requirement',
    input: 'text',
    inputPlaceholder: 'e.g. Birth Certificate (PSA)',
    showCancelButton: true,
    confirmButtonColor: '#235a38'
  }).then(function(r){
    if(r.isConfirmed && r.value){
      $.post('<?= site_url($service_config_base . '/add-requirement') ?>', {
        service_type_id: $('#f-id').val(),
        label: r.value,
        is_required: 1
      }, function(res){
        if(res.success){ toastr.success(res.message); editService($('#f-id').val()); }
        else toastr.error(res.message || 'Unable to add requirement.');
      });
    }
  });
}

function removeRequirement(id, btn){
  Swal.fire({
    icon:'warning',
    title:'Remove this requirement?',
    showCancelButton:true,
    confirmButtonColor:'#b42318',
    confirmButtonText:'Remove'
  }).then(function(r){
    if(r.isConfirmed){
      $.post('<?= site_url($service_config_base . '/delete-requirement/') ?>' + id, function(res){
        if(res.success){ $(btn).closest('div').remove(); toastr.success('Requirement removed.'); }
      });
    }
  });
}

function deleteRule(id){
  Swal.fire({
    icon:'warning',
    title:'Delete this schedule rule?',
    text:'Future regular availability generated from this rule will disappear.',
    showCancelButton:true,
    confirmButtonColor:'#b42318',
    confirmButtonText:'Delete'
  }).then(function(result){
    if(!result.isConfirmed) return;
    $.post('<?= site_url($service_config_base . '/delete-schedule-rule/') ?>' + id, function(res){
      if(res.success){ toastr.success(res.message); editService($('#f-id').val()); }
      else toastr.error(res.message || 'Unable to delete schedule.');
    });
  });
}

$('#f-special-enabled').on('change', toggleSpecialSettings);

$('#rule-form').on('submit', function(e){
  e.preventDefault();
  $.post('<?= site_url($service_config_base . '/save-schedule-rule') ?>', $(this).serialize(), function(res){
    if(res.success){
      toastr.success(res.message);
      closeRuleModal();
      editService($('#f-id').val());
    } else {
      Swal.fire({icon:'error', title:'Could not save schedule', text:res.message, confirmButtonColor:'#235a38'});
    }
  });
});

$('#svc-form').on('submit', function(e){
  e.preventDefault();

  var $btn = $('#save-service-settings');
  var originalHtml = $btn.html();
  $btn.prop('disabled', true).addClass('opacity-60 cursor-not-allowed')
      .html('<i class="ph ph-spinner-gap animate-spin mr-1"></i> Saving…');

  $.post('<?= site_url($service_config_base . '/store') ?>', $(this).serialize(), function(res){
    if(res.success){
      if(res.data){
        $('#f-buffer-before').val(res.data.booking_buffer_before_minutes);
        $('#f-duration').val(res.data.duration_minutes);
        $('#f-buffer').val(res.data.booking_buffer_minutes);
      }

      toastr.success(
        (res.message || 'Service settings saved.') +
        (res.data ? ' Protected time: ' + res.data.booking_buffer_before_minutes + ' + ' + res.data.duration_minutes + ' + ' + res.data.booking_buffer_minutes + ' min.' : '')
      );
      setTimeout(function(){ location.reload(); }, 700);
    } else {
      $btn.prop('disabled', false).removeClass('opacity-60 cursor-not-allowed').html(originalHtml);
      Swal.fire({
        icon:'error',
        title:'Could not save service',
        text:res.message || 'Please try again.',
        confirmButtonColor:'#235a38'
      });
    }
  }).fail(function(xhr){
    $btn.prop('disabled', false).removeClass('opacity-60 cursor-not-allowed').html(originalHtml);
    Swal.fire({
      icon:'error',
      title:'Could not save service',
      text:'The server did not accept the update. Please check the database migration and try again.',
      confirmButtonColor:'#235a38'
    });
  });
});

$(function(){
  document.querySelector('#svc-modal .relative').addEventListener('click', function(e){ e.stopPropagation(); });
  document.querySelector('#rule-modal .relative').addEventListener('click', function(e){ e.stopPropagation(); });
});
</script>
