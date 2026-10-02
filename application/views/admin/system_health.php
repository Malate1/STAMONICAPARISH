<div class="max-w-7xl mx-auto">
  <div class="flex flex-col lg:flex-row lg:items-end lg:justify-between gap-4 mb-6">
    <div>
      <div class="text-xs uppercase tracking-[.16em] text-gold-600 font-semibold">Administration</div>
      <h1 class="text-2xl sm:text-3xl font-semibold text-gray-900 mt-1">System Health</h1>
      <p class="text-sm text-gray-500 mt-1 max-w-3xl">Read-only deployment checks for security settings, writable storage and required database migrations. This page never runs migrations automatically.</p>
    </div>
    <div class="flex gap-2">
      <span class="inline-flex items-center gap-2 px-3 py-2 rounded-xl border border-gray-200 bg-white text-xs text-gray-600"><i class="ph ph-code"></i>PHP <?= html_escape($php_version) ?></span>
      <span class="inline-flex items-center gap-2 px-3 py-2 rounded-xl border border-gray-200 bg-white text-xs text-gray-600"><i class="ph ph-server"></i><?= html_escape(ucfirst($environment)) ?></span>
    </div>
  </div>

  <?php $all_ok = $passed === $total && $migration_ready === count($migrations); ?>
  <div class="rounded-2xl border <?= $all_ok ? 'border-emerald-100 bg-emerald-50/70' : 'border-amber-100 bg-amber-50/70' ?> p-5 sm:p-6 mb-6">
    <div class="flex flex-col sm:flex-row sm:items-center gap-4">
      <div class="w-12 h-12 rounded-2xl bg-white flex items-center justify-center text-2xl <?= $all_ok ? 'text-emerald-700' : 'text-amber-700' ?>"><i class="ph <?= $all_ok ? 'ph-check-circle' : 'ph-warning-circle' ?>"></i></div>
      <div class="flex-1">
        <h2 class="font-semibold <?= $all_ok ? 'text-emerald-900' : 'text-amber-900' ?>"><?= $all_ok ? 'Core deployment checks are healthy' : 'Some deployment checks need attention' ?></h2>
        <p class="text-xs <?= $all_ok ? 'text-emerald-800/70' : 'text-amber-800/70' ?> mt-1"><?= $passed ?> of <?= $total ?> runtime/security checks passed · <?= $migration_ready ?> of <?= count($migrations) ?> current security/workflow migrations detected.</p>
      </div>
      <button type="button" onclick="location.reload()" class="px-4 py-2.5 rounded-xl bg-white border border-gray-200 text-gray-600 text-xs font-semibold hover:bg-gray-50"><i class="ph ph-arrows-clockwise mr-1"></i>Recheck</button>
    </div>
  </div>

  <div class="grid xl:grid-cols-[1.25fr_.75fr] gap-6">
    <section class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
      <div class="px-5 sm:px-6 py-4 border-b border-gray-100">
        <h2 class="font-semibold text-gray-800">Runtime &amp; Security Checks</h2>
        <p class="text-xs text-gray-400 mt-1">These checks reflect the currently connected environment.</p>
      </div>
      <div class="divide-y divide-gray-100">
        <?php foreach ($checks as $check): ?>
          <div class="px-5 sm:px-6 py-4 flex items-start gap-4">
            <div class="w-9 h-9 rounded-xl <?= $check['ok'] ? 'bg-emerald-50 text-emerald-700' : 'bg-red-50 text-red-600' ?> flex items-center justify-center flex-shrink-0"><i class="ph <?= $check['ok'] ? 'ph-check' : 'ph-x' ?>"></i></div>
            <div class="min-w-0 flex-1">
              <div class="font-medium text-sm text-gray-800"><?= html_escape($check['label']) ?></div>
              <div class="text-xs text-gray-400 mt-1 leading-relaxed"><?= html_escape($check['detail']) ?></div>
            </div>
            <span class="text-[10px] uppercase tracking-wide font-bold <?= $check['ok'] ? 'text-emerald-600' : 'text-red-500' ?>"><?= $check['ok'] ? 'Pass' : 'Attention' ?></span>
          </div>
        <?php endforeach; ?>
      </div>
    </section>

    <section class="bg-white rounded-2xl border border-gray-100 overflow-hidden">
      <div class="px-5 sm:px-6 py-4 border-b border-gray-100">
        <h2 class="font-semibold text-gray-800">Database Migration Status</h2>
        <p class="text-xs text-gray-400 mt-1">Run missing SQL files manually in phpMyAdmin.</p>
      </div>
      <div class="divide-y divide-gray-100">
        <?php foreach ($migrations as $migration): ?>
          <div class="p-5">
            <div class="flex items-start gap-3">
              <div class="w-9 h-9 rounded-xl <?= $migration['ready'] ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' ?> flex items-center justify-center flex-shrink-0"><i class="ph <?= $migration['ready'] ? 'ph-check-circle' : 'ph-database' ?>"></i></div>
              <div class="min-w-0">
                <div class="font-medium text-sm text-gray-800"><?= html_escape($migration['label']) ?></div>
                <div class="text-xs text-gray-400 mt-1"><?= html_escape($migration['detail']) ?></div>
                <div class="mt-2 text-[11px] font-mono break-all <?= $migration['ready'] ? 'text-emerald-600' : 'text-amber-700' ?>"><?= html_escape($migration['file']) ?></div>
              </div>
            </div>
          </div>
        <?php endforeach; ?>
      </div>
      <div class="p-5 bg-gray-50/70 border-t border-gray-100 text-xs text-gray-500 leading-relaxed"><strong>Existing production database:</strong> run only the individual migration SQL files shown as missing. Do not import <code>database/schema.sql</code> over an existing parish database.</div>
    </section>
  </div>
</div>
