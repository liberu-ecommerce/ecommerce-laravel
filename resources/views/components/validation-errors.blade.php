@if ($errors->any())
    {{-- role=alert + a full border, not a side stripe. "Whoops! Something went wrong."
         told the user nothing they couldn't see; the errors themselves are the message. --}}
    <div {{ $attributes->merge(['class' => 'rounded-lg border border-danger-600 bg-danger-50 px-4 py-3']) }} role="alert">
        <p class="text-label font-medium text-danger-700">
            {{ trans_choice('Please fix the field below.|Please fix the :count fields below.', $errors->count(), ['count' => $errors->count()]) }}
        </p>

        <ul class="mt-2 list-inside list-disc text-label text-danger-700">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif
