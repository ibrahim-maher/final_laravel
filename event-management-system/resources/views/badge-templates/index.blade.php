@extends('layouts.app')

@section('title', 'Badge Templates')
@section('page-title', 'Badge Templates')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex justify-between items-center">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Badge Templates</h2>
            <p class="text-gray-600">Create and manage event badge templates</p>
        </div>
        <a href="{{ route('badge-templates.create') }}" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
            <i class="fas fa-plus mr-2"></i>
            Create Template
        </a>
    </div>

    <!-- Templates Grid -->
    @if($templates->count() > 0)
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @foreach($templates as $template)
        <div class="bg-white rounded-xl shadow-lg border border-gray-200 hover:shadow-xl transition-shadow">
            <!-- Template Preview -->
            <div class="p-4 border-b border-gray-100">
                <div class="relative bg-gray-50 rounded-lg overflow-hidden" style="height: 200px;">
                    @if($template->background_image_url)
                    <div class="absolute inset-0 bg-cover bg-center" style="background-image: url('{{ $template->background_image_url }}')"></div>
                    @else
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-100 to-purple-100"></div>
                    @endif
                    
                    <!-- Template Content Preview -->
                    <div class="absolute inset-0 p-2">
                        @foreach($template->contents->take(3) as $content)
                        <div class="absolute text-xs font-medium text-gray-800 bg-white bg-opacity-75 px-1 rounded"
                             style="left: {{ min($content->position_x / $template->width * 100, 90) }}%; 
                                    top: {{ min($content->position_y / $template->height * 100, 90) }}%;">
                            {{ $content->getFieldDisplayName() }}
                        </div>
                        @endforeach
                        
                        @if($template->contents->count() > 3)
                        <div class="absolute bottom-2 right-2 text-xs text-gray-600 bg-white bg-opacity-75 px-2 py-1 rounded">
                            +{{ $template->contents->count() - 3 }} more
                        </div>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Template Info -->
            <div class="p-4">
                <div class="flex items-start justify-between mb-2">
                    <h3 class="text-lg font-semibold text-gray-900">{{ $template->name }}</h3>
                    <span class="text-xs bg-blue-100 text-blue-800 px-2 py-1 rounded">
                        {{ $template->width }}×{{ $template->height }}cm
                    </span>
                </div>
                
                <div class="space-y-2 text-sm text-gray-600">
                    <div class="flex items-center">
                        <i class="fas fa-calendar-alt mr-2 text-gray-400"></i>
                        <span>{{ $template->ticket->event->name ?? 'No Event' }}</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-ticket-alt mr-2 text-gray-400"></i>
                        <span>{{ $template->ticket->name ?? 'No Ticket' }}</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-user mr-2 text-gray-400"></i>
                        <span>{{ $template->creator->name ?? 'Unknown' }}</span>
                    </div>
                    <div class="flex items-center">
                        <i class="fas fa-clock mr-2 text-gray-400"></i>
                        <span>{{ $template->created_at->diffForHumans() }}</span>
                    </div>
                </div>

                <!-- Actions -->
                <div class="mt-4 flex space-x-2">
                    <a href="{{ route('badge-templates.preview', $template) }}" 
                       class="flex-1 text-center px-3 py-2 bg-blue-50 text-blue-700 rounded-lg hover:bg-blue-100 text-sm font-medium">
                        <i class="fas fa-eye mr-1"></i>
                        Preview
                    </a>
                    <a href="{{ route('badge-templates.createOrEdit', ['ticket' => $template->ticket_id]) }}" 
                       class="flex-1 text-center px-3 py-2 bg-green-50 text-green-700 rounded-lg hover:bg-green-100 text-sm font-medium">
                        <i class="fas fa-edit mr-1"></i>
                        Edit
                    </a>
                    <form method="POST" action="{{ route('badge-templates.destroy', $template) }}" 
                          class="flex-1" onsubmit="return confirm('Are you sure you want to delete this template?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                class="w-full px-3 py-2 bg-red-50 text-red-700 rounded-lg hover:bg-red-100 text-sm font-medium">
                            <i class="fas fa-trash mr-1"></i>
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="mt-6">
        {{ $templates->links() }}
    </div>
    @else
    <!-- Empty State -->
    <div class="text-center py-12">
        <div class="bg-white rounded-xl shadow-lg p-8">
            <i class="fas fa-id-badge text-6xl text-gray-300 mb-4"></i>
            <h3 class="text-xl font-semibold text-gray-900 mb-2">No Badge Templates Yet</h3>
            <p class="text-gray-600 mb-6">Create your first badge template to get started with event badges.</p>
            <a href="{{ route('badge-templates.create') }}" 
               class="inline-flex items-center px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700">
                <i class="fas fa-plus mr-2"></i>
                Create Your First Template
            </a>
        </div>
    </div>
    @endif
</div>
@endsection