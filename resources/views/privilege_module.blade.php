<li>
    @if (!empty($module['children']))
        <span class="toggle">[+]</span>
    @else
        <span style="display:inline-block; width: 20px;"></span>
    @endif

    <input type="checkbox" name="privileges[]" value="{{ $module['id'] }}" id="module-{{ $module['id'] }}" @if(in_array($module['id'],$assign_privilege)) checked @endif>
    <label for="module-{{ $module['id'] }}">{{ $module['module_name'] }}</label>

    @if (!empty($module['children']))
        <ul class="hidden">
            @foreach ($module['children'] as $child)
                @include('privilege_module', ['module' => $child])
            @endforeach
        </ul>
    @endif
</li>