<li>
    @if(!empty($module['children']))
        <span class="toggle">[+]</span>
    @else
        <span style="display:inline-block;width:20px;"></span>
    @endif

    <input type="checkbox" name="privileges[]" value="{{ $module['id'] }}" id="module-{{ $module['id'] }}"
        @if(in_array($module['id'], $assigned)) checked @endif>

    <label for="module-{{ $module['id'] }}" style="font-size:16px;">{{ $module['module_name'] }}</label>

    @if(!empty($module['children']))
        <ul class="hidden">
            @foreach($module['children'] as $child)
                @include('admin-module.manageUser.privilege_module', ['module'=>$child, 'assigned'=>$assigned])
            @endforeach
        </ul>
    @endif
</li>
