<?php
$role = (int) $current_user['role_id'];
$base = $role === ROLE_ADMIN ? 'admin' : ($role === ROLE_SECRETARY ? 'staff' : 'priest');
$seg2 = $this->uri->segment(2);

if (!function_exists('nav_item')) {
    function nav_item($url, $icon, $label, $active) {
        $cls = $active ? 'bg-white/10 text-white font-medium' : 'text-parish-200 hover:bg-white/5';
        return '<a href="' . $url . '" class="flex items-center gap-3 px-3 py-2.5 rounded-lg ' . $cls . '"><i class="ph ' . $icon . ' text-lg"></i> ' . $label . '</a>';
    }
}
?>
<aside class="w-64 bg-parish-900 text-parish-100 flex-shrink-0 hidden lg:flex flex-col">
  <div class="h-16 flex items-center gap-2.5 px-5 border-b border-white/10">
    <div class="w-9 h-9 rounded-xl bg-white/10 overflow-hidden flex-shrink-0">
      <img src="<?= base_url('favicon.svg') ?>" alt="Sta. Monica Parish Church logo" class="w-full h-full object-cover">
    </div>
    <div class="leading-tight">
      <div class="font-semibold text-white text-sm">Sta. Monica Parish</div>
      <div class="text-[11px] text-gold-400 -mt-0.5"><?= html_escape(role_label($role)) ?></div>
    </div>
  </div>

  <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1 text-sm">
    <?= nav_item(site_url($base . '/dashboard'), 'ph-squares-four', 'Dashboard', $seg2 === 'dashboard') ?>

    <?php if ($role === ROLE_ADMIN || $role === ROLE_SECRETARY): ?>
      <div class="pt-3 pb-1 px-3 text-[11px] uppercase tracking-wide text-parish-400">Services</div>
      <?= nav_item(site_url($base . '/booking'), 'ph-calendar-check', 'Bookings', $seg2 === 'booking') ?>
      <?= nav_item(site_url($base . '/certificate'), 'ph-scroll', 'Certificates', $seg2 === 'certificate') ?>
      <?= nav_item(site_url($base . '/payment'), 'ph-credit-card', 'Payments', $seg2 === 'payment') ?>
      <?= nav_item(site_url($base . '/record'), 'ph-archive', 'Sacramental Records', $seg2 === 'record') ?>
      <?php if ($role === ROLE_SECRETARY): ?>
        <?= nav_item(site_url('staff/service-config'), 'ph-calendar-dots', 'Service Availability', $seg2 === 'service-config') ?>
      <?php endif; ?>
    <?php endif; ?>

    <?php if ($role === ROLE_SECRETARY): ?>
      <?= nav_item(site_url('staff/mass_intention'), 'ph-hands-praying', 'Mass Intentions', $seg2 === 'mass_intention') ?>
      <?= nav_item(site_url('staff/walkin'), 'ph-user-plus', 'Walk-in Transaction', $seg2 === 'walkin') ?>
    <?php endif; ?>

    <?php if ($role === ROLE_PRIEST): ?>
      <div class="pt-3 pb-1 px-3 text-[11px] uppercase tracking-wide text-parish-400">My Ministry</div>
      <?= nav_item(site_url('priest/schedule'), 'ph-calendar-blank', 'My Schedule', $seg2 === 'schedule') ?>
    <?php endif; ?>

    <?php if ($role === ROLE_ADMIN): ?>
      <div class="pt-3 pb-1 px-3 text-[11px] uppercase tracking-wide text-parish-400">Content</div>
      <?= nav_item(site_url('admin/mass_schedule'), 'ph-clock', 'Mass Schedule', $seg2 === 'mass_schedule') ?>
      <?= nav_item(site_url('admin/announcement'), 'ph-megaphone', 'Announcements', $seg2 === 'announcement') ?>
      <?= nav_item(site_url('admin/event'), 'ph-calendar-star', 'Events', $seg2 === 'event') ?>
      <?= nav_item(site_url('admin/ministry'), 'ph-users-three', 'Ministries', $seg2 === 'ministry') ?>

      <div class="pt-3 pb-1 px-3 text-[11px] uppercase tracking-wide text-parish-400">Administration</div>
      <?= nav_item(site_url('admin/users'), 'ph-identification-badge', 'Accounts', $seg2 === 'users') ?>
      <?= nav_item(site_url('admin/service_type'), 'ph-sliders', 'Service Config', $seg2 === 'service_type') ?>
      <?= nav_item(site_url('admin/setting'), 'ph-gear', 'Settings', $seg2 === 'setting') ?>
    <?php endif; ?>

    <div class="pt-4 mt-4 border-t border-white/10">
      <?= nav_item(site_url('/'), 'ph-globe', 'Visit Public Site', false) ?>
      <?= nav_item(site_url('logout'), 'ph-sign-out', 'Logout', false) ?>
    </div>
  </nav>
</aside>

<div x-show="sidebarOpen" x-cloak class="fixed inset-0 z-50 lg:hidden">
  <div class="absolute inset-0 bg-black/50" @click="sidebarOpen=false"></div>
  <aside class="absolute left-0 top-0 bottom-0 w-72 bg-parish-900 text-parish-100 flex flex-col overflow-y-auto" @click.outside="sidebarOpen=false">
    <div class="h-16 flex items-center justify-between px-5 border-b border-white/10 flex-shrink-0">
      <div class="font-semibold text-white text-sm">Sta. Monica Parish</div>
      <button @click="sidebarOpen=false" class="text-parish-200"><i class="ph ph-x text-xl"></i></button>
    </div>
    <nav class="flex-1 py-4 px-3 space-y-1 text-sm">
      <?= nav_item(site_url($base . '/dashboard'), 'ph-squares-four', 'Dashboard', false) ?>
      <?php if ($role === ROLE_ADMIN || $role === ROLE_SECRETARY): ?>
        <?= nav_item(site_url($base . '/booking'), 'ph-calendar-check', 'Bookings', false) ?>
        <?= nav_item(site_url($base . '/certificate'), 'ph-scroll', 'Certificates', false) ?>
        <?= nav_item(site_url($base . '/payment'), 'ph-credit-card', 'Payments', false) ?>
        <?= nav_item(site_url($base . '/record'), 'ph-archive', 'Sacramental Records', false) ?>
        <?php if ($role === ROLE_SECRETARY): ?>
          <?= nav_item(site_url('staff/service-config'), 'ph-calendar-dots', 'Service Availability', false) ?>
        <?php endif; ?>
      <?php endif; ?>
      <?php if ($role === ROLE_PRIEST): ?>
        <?= nav_item(site_url('priest/schedule'), 'ph-calendar-blank', 'My Schedule', false) ?>
      <?php endif; ?>
      <?= nav_item(site_url('logout'), 'ph-sign-out', 'Logout', false) ?>
    </nav>
  </aside>
</div>
