
@extends('layouts.app')

@section('title', 'Edit Category')
@section('page-title', 'Edit Category')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-2xl font-bold text-gray-900">Edit Category</h2>
            <p class="text-gray-600">Update category information</p>
        </div>
        <div class="flex items-center space-x-3">
            <a href="{{ route('categories.show', $category) }}" class="inline-flex items-center px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
                <i class="fas fa-eye mr-2"></i>
                View Category
            </a>
            <a href="{{ route('categories.index') }}" class="inline-flex items-center px-4 py-2 bg-gray-500 text-white rounded-lg hover:bg-gray-600 transition-colors">
                <i class="fas fa-arrow-left mr-2"></i>
                Back to Categories
            </a>
        </div>
    </div>

    <!-- Edit Form -->
    <div class="bg-white rounded-xl shadow-lg">
        <form method="POST" action="{{ route('categories.update', $category) }}" class="p-6 space-y-6">
            @csrf
            @method('PATCH')

            <!-- Category Information -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Category Information</h3>
                
                <div class="space-y-6">
                    <!-- Category Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                            Category Name <span class="text-red-500">*</span>
                        </label>
                        <input type="text" 
                               id="name" 
                               name="name" 
                               value="{{ old('name', $category->name) }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-500 @enderror"
                               placeholder="Enter category name (e.g., Conference)">
                        @error('name')
                        <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>
            </div>

            <!-- Preview Section -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Preview</h3>
                <div class="bg-gray-50 rounded-lg p-4">
                    <div class="flex items-start space-x-4">
                        <div class="w-16 h-16 bg-gradient-to-r from-purple-500 to-blue-500 rounded-lg flex items-center justify-center">
                            <i class="fas fa-tag text-2xl text-white"></i>
                        </div>
                        <div class="flex-1">
                            <h4 class="font-semibold text-gray-900" id="preview-name">{{ $category->name }}</h4>
                            <p class="text-sm text-gray-500 mt-2">
                                <i class="fas fa-calendar-alt mr-1"></i>
                                Events: <span id="preview-events">{{ $category->events->count() }}</span>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Current Statistics -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-semibold text-gray-900 mb-4">Current Statistics</h3>
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-blue-50 p-4 rounded-lg text-center">
                        <div class="text-2xl font-bold text-blue-600">{{ $category->events->count() }}</div>
                        <div class="text-sm text-gray-600">Total Events</div>
                    </div>
                    <div class="bg-green-50 p-4 rounded-lg text-center">
                        <div class="text-2xl font-bold text-green-600">{{ $category->events->where('is_active', true)->count() }}</div>
                        <div class="text-sm text-gray-600">Active Events</div>
                    </div>
                </div>
                @if($category->events->count() > 0)
                <div class="mt-4 p-3 bg-yellow-50 border border-yellow-200 rounded-lg">
                    <div class="flex items-start">
                        <i class="fas fa-info-circle text-yellow-600 mr-2 mt-0.5"></i>
                        <div class="text-sm text-yellow-800">
                            <strong>Note:</strong> This category has {{ $category->events->count() }} associated events. 
                            Changes will affect all associated events.
                        </div>
                    </div>
                </div>
                @endif
            </div>

            <!-- Danger Zone -->
            <div class="border-b border-gray-200 pb-6">
                <h3 class="text-lg font-semibold text-red-600 mb-4">Danger Zone</h3>
                <div class="bg-red-50 border border-red-200 rounded-lg p-4">
                    <div class="flex items-start">
                        <i class="fas fa-exclamation-triangle text-red-600 mr-3 mt-0.5"></i>
                        <div>
                            <h4 class="font-medium text-red-800">Delete Category</h4>
                            <p class="text-sm text-red-700 mt-1">
                                Once you delete this category, there is no going back. This action cannot be undone.
                                @if($category->events->count() > 0)
                                    <br><strong>Warning:</strong> This category is associated with {{ $category->events->count() }} events. Deleting it may cause data inconsistencies.
                                @endif
                            </p>
                            <form method="POST" action="{{ route('categories.destroy', $category) }}" class="mt-3" onsubmit="return confirm('Are you absolutely sure you want to delete this category? This action cannot be undone.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-4 py-2 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700 transition-colors">
                                    <i class="fas fa-trash mr-2"></i>
                                    Delete Category
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end space-x-4 pt-6">
                <a href="{{ route('categories.show', $category) }}" class="px-6 py-2 border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-50 transition-colors">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                    <i class="fas fa-save mr-2"></i>
                    Update Category
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Form preview functionality
    const nameInput = document.getElementById('name');
    const previewName = document.getElementById('preview-name');
    
    function updatePreview() {
        previewName.textContent = nameInput.value || 'Category Name';
    }
    
    nameInput.addEventListener('input', updatePreview);
});
</script>
@endpush
@endsection
