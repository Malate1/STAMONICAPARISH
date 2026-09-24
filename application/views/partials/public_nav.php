<?php
$current_path = trim(uri_string(), '/');
$is_active = static function ($route) use ($current_path) {
    return $current_path === $route || strpos($current_path, $route . '/') === 0;
};

$primary_links = [
    'mass-schedule' => 'Mass Schedule',
    'sacraments' => 'Sacraments',
    'announcements' => 'Announcements',
    'contact' => 'Visit & Contact',
];

$secondary_links = [
    'events' => ['label' => 'Events', 'icon' => 'ph-calendar-star'],
    'projects' => ['label' => 'Projects', 'icon' => 'ph-folder-open'],
    'donate' => ['label' => 'Donate', 'icon' => 'ph-heart'],
    'ministries' => ['label' => 'Ministries', 'icon' => 'ph-users-three'],
    'priests' => ['label' => 'Our Priests', 'icon' => 'ph-church'],
    'prayers' => ['label' => 'Prayers & Novena', 'icon' => 'ph-hands-praying'],
    'st-monica' => ['label' => 'Life of St. Monica', 'icon' => 'ph-book-open-text'],
    'about' => ['label' => 'Heritage', 'icon' => 'ph-columns'],
];

$all_links = [
    'mass-schedule' => 'Mass Schedule',
    'sacraments' => 'Sacraments',
    'announcements' => 'Announcements',
    'events' => 'Events',
    'projects' => 'Projects',
    'donate' => 'Donate',
    'ministries' => 'Ministries',
    'priests' => 'Our Priests',
    'prayers' => 'Prayers & Novena',
    'st-monica' => 'Life of St. Monica',
    'about' => 'Heritage',
    'contact' => 'Visit & Contact',
];

$secondary_active = false;
foreach (array_keys($secondary_links) as $route) {
    if ($is_active($route)) {
        $secondary_active = true;
        break;
    }
}
?>

<!-- Slim utility bar: location + subtle account actions -->
<div class="hidden md:block bg-parish-900 text-white/75 text-xs">
  <div class="max-w-[1440px] mx-auto px-5 lg:px-8 h-9 flex items-center justify-between gap-6">
    <div class="flex items-center gap-5 min-w-0">
      <span class="inline-flex items-center gap-1.5 whitespace-nowrap">
        <i class="ph ph-map-pin text-gold-300"></i>
        Poblacion, Alburquerque, Bohol
      </span>
      <span class="hidden lg:inline-flex items-center gap-1.5 truncate">
        <i class="ph ph-church text-gold-300"></i>
        A living place of faith &amp; heritage since 1842
      </span>
    </div>

    <div class="flex items-center gap-1 flex-shrink-0">
      <a href="<?= site_url('contact') ?>" class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg hover:bg-white/10 hover:text-white transition">
        <i class="ph ph-compass"></i>
        Plan your visit
      </a>

      <span class="w-px h-4 bg-white/15 mx-1"></span>

      <?php if (!empty($current_user)): ?>
        <a href="<?= role_home_url($current_user['role_id']) ?>" class="inline-flex items-center gap-2 px-2.5 py-1.5 rounded-lg hover:bg-white/10 hover:text-white transition">
          <span class="w-5 h-5 rounded-full bg-gold-500 text-white flex items-center justify-center text-[9px] font-bold">
            <?= initials($current_user['first_name'] . ' ' . $current_user['last_name']) ?>
          </span>
          My Account
        </a>
      <?php else: ?>
        <a href="<?= site_url('login') ?>" class="inline-flex items-center gap-1.5 px-2.5 py-1.5 rounded-lg hover:bg-white/10 hover:text-white transition">
          <i class="ph ph-sign-in"></i>
          Log In
        </a>
        <a href="<?= site_url('register') ?>" class="inline-flex items-center gap-1.5 ml-1 px-3 py-1.5 rounded-lg border border-white/15 bg-white/5 text-white/90 hover:bg-white/10 hover:border-white/25 hover:text-white transition">
          <i class="ph ph-user-plus"></i>
          Create Account
        </a>
      <?php endif; ?>
    </div>
  </div>
</div>

<nav
  x-data="{ open: false, moreOpen: false }"
  class="sticky top-0 z-40 bg-white/95 backdrop-blur-xl border-b border-stonewarm-200/80 shadow-[0_8px_30px_rgba(23,59,41,.05)]"
>
  <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8">
    <div class="flex items-center justify-between gap-5 h-[72px]">
      <!-- Brand -->
      <a href="<?= site_url('/') ?>" class="flex items-center gap-3 min-w-0 flex-shrink-0">
        <div class="w-11 h-11 rounded-2xl bg-parish-800 ring-4 ring-parish-50 flex-shrink-0 overflow-hidden shadow-sm">
          <img src="<?= base_url('favicon.svg') ?>" alt="Sta. Monica Parish Church logo" class="w-full h-full object-cover">
        </div>
        <div class="leading-tight min-w-0">
          <div class="font-bold text-parish-900 text-[15px] truncate max-w-[230px]">Sta. Monica Parish Church</div>
          <div class="text-[10px] text-gold-600 tracking-[.16em] uppercase mt-0.5 whitespace-nowrap">Alburquerque · Bohol</div>
        </div>
      </a>

      <!-- Desktop navigation -->
      <div class="hidden xl:flex items-center justify-end gap-1 min-w-0">
        <?php foreach ($primary_links as $route => $label): ?>
          <a href="<?= site_url($route) ?>"
             class="px-3.5 py-2 text-sm font-medium rounded-full whitespace-nowrap transition <?= $is_active($route) ? 'bg-parish-50 text-parish-800' : 'text-gray-600 hover:text-parish-800 hover:bg-stonewarm-50' ?>">
            <?= $label ?>
          </a>
        <?php endforeach; ?>

        <!-- Full secondary nav only on very wide screens -->
        <div class="hidden min-[1750px]:flex items-center gap-1">
          <?php foreach ($secondary_links as $route => $meta): ?>
            <a href="<?= site_url($route) ?>"
               class="px-3.5 py-2 text-sm font-medium rounded-full whitespace-nowrap transition <?= $is_active($route) ? 'bg-parish-50 text-parish-800' : 'text-gray-600 hover:text-parish-800 hover:bg-stonewarm-50' ?>">
              <?= $meta['label'] ?>
            </a>
          <?php endforeach; ?>
        </div>

        <!-- Compact "More" dropdown for standard desktop widths -->
        <div class="relative min-[1750px]:hidden" @click.outside="moreOpen = false">
          <button
            type="button"
            @click="moreOpen = !moreOpen"
            :aria-expanded="moreOpen"
            class="inline-flex items-center gap-1.5 px-3.5 py-2 text-sm font-medium rounded-full whitespace-nowrap transition <?= $secondary_active ? 'bg-parish-50 text-parish-800' : 'text-gray-600 hover:text-parish-800 hover:bg-stonewarm-50' ?>"
          >
            More
            <i class="ph ph-caret-down text-xs transition-transform" :class="moreOpen ? 'rotate-180' : ''"></i>
          </button>

          <div
            x-show="moreOpen"
            x-cloak
            x-transition.origin.top.right
            class="absolute right-0 top-full mt-2 w-60 rounded-2xl bg-white border border-stonewarm-200 shadow-xl p-2"
          >
            <?php foreach ($secondary_links as $route => $meta): ?>
              <a href="<?= site_url($route) ?>"
                 @click="moreOpen = false"
                 class="flex items-center gap-3 px-3.5 py-3 rounded-xl text-sm transition <?= $is_active($route) ? 'bg-parish-50 text-parish-800 font-semibold' : 'text-gray-700 hover:bg-stonewarm-50 hover:text-parish-800' ?>">
                <span class="w-8 h-8 rounded-lg bg-parish-50 text-parish-700 flex items-center justify-center">
                  <i class="ph <?= $meta['icon'] ?>"></i>
                </span>
                <span><?= $meta['label'] ?></span>
                <i class="ph ph-arrow-right ml-auto text-gray-300"></i>
              </a>
            <?php endforeach; ?>
          </div>
        </div>
      </div>

      <!-- Mobile/tablet menu button -->
      <button
        @click="open = !open"
        :aria-expanded="open"
        aria-label="Open navigation menu"
        class="xl:hidden w-10 h-10 inline-flex items-center justify-center rounded-full bg-stonewarm-50 text-parish-900 hover:bg-parish-50 transition flex-shrink-0"
      >
        <i class="ph text-2xl" :class="open ? 'ph-x' : 'ph-list'"></i>
      </button>
    </div>
  </div>

  <!-- Mobile/tablet navigation -->
  <div x-show="open" x-cloak x-transition.opacity class="xl:hidden border-t border-stonewarm-200 bg-white shadow-lg">
    <div class="px-4 py-4">
      <div class="grid sm:grid-cols-2 gap-1">
        <?php foreach ($all_links as $route => $label): ?>
          <a
            @click="open = false"
            href="<?= site_url($route) ?>"
            class="flex items-center justify-between px-4 py-3 rounded-xl text-sm font-medium <?= $is_active($route) ? 'bg-parish-50 text-parish-800' : 'text-gray-700 hover:bg-stonewarm-50' ?>"
          >
            <?= $label ?>
            <i class="ph ph-arrow-right text-gray-300"></i>
          </a>
        <?php endforeach; ?>
      </div>

      <div class="pt-3 mt-3 border-t border-stonewarm-200 flex gap-2">
        <?php if (!empty($current_user)): ?>
          <a href="<?= role_home_url($current_user['role_id']) ?>" class="flex-1 text-center px-4 py-3 text-sm font-semibold text-white bg-parish-800 rounded-xl">
            My Account
          </a>
        <?php else: ?>
          <a href="<?= site_url('login') ?>" class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-3 text-sm font-semibold text-parish-800 border border-parish-200 rounded-xl">
            <i class="ph ph-sign-in"></i> Log In
          </a>
          <a href="<?= site_url('register') ?>" class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-3 text-sm font-semibold text-white bg-parish-800 rounded-xl">
            <i class="ph ph-user-plus"></i> Sign Up
          </a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>
