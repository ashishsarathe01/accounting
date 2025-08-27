<li>
    @if (!empty($module['children']))
        <span class="toggle">[+]</span>
    @else
        <span style="display:inline-block; width: 20px;"></span>
    @endif
    @php $assign_privilege = App\Models\PrivilegesModuleMapping::select('module_id')->where('employee_id', $employee_id)->where('company_id',$company_id)->pluck('module_id') @endphp
    <input type="checkbox" name="privileges[]" value="{{ $module['id'] }}" id="module-{{ $module['id'] }}" @if(in_array($module['id'],$assign_privilege->toArray())) checked @endif>
    <label for="module-{{ $module['id'] }}" style="font-size: 19px;">{{ $module['module_name'] }}</label>

    @if (!empty($module['children']))
        <ul class="hidden">
            @foreach ($module['children'] as $child)
                @include('privilege_module', ['module' => $child])
            @endforeach
        </ul>
    @endif
</li>