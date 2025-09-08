@extends('layouts.user')

@section('content')
<div class="bg-gray-100 antialiased">
    <div class="container mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="mb-6">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                <div>
                  <a href="{{ route('user.report') }}" class="text-sm text-gray-600 hover:text-blue-600 transition">
                    ← Back to Reports
                  </a>
                    <h1 class="text-3xl font-bold text-gray-800 mt-3">Report Case #{{ $report->id }}</h1>
                    <p class="mt-1 text-gray-500">Details for incident reported on {{ $report->created_at->format('F j, Y') }}</p>
                </div>
                
            </div>
            <hr class="my-6">
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 lg:gap-8">
            <div class="lg:col-span-2 space-y-6">
                <div class="bg-white p-6 shadow-sm rounded-lg border border-gray-200">
                    <div class="flex items-center justify-between mb-4 border-b pb-3">
                      <h3 class="text-lg font-semibold text-gray-900 flex items-center">
                          <i class="fas fa-file-invoice text-gray-400 mr-3"></i>
                          Report Details
                      </h3>
                      <div class="flex items-center space-x-2">
                          <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium leading-4
                              @switch(strtolower($report->status))
                                  @case('completed') bg-green-100 text-green-800 @break
                                  @case('in progress') bg-blue-100 text-blue-800 @break
                                  @case('pending') bg-yellow-100 text-yellow-800 @break
                                  @case('deny') bg-red-100 text-red-800 @break
                                  @default bg-gray-100 text-gray-800
                              @endswitch">
                              {{ ucfirst($report->status) }}
                          </span>
                          <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium leading-4
                              @if($report->esi_level == 1) bg-red-100 text-red-800
                              @elseif($report->esi_level == 2) bg-orange-100 text-orange-800
                              @elseif($report->esi_level == 3) bg-yellow-100 text-yellow-800
                              @elseif($report->esi_level == 4) bg-green-100 text-green-800
                              @elseif($report->esi_level == 5) bg-blue-100 text-blue-800
                              @else bg-gray-100 text-gray-800 @endif">
                              ISI Level {{ $report->esi_level }}
                          </span>
                      </div>
                  </div>
                    <dl class="grid grid-cols-1 sm:grid-cols-2 gap-x-6 gap-y-6">
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Descriptive Title</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ ucfirst($report->description) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Incident Type</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $report->incident_type }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">Location</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ ucfirst($report->location) }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Reporter</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $report->reported_by }}</dd>
                        </div>
                        <div>
                            <dt class="text-sm font-medium text-gray-500">Contact Information</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $report->contact_info }}</dd>
                        </div>
                        <div class="sm:col-span-2">
                            <dt class="text-sm font-medium text-gray-500">Date & Time Reported</dt>
                            <dd class="mt-1 text-sm text-gray-900">{{ $report->created_at->toDayDateTimeString() }}</dd>
                        </div>
                    </dl>
                </div>

                <div class="bg-white p-6 shadow-sm rounded-lg border border-gray-200">
                  <h3 class="text-lg font-semibold text-gray-900 border-b pb-3 mb-4 flex items-center">
                      <i class="fas fa-align-left text-gray-400 mr-3"></i>
                      Full Description
                  </h3>
                  <p class="text-gray-700 leading-relaxed whitespace-pre-wrap break-words">{{ $report->full_description }}</p>
              </div>

                @if ($report->evidence_image)
                <div class="bg-white p-6 shadow-sm rounded-lg border border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 border-b pb-3 mb-4 flex items-center">
                        <i class="fas fa-paperclip text-gray-400 mr-3"></i>
                        Attached Evidence
                    </h3>
                    <div>
                        <img src="{{ asset('storage/' . $report->evidence_image) }}"
                             alt="Evidence Documentation"
                             class="rounded-md border border-gray-200 max-w-lg w-full cursor-pointer transition-transform duration-200 hover:scale-105"
                             onclick="openFullscreen('{{ asset('storage/' . $report->evidence_image) }}')">
                        <p class="text-xs text-gray-500 mt-2">Click image to enlarge.</p>
                    </div>
                </div>
                @endif
            </div>

            <div class="lg:col-span-1 mt-8 lg:mt-0">
                @if (auth()->user()->id === $report->user_id || in_array(auth()->user()->role, ['admin', 'super_admin']))
                <div class="bg-white p-6 shadow-sm rounded-lg border border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Post a Comment</h3>
                    <form method="POST" action="{{ route('comments.store', $report->id) }}">
                        @csrf
                        <textarea name="message" required
                                  class="w-full border-gray-300 rounded-md shadow-sm focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                                  rows="4"
                                  placeholder="Add your thoughts or updates..."></textarea>
                        <button type="submit"
                                class="mt-3 inline-flex items-center justify-center px-6 py-2 border border-transparent text-sm font-medium rounded-full shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                            Submit
                        </button>
                    </form>
                </div>
                @endif

                <div class="bg-white mt-6 shadow-sm rounded-lg border border-gray-200">
                    <h3 class="text-lg font-semibold text-gray-900 p-6 border-b border-gray-200">
                        Discussion ({{ $report->comments->count() }})
                    </h3>
                    <div class="divide-y divide-gray-200">
                        @forelse ($report->comments->sortByDesc('created_at') as $comment)
                            <div class="p-6">
                                <div class="flex items-start space-x-4">
                                    <div class="flex-shrink-0 h-10 w-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-500 font-bold">
                                        {{ strtoupper(substr($comment->user->name, 0, 1)) }}
                                    </div>
                                    <div class="min-w-0 flex-1">
                                        <p class="text-sm font-medium text-gray-900">
                                                {{ Str::title(trim(implode(' ', [
                                                    $comment->user->name ?? '',
                                                    $comment->user->middle_name ?? '',
                                                    $comment->user->last_name ?? '',
                                                    $comment->user->suffix ?? '',
                                                ]))) }}
                                                
                                                @php
                                                    $role = $comment->user->role;
                                                    $badgeClass = '';
                                                    $displayText = '';
                                                    
                                                    // Check if the comment author is the original reporter
                                                    if ($comment->user->id === $report->user_id) {
                                                        $badgeClass = 'bg-green-100 text-green-800';
                                                        $displayText = 'Reporter';
                                                    } 
                                                    // Check for specific staff roles
                                                    elseif (in_array($role, ['officer', 'admin', 'super_admin'])) {
                                                        $roleClasses = [
                                                            'officer' => 'bg-blue-100 text-blue-800',
                                                            'admin' => 'bg-purple-100 text-purple-800',
                                                            'super_admin' => 'bg-yellow-100 text-yellow-800',
                                                        ];
                                                        $badgeClass = $roleClasses[$role];
                                                        $displayText = ucwords(str_replace('_', ' ', $role));
                                                    }
                                                @endphp

                                                @if($badgeClass)
                                                    <span class="ml-2 inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $badgeClass }}">
                                                        {{ $displayText }}
                                                    </span>
                                                @endif
                                            </p>
                                        <p class="text-sm text-gray-500 mt-0.5">
                                            <time datetime="{{ $comment->created_at->toIso8601String() }}">{{ $comment->created_at->diffForHumans() }}</time>
                                        </p>
                                        <div class="mt-3 text-sm text-gray-700 break-words">
                                            <p class="whitespace-pre-wrap break-words">{{ $comment->message }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="p-8 text-center">
                                <i class="fas fa-comments text-gray-300 text-4xl mx-auto"></i>
                                <p class="mt-4 text-sm text-gray-600">No comments yet.</p>
                                <p class="text-xs text-gray-400">Be the first to contribute to the discussion.</p>
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<div id="fullscreen-modal" class="fixed inset-0 bg-black bg-opacity-80 flex items-center justify-center z-50 p-4 hidden" onclick="closeFullscreen()">
    <div class="relative max-w-4xl max-h-full" onclick="event.stopPropagation()">
        <img id="fullscreen-image" class="block max-w-full max-h-[90vh] rounded-lg shadow-xl">
        <button onclick="closeFullscreen()"
                class="absolute -top-3 -right-3 h-8 w-8 rounded-full bg-white text-gray-700 hover:bg-gray-200 flex items-center justify-center text-2xl font-light leading-none">
            &times;
        </button>
    </div>
</div>

<script>
    function openFullscreen(src) {
        document.getElementById('fullscreen-image').src = src;
        document.getElementById('fullscreen-modal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeFullscreen() {
        document.getElementById('fullscreen-modal').classList.add('hidden');
        document.getElementById('fullscreen-image').src = '';
        document.body.style.overflow = 'auto';
    }

    document.addEventListener('keydown', (e) => (e.key === "Escape") && closeFullscreen());
</script>
@endsection