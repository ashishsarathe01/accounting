@extends('admin-module.layouts.app')
@section('content')
@include('admin-module.layouts.header')

<div class="list-of-view-company">
    <section class="list-of-view-company-section container-fluid">
        <div class="row vh-100">
            @include('admin-module.layouts.leftnav')

            <div class="col-md-12 ml-sm-auto col-lg-10 px-md-4 bg-mint">
                
                @if(session('success'))
                    <div class="alert alert-success mt-3">{{ session('success') }}</div>
                @endif

                <h5 class="table-title-bottom-line px-4 py-3 m-0 bg-plum-viloet position-relative title-border-redius border-divider shadow-sm">
                    Set Privileges for: {{ $user->name }}
                </h5>

                <!-- Box container for privileges -->
                <div class="bg-white px-4 py-3 border-divider rounded-bottom-8 shadow-sm mt-0">
                    <form method="POST" action="{{ route('admin.manageUser.setUserPrivileges') }}">
                        @csrf
                        <input type="hidden" name="user_id" value="{{ $user->id }}">

                        <ul class="privilege-tree">
                            @foreach($privileges as $module)
                                @include('admin-module.manageUser.privilege_module', ['module'=>$module, 'assigned'=>$assigned])
                            @endforeach
                        </ul>

                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-primary">Save Privileges</button>
                        </div>
                    </form>
                </div>
                <!-- End box container -->

            </div>
        </div>
    </section>
</div>

@include('layouts.footer')

<style>
ul { list-style:none; padding-left:20px; }
.toggle { cursor:pointer; font-weight:bold; margin-right:5px; font-size: 18px; }
.hidden { display:none; }

/* Adjust privilege tree font size and checkbox */
.privilege-tree input[type="checkbox"] { 
    transform: scale(1.3); /* slightly larger checkbox */
    margin-right:8px; 
}

.privilege-tree label { 
    font-size: 18px; /* slightly larger text */
    font-weight: 500;
}
</style>

<script>
document.querySelectorAll('.toggle').forEach(function(btn) {
    btn.addEventListener('click', function () {
        const ul = this.closest('li').querySelector('ul');
        if (ul) {
            ul.classList.toggle('hidden');
            this.textContent = ul.classList.contains('hidden') ? '[+]' : '[-]';
        }
    });
});

// Correct parent-child checkbox logic
document.querySelectorAll('input[type="checkbox"]').forEach(function(chk) {
    chk.addEventListener('change', function() {
        const li = this.closest('li');

        // Check/uncheck all children
        const children = li.querySelectorAll('ul input[type="checkbox"]');
        children.forEach(c => c.checked = this.checked);

        // Update parent checkboxes recursively
        function updateParent(el) {
            const parentLi = el.closest('ul')?.closest('li');
            if (parentLi) {
                const parentChk = parentLi.querySelector('input[type="checkbox"]');
                const siblings = Array.from(el.closest('ul').querySelectorAll('input[type="checkbox"]'));
                // Parent is checked only if all children are checked
                parentChk.checked = siblings.every(s => s.checked);
                updateParent(parentLi);
            }
        }
        updateParent(this);
    });
});
</script>
@endsection
