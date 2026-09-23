<?php
$current_path = trim(uri_string(), '/');
$is_active = static function ($route) use ($current_path) {
    return $current_path === $route || strpos($current_path, $route . '/') === 0;
};
?>
<div class="hidden md:block bg-parish-900 text-white/80 text-xs">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-9 flex items-center justify-between">
    <div class="flex items-center gap-5">
      <span class="inline-flex items-center gap-1.5"><i class="ph ph-map-pin"></i> Poblacion, Alburquerque, Bohol</span>
      <span class="inline-flex items-center gap-1.5"><i class="ph ph-church"></i> A living place of faith &amp; heritage since 1842</span>
    </div>
    <a href="<?= site_url('contact') ?>" class="inline-flex items-center gap-1.5 hover:text-white transition">
      Plan your visit <i class="ph ph-arrow-up-right"></i>
    </a>
  </div>
</div>

<nav x-data="{ open: false }" class="sticky top-0 z-40 bg-white/95 backdrop-blur-xl border-b border-stonewarm-200/80 shadow-[0_8px_30px_rgba(23,59,41,.05)]">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex justify-between items-center h-[72px]">
      <a href="<?= site_url('/') ?>" class="flex items-center gap-3 min-w-0">
        <div class="w-11 h-11 rounded-full bg-parish-800 text-white flex items-center justify-center font-bold text-sm ring-4 ring-parish-50 flex-shrink-0">SM</div>
        <div class="leading-tight min-w-0">
          <div class="font-bold text-parish-900 text-[15px] truncate">Sta. Monica Parish Church</div>
          <div class="text-[10px] text-gold-600 tracking-[.16em] uppercase mt-0.5">Alburquerque · Bohol</div>
        </div>
      </a>

      <div class="hidden xl:flex items-center gap-0.5">
        <?php
          $links = [
            'mass-schedule' => 'Mass Schedule',
            'sacraments' => 'Sacraments',
            'announcements' => 'Announcements',
            'events' => 'Events',
            'ministries' => 'Ministries',
            'about' => 'Heritage',
            'contact' => 'Visit & Contact',
          ];
          foreach ($links as $route => $label):
            $active = $is_active($route);
        ?>
        <a href="<?= site_url($route) ?>" class="px-3 py-2 text-sm font-medium rounded-full transition <?= $active ? 'bg-parish-50 text-parish-800' : 'text-gray-600 hover:text-parish-800 hover:bg-stonewarm-50' ?>">
          <?= $label ?>
        </a>
        <?php endforeach; ?>
      </div>

      <div class="hidden xl:flex items-center gap-2">
        <?php if (!empty($current_user)): ?>
          <a href="<?= role_home_url($current_user['role_id']) ?>" class="flex items-center gap-2 pl-2 pr-3 py-1.5 rounded-full border border-stonewarm-200 hover:border-parish-200 hover:bg-parish-50 transition">
            <div class="w-8 h-8 rounded-full bg-gold-500 text-white flex items-center justify-center text-xs font-semibold"><?= initials($current_user['first_name'] . ' ' . $current_user['last_name']) ?></div>
            <span class="text-sm font-medium text-gray-700"><?= html_escape($current_user['first_name']) ?></span>
          </a>
        <?php else: ?>
          <a href="<?= site_url('login') ?>" class="px-4 py-2 text-sm font-medium text-parish-800 hover:bg-parish-50 rounded-full transition">Log In</a>
          <a href="<?= site_url('register') ?>" class="px-5 py-2.5 text-sm font-semibold text-white bg-parish-800 hover:bg-parish-900 rounded-full shadow-sm transition">Create Account</a>
        <?php endif; ?>
      </div>

      <button @click="open = !open" :aria-expanded="open" aria-label="Open navigation menu" class="xl:hidden w-10 h-10 inline-flex items-center justify-center rounded-full bg-stonewarm-50 text-parish-900 hover:bg-parish-50 transition">
        <i class="ph text-2xl" :class="open ? 'ph-x' : 'ph-list'"></i>
      </button>
    </div>
  </div>

  <div x-show="open" x-cloak x-transition.opacity class="xl:hidden border-t border-stonewarm-200 bg-white">
    <div class="px-4 py-4 grid sm:grid-cols-2 gap-1">
      <?php foreach ($links as $route => $label): ?>
      <a @click="open = false" href="<?= site_url($route) ?>" class="flex items-center justify-between px-4 py-3 rounded-xl text-sm font-medium <?= $is_active($route) ? 'bg-parish-50 text-parish-800' : 'text-gray-700 hover:bg-stonewarm-50' ?>">
        <?= $label ?><i class="ph ph-arrow-right text-gray-300"></i>
      </a>
      <?php endforeach; ?>
      <div class="sm:col-span-2 pt-3 mt-2 border-t border-stonewarm-200 flex gap-2">
        <?php if (!empty($current_user)): ?>
          <a href="<?= role_home_url($current_user['role_id']) ?>" class="flex-1 text-center px-4 py-3 text-sm font-semibold text-white bg-parish-800 rounded-xl">My Account</a>
        <?php else: ?>
          <a href="<?= site_url('login') ?>" class="flex-1 text-center px-4 py-3 text-sm font-semibold text-parish-800 border border-parish-200 rounded-xl">Log In</a>
          <a href="<?= site_url('register') ?>" class="flex-1 text-center px-4 py-3 text-sm font-semibold text-white bg-parish-800 rounded-xl">Sign Up</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>
