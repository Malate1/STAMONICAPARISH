<section class="min-h-[calc(100vh-4rem)] flex items-center justify-center bg-parish-50/40 px-4 py-14">
  <div class="w-full max-w-lg bg-white rounded-2xl shadow-sm border border-gray-100 p-8">
    <div class="text-center mb-8">
      <div class="w-12 h-12 rounded-full bg-parish-700 text-white flex items-center justify-center font-semibold mx-auto">SM</div>
      <h1 class="text-xl font-semibold text-gray-900 mt-4">Create Your Account</h1>
      <p class="text-sm text-gray-400 mt-1">Register to book services, request certificates, and more</p>
    </div>

    <form id="register-form" class="space-y-4">
      <div class="grid sm:grid-cols-2 gap-4">
        <div>
          <label class="text-xs font-medium text-gray-500">First Name</label>
          <input required name="first_name" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:ring-2 focus:ring-parish-300 outline-none">
        </div>
        <div>
          <label class="text-xs font-medium text-gray-500">Last Name</label>
          <input required name="last_name" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:ring-2 focus:ring-parish-300 outline-none">
        </div>
      </div>
      <div>
        <label class="text-xs font-medium text-gray-500">Email Address</label>
        <input required type="email" name="email" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:ring-2 focus:ring-parish-300 outline-none">
      </div>
      <div>
        <label class="text-xs font-medium text-gray-500">Mobile Number</label>
        <input required name="mobile_number" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:ring-2 focus:ring-parish-300 outline-none" placeholder="09XXXXXXXXX">
      </div>
      <div>
        <label class="text-xs font-medium text-gray-500">Address</label>
        <input name="address" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:ring-2 focus:ring-parish-300 outline-none">
      </div>
      <div class="grid sm:grid-cols-2 gap-4">
        <div>
          <label class="text-xs font-medium text-gray-500">Password</label>
          <input required type="password" name="password" minlength="8" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:ring-2 focus:ring-parish-300 outline-none">
        </div>
        <div>
          <label class="text-xs font-medium text-gray-500">Confirm Password</label>
          <input required type="password" name="password_confirm" minlength="8" class="mt-1 w-full px-4 py-2.5 rounded-lg border border-gray-200 text-sm focus:ring-2 focus:ring-parish-300 outline-none">
        </div>
      </div>
      <button type="submit" class="w-full py-2.5 rounded-lg bg-parish-700 hover:bg-parish-800 text-white text-sm font-medium">Create Account</button>
    </form>

    <p class="text-center text-sm text-gray-400 mt-6">Already have an account? <a href="<?= site_url('login') ?>" class="text-parish-700 font-medium hover:underline">Log in</a></p>
  </div>
</section>

<script>
$('#register-form').on('submit', function(e){
  e.preventDefault();
  $.post('<?= site_url('register') ?>', $(this).serialize())
    .done(function(res){
      if(res.success){
        toastr.success('Account created!');
        window.location.href = res.redirect;
      } else {
        Swal.fire({ icon: 'error', title: 'Registration Failed', text: res.message, confirmButtonColor: '#235a38' });
      }
    });
});
</script>
