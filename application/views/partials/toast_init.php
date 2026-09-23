<script>
  toastr.options = {
    closeButton: true,
    progressBar: true,
    positionClass: 'toast-top-right',
    timeOut: 4500,
    newestOnTop: true,
  };
  <?php if ($this->session->flashdata('toastr_success')): ?>
    toastr.success(<?= json_encode($this->session->flashdata('toastr_success')) ?>);
  <?php endif; ?>
  <?php if ($this->session->flashdata('toastr_error')): ?>
    toastr.error(<?= json_encode($this->session->flashdata('toastr_error')) ?>);
  <?php endif; ?>
  <?php if ($this->session->flashdata('toastr_info')): ?>
    toastr.info(<?= json_encode($this->session->flashdata('toastr_info')) ?>);
  <?php endif; ?>
</script>
