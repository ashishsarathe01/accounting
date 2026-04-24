<li>

    {{-- Toggle --}}
    @if (!empty($module['children']))
        <span class="toggle">[+]</span>
    @else
        <span style="display:inline-block; width:20px;"></span>
    @endif

    {{-- Checkbox --}}
    <input type="checkbox"
           id="module-{{ $module['id'] }}"
           class="module-checkbox"
           name="privileges[]"
           value="{{ $module['id'] }}"
           @if(in_array($module['id'], $assigned)) checked @endif>

    {{-- Label --}}
    <label for="module-{{ $module['id'] }}"
           style="font-size:19px; cursor:pointer;">
        {{ $module['module_name'] }}
    </label>

    {{-- Children --}}
    @if (!empty($module['children']))
        <ul class="hidden">
            @foreach ($module['children'] as $child)
                @include('admin-module.merchant.merchant_privilege_module', [
                    'module' => $child,
                    'assigned' => $assigned
                ])
            @endforeach
        </ul>
    @endif

</li>
