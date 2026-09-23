<section class="min-h-[calc(100vh-4rem)] flex items-center justify-center bg-parish-50/40 px-4 py-14">
  <div class="w-full max-w-md bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
    <div class="text-center mb-8">
      <div class="w-12 h-12 rounded-full bg-parish-700 text-white flex items-center justify-center font-semibold mx-auto">SM</div>
      <h1 class="text-xl font-semibold text-gray-900 mt-4">Welcome Back</h1>
      <p class="text-sm text-gray-400 mt-1">Log in to your Sta. Monica Parish Connect account</p>
    </div>

    <form id="login-form" class="space-y-4" x-data="{ submitting: false }">
      <div>
        <label class="text-xs font-medium text-gray-500">Email Address</label>
        <input required type="email" name="email" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:ring-2 focus:ring-parish-300 outline-none" placeholder="you@example.com">
      </div>
      <div>
        <div class="flex items-center justify-between">
          <label class="text-xs font-medium text-gray-500">Password</label>
          <a href="<?= site_url('forgot-password') ?>" class="text-xs text-parish-700 hover:underline">Forgot password?</a>
        </div>
        <input required type="password" name="password" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:ring-2 focus:ring-parish-300 outline-none" placeholder="••••••••">
      </div>
      <button type="submit" :disabled="submitting" class="w-full py-2.5 rounded-lg bg-parish-700 hover:bg-parish-800 text-white text-sm font-medium disabled:opacity-60">
        <span x-show="!submitting">Log In</span>
        <span x-show="submitting" x-cloak>Logging in…</span>
      </button>
    </form>

    <p class="text-center text-sm text-gray-400 mt-6">Don't have an account? <a href="<?= site_url('register') ?>" class="text-parish-700 font-medium hover:underline">Sign up</a></p>

    <div class="mt-6 pt-6 border-t border-gray-50 text-center">
      <p class="text-xs text-gray-400">Parish staff (Admin / Secretary / Priest) use the same login.</p>
    </div>
  </div>
</section>

<script>
$('#login-form').on('submit', function(e){
  e.preventDefault();
  var $form = $(this);
  $.post('<?= site_url('login') ?>', $form.serialize())
    .done(function(res){
      if(res.success){
        toastr.success('Welcome back!');
        window.location.href = res.redirect;
      } else {
        Swal.fire({ icon: 'error', title: 'Login Failed', text: res.message, confirmButtonColor: '#235a38' });
      }
    })
    .fail(function(){
      Swal.fire({ icon: 'error', title: 'Something went wrong', text: 'Please try again.', confirmButtonColor: '#235a38' });
    });
});
</script>
