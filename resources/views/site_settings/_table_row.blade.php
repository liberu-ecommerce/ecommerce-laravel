<tr>
    <td>{{ $setting->name }}</td>
    <td>{{ $setting->value }}</td>
    <td>{{ $setting->description }}</td>
    <td>
        <a href="{{ route('site_settings.edit', $setting->id) }}" class="btn btn-primary">Edit</a>
    </td>
</tr>
