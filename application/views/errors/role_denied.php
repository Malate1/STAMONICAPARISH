<?php
$allowed_text = !empty($allowed_role_labels) ? implode(' / ', $allowed_role_labels) : 'another account type';
?>
<section class="min-h-[68vh] flex items-center bg-stonewarm-50 border-y border-stonewarm-200">
  <div class="max-w-4xl mx-auto w-full px-4 sm:px-6 lg:px-8 py-16 sm:py-20">
    <div class="rounded-[2rem] bg-white border border-stonewarm-200 shadow-soft overflow-hidden">
      <div class="grid lg:grid-cols-[.42fr_.58fr]">
        <div class="bg-parish-900 text-white p-8 sm:p-10 flex flex-col justify-between">
          <div>
            <div class="w-14 h-14 rounded-2xl bg-white/10 border border-white/10 flex items-center justify-center text-gold-200 text-2xl">
              <i class="ph ph-shield-check"></i>
            </div>
            <div class="heritage-kicker text-gold-300 mt-7">Account restriction</div>
            <h1 class="text-3xl sm:text-4xl font-bold mt-2">This page belongs to a different portal.</h1>
          </div>

          <div class="mt-10 rounded-2xl bg-white/8 border border-white/10 p-4">
            <div class="text-[10px] uppercase tracking-[.14em] text-white/45 font-bold">Signed in as</div>
            <div class="font-semibold mt-1"><?= html_escape($current_role_label ?? 'User') ?></div>
          </div>
        </div>

        <div class="p-8 sm:p-10 lg:p-12">
          <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-amber-50 text-amber-700 border border-amber-100 text-xs font-semibold">
            <i class="ph ph-info"></i> No account problem
          </div>

          <h2 class="text-2xl sm:text-3xl font-bold text-parish-900 mt-5">You are signed in correctly, but this action is not available for your role.</h2>
          <p class="text-gray-600 leading-relaxed mt-4">
            This page is intended for <strong><?= html_escape($allowed_text) ?></strong> accounts.
            Your <strong><?= html_escape($current_role_label ?? 'current') ?></strong> account has its own portal and permissions.
          </p>

          <div class="mt-6 rounded-2xl border border-parish-100 bg-parish-50/60 p-5">
            <div class="flex gap-3">
              <i class="ph ph-lock-key text-parish-700 text-xl mt-0.5"></i>
              <div>
                <div class="font-semibold text-gray-800">Why you are seeing this</div>
                <p class="text-sm text-gray-500 mt-1 leading-relaxed">Parishioner booking, certificate, payment and donation workflows are kept separate from administrator, secretary and priest accounts to avoid accidental submissions under staff accounts.</p>
              </div>
            </div>
          </div>

          <div class="mt-8 flex flex-col sm:flex-row gap-3">
            <a href="<?= html_escape($account_home_url ?? site_url('/')) ?>" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-parish-800 hover:bg-parish-900 text-white font-semibold">
              <i class="ph ph-squares-four"></i> Go to My Portal
            </a>
            <a href="<?= site_url('/') ?>" class="inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl border border-stonewarm-200 bg-white hover:bg-stonewarm-50 text-gray-700 font-semibold">
              <i class="ph ph-house"></i> Return to Public Site
            </a>
          </div>
        </div>
      </div>
    </div>
  </div>
</section>
