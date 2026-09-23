<aside class="w-64 bg-parish-900 text-parish-100 flex-shrink-0 hidden lg:flex flex-col">
  <div class="h-16 flex items-center gap-2.5 px-5 border-b border-white/10">
    <div class="w-9 h-9 rounded-full bg-white/10 text-white flex items-center justify-center font-semibold text-sm">SM</div>
    <div class="leading-tight">
      <div class="font-semibold text-white text-sm">Sta. Monica Parish</div>
      <div class="text-[11px] text-gold-400 -mt-0.5">CONNECT</div>
    </div>
  </div>

  <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1 text-sm">
    <?php $u = $this->uri->segment(2); ?>
    <a href="<?= site_url('my/dashboard') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg <?= $u === 'dashboard' ? 'bg-white/10 text-white font-medium' : 'text-parish-200 hover:bg-white/5' ?>"><i class="ph ph-squares-four text-lg"></i> Dashboard</a>
    <a href="<?= site_url('my/bookings') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg <?= $u === 'bookings' ? 'bg-white/10 text-white font-medium' : 'text-parish-200 hover:bg-white/5' ?>"><i class="ph ph-calendar-check text-lg"></i> My Bookings</a>
    <a href="<?= site_url('my/certificates') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg <?= $u === 'certificates' ? 'bg-white/10 text-white font-medium' : 'text-parish-200 hover:bg-white/5' ?>"><i class="ph ph-scroll text-lg"></i> Certificates</a>
    <a href="<?= site_url('my/mass-intentions') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg <?= $u === 'mass-intentions' ? 'bg-white/10 text-white font-medium' : 'text-parish-200 hover:bg-white/5' ?>"><i class="ph ph-candelabra text-lg"></i> Mass Intentions</a>
    <a href="<?= site_url('my/payments') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg <?= $u === 'payments' ? 'bg-white/10 text-white font-medium' : 'text-parish-200 hover:bg-white/5' ?>"><i class="ph ph-credit-card text-lg"></i> Payments</a>
    <a href="<?= site_url('my/profile') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg <?= $u === 'profile' ? 'bg-white/10 text-white font-medium' : 'text-parish-200 hover:bg-white/5' ?>"><i class="ph ph-user-circle text-lg"></i> My Profile</a>

    <div class="pt-4 mt-4 border-t border-white/10">
      <a href="<?= site_url('/') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-parish-200 hover:bg-white/5"><i class="ph ph-globe text-lg"></i> Visit Public Site</a>
      <a href="<?= site_url('logout') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-parish-200 hover:bg-white/5"><i class="ph ph-sign-out text-lg"></i> Logout</a>
    </div>
  </nav>
</aside>

<!-- Mobile sidebar -->
<div x-show="sidebarOpen" x-cloak class="fixed inset-0 z-50 lg:hidden">
  <div class="absolute inset-0 bg-black/50" @click="sidebarOpen=false"></div>
  <aside class="absolute left-0 top-0 bottom-0 w-72 bg-parish-900 text-parish-100 flex flex-col" @click.outside="sidebarOpen=false">
    <div class="h-16 flex items-center justify-between px-5 border-b border-white/10">
      <div class="font-semibold text-white text-sm">Sta. Monica Parish</div>
      <button @click="sidebarOpen=false" class="text-parish-200"><i class="ph ph-x text-xl"></i></button>
    </div>
    <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1 text-sm">
      <a href="<?= site_url('my/dashboard') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-parish-200 hover:bg-white/5"><i class="ph ph-squares-four text-lg"></i> Dashboard</a>
      <a href="<?= site_url('my/bookings') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-parish-200 hover:bg-white/5"><i class="ph ph-calendar-check text-lg"></i> My Bookings</a>
      <a href="<?= site_url('my/certificates') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-parish-200 hover:bg-white/5"><i class="ph ph-scroll text-lg"></i> Certificates</a>
      <a href="<?= site_url('my/mass-intentions') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-parish-200 hover:bg-white/5"><i class="ph ph-candelabra text-lg"></i> Mass Intentions</a>
      <a href="<?= site_url('my/payments') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-parish-200 hover:bg-white/5"><i class="ph ph-credit-card text-lg"></i> Payments</a>
      <a href="<?= site_url('my/profile') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-parish-200 hover:bg-white/5"><i class="ph ph-user-circle text-lg"></i> My Profile</a>
      <a href="<?= site_url('logout') ?>" class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-parish-200 hover:bg-white/5"><i class="ph ph-sign-out text-lg"></i> Logout</a>
    </nav>
  </aside>
</div>
