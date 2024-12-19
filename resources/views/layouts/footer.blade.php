<script src="{{ URL::asset('public/assets/js/vendors/jquery-3.7.0.js')}}"></script>
<script src="{{ URL::asset('public/assets/js/vendors/jquery.dataTables.min.js')}}"></script>
<script src="{{ URL::asset('public/assets/js/vendors/dataTables.bootstrap5.min.js')}}"></script>
<script src="{{ URL::asset('public/assets/js/vendors/customclass.js')}}"></script>
<script src="{{ URL::asset('public/assets/js/vendors/custom.js')}}"></script>
<script src="{{ URL::asset('public/assets/js/vendors/select2.full.js')}}"></script>
<script src="{{ URL::asset('public/assets/js/vendors/jquery.validate.js')}}"></script>
<script type="text/javascript">
    $(document).ready(function() {
        $('.authInput').keyup(function(e) {
            if (this.value.length === this.maxLength) {
                let next = $(this).data('next');
                $('#n' + next).focus();
            }
        });
        $('#change_company').change(function() { 
            $('#change_company_frm').submit();
        });
    });
</script>