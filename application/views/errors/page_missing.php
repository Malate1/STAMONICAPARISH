<section class="min-h-[calc(100vh-8rem)] flex items-center justify-center px-4 py-16 bg-parish-50/40">
  <div class="w-full max-w-2xl text-center">
    <div class="w-16 h-16 rounded-2xl bg-parish-800 text-white flex items-center justify-center text-3xl mx-auto shadow-sm">
      <i class="ph ph-compass"></i>
    </div>
    <div class="text-xs uppercase tracking-[.18em] text-gold-600 font-bold mt-6">404 · Page Not Found</div>
    <h1 class="text-3xl sm:text-4xl font-semibold text-parish-900 mt-2">We couldn’t find that page.</h1>
    <p class="text-sm sm:text-base text-gray-500 mt-4 max-w-xl mx-auto leading-relaxed">The page may have moved, the link may be outdated, or the address may have been typed incorrectly. You can return to the parish website or go back to your account portal.</p>

    <div class="mt-7 flex flex-col sm:flex-row justify-center gap-3">
      <a href="<?= site_url('/') ?>" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-parish-800 hover:bg-parish-900 text-white text-sm font-semibold">
        <i class="ph ph-church"></i> Public Website
      </a>
      <?php if (!empty($current_user)): ?>
        <a href="<?= role_home_url($current_user['role_id']) ?>" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl border border-gray-200 bg-white text-gray-700 text-sm font-semibold hover:bg-gray-50">
          <i class="ph ph-squares-four"></i> My Portal
        </a>
      <?php else: ?>
        <a href="<?= site_url('login') ?>" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl border border-gray-200 bg-white text-gray-700 text-sm font-semibold hover:bg-gray-50">
          <i class="ph ph-sign-in"></i> Log In
        </a>
      <?php endif; ?>
    </div>
  </div>
</section>
