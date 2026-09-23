<header class="h-16 bg-white border-b border-gray-100 flex items-center justify-between px-4 sm:px-6 flex-shrink-0">
  <button @click="sidebarOpen = true" class="lg:hidden p-2 -ml-2 rounded-lg hover:bg-gray-100">
    <i class="ph ph-list text-xl"></i>
  </button>
  <div class="hidden lg:block text-sm text-gray-400">
    <?= html_escape(role_label($current_user['role_id'])) ?> Portal
  </div>

  <div class="flex items-center gap-2" x-data="{ notifOpen: false }">
    <div class="relative">
      <button @click="notifOpen = !notifOpen; if(notifOpen) fetchNotifications()" class="relative p-2.5 rounded-full hover:bg-gray-100">
        <i class="ph ph-bell text-xl text-gray-600"></i>
        <span id="notif-badge" class="hidden absolute top-1.5 right-1.5 w-2 h-2 rounded-full bg-red-500"></span>
      </button>
      <div x-show="notifOpen" x-cloak @click.outside="notifOpen=false" x-transition
           class="absolute right-0 mt-2 w-80 bg-white rounded-xl shadow-lg border border-gray-100 overflow-hidden z-50">
        <div class="px-4 py-3 border-b border-gray-100 font-medium text-sm text-gray-700 flex justify-between items-center">
          Notifications
          <button onclick="markAllRead()" class="text-xs text-parish-700 hover:underline">Mark all read</button>
        </div>
        <div id="notif-list" class="max-h-80 overflow-y-auto divide-y divide-gray-50">
          <div class="px-4 py-6 text-center text-sm text-gray-400">Loading…</div>
        </div>
      </div>
    </div>

    <div class="w-9 h-9 rounded-full bg-gold-500 text-white flex items-center justify-center text-xs font-semibold">
      <?= html_escape(initials($current_user['first_name'] . ' ' . $current_user['last_name'])) ?>
    </div>
  </div>
</header>

<script>
function fetchNotifications(){
  $.get('<?= site_url('notifications/list') ?>', function(res){
    var $list = $('#notif-list');
    $list.empty();
    if(!res.data || res.data.length === 0){
      $list.append('<div class="px-4 py-6 text-center text-sm text-gray-400">No notifications yet.</div>');
      return;
    }
    res.data.forEach(function(n){
      $list.append(
        '<a href="'+ (n.link || '#') +'" class="block px-4 py-3 hover:bg-gray-50 '+(n.is_read == 0 ? 'bg-parish-50/50' : '')+'">' +
          '<div class="text-sm font-medium text-gray-800">'+ n.title +'</div>' +
          '<div class="text-xs text-gray-500 mt-0.5">'+ n.message +'</div>' +
        '</a>'
      );
    });
  });
}
function markAllRead(){
  $.post('<?= site_url('notifications/mark_all_read') ?>', function(){ fetchNotifications(); $('#notif-badge').addClass('hidden'); });
}
(function(){
  $.get('<?= site_url('notifications/unread_count') ?>', function(res){
    if(res.count > 0) $('#notif-badge').removeClass('hidden');
  });
})();
</script>
