@if(isset($menuItems) && $menuItems->isNotEmpty())
    <ul class="flex space-x-8 overflow-x-auto pb-1 hide-scrollbar">
        @foreach($menuItems as $item)
            @php
                $itemPath = trim(parse_url($item->link, PHP_URL_PATH) ?? '', '/');
                $isActive = $item->route
                    ? request()->routeIs($item->route)
                    : ($itemPath === '' ? request()->path() === '/' : request()->is($itemPath));
            @endphp
            <li class="{{ $item->wrapper_class }}">
                <a
                    href="{{ $item->link }}"
                    target="{{ $item->target }}"
                    class="{{ trim(($item->link_class ? $item->link_class . ' ' : '') . 'text-gray-700 hover:text-blue-600 whitespace-nowrap' . ($isActive || $loop->first ? ' font-medium' : '')) }}"
                >
                    {{ $item->name }}
                </a>
            </li>
        @endforeach
    </ul>
@endif
