{{-- Emits the number only. This used to render its own bg-red-600 pill, which then
     sat nested inside the navbar's badge — two stacked badges, and red won. The
     count is this component's job; presentation belongs to its one caller. --}}
<span>{{ $count }}</span>
