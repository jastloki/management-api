@extends('admin.layouts.app')

@section('title', 'Create Status')
@section('heading', 'Create New Status')

@section('page-actions')
<div class="btn-group" role="group">
    <a href="{{ route('admin.statuses.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back to Statuses
    </a>
</div>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-white border-0">
                <h5 class="card-title mb-0">
                    <i class="bi bi-plus-circle me-2 text-primary"></i>Create New Status
                </h5>
                <p class="text-muted small mb-0">Add a new status to the system</p>
            </div>

            <div class="card-body">
                <form action="{{ route('admin.statuses.store') }}" method="POST">
                    @csrf

                    <!-- Name Field -->
                    <div class="mb-4">
                        <label for="name" class="form-label fw-semibold">
                            <i class="bi bi-tag me-1"></i>Status Name <span class="text-danger">*</span>
                        </label>
                        <input
                            type="text"
                            class="form-control @error('name') is-invalid @enderror"
                            id="name"
                            name="name"
                            value="{{ old('name') }}"
                            placeholder="Enter status name (e.g., Active, Pending, Completed)"
                            required
                            maxlength="255"
                        >
                        @error('name')
                            <div class="invalid-feedback">
                                <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                            </div>
                        @enderror
                        <div class="form-text">
                            <i class="bi bi-info-circle me-1"></i>Enter a unique name for this status (max 255 characters)
                        </div>
                    </div>

                    <!-- Description Field -->
                    <div class="mb-4">
                        <label for="description" class="form-label fw-semibold">
                            <i class="bi bi-text-paragraph me-1"></i>Description
                        </label>
                        <textarea
                            class="form-control @error('description') is-invalid @enderror"
                            id="description"
                            name="description"
                            rows="4"
                            placeholder="Enter a description for this status (optional)"
                            maxlength="1000"
                        >{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">
                                <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                            </div>
                        @enderror
                        <div class="form-text">
                            <i class="bi bi-info-circle me-1"></i>Optional description to explain when this status should be used (max 1000 characters)
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('admin.statuses.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg me-2"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-2"></i>Create Status
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Help Card -->
        <div class="card mt-4">
            <div class="card-body">
                <h6 class="card-title">
                    <i class="bi bi-lightbulb me-2 text-warning"></i>Tips for Creating Statuses
                </h6>
                <ul class="mb-0 small text-muted">
                    <li>Use clear, descriptive names that are easy to understand</li>
                    <li>Status names should be unique across the system</li>
                    <li>Consider how this status will be used in your workflow</li>
                    <li>Add a description to help other users understand when to use this status</li>
                </ul>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    // Character counter for description
    const descriptionField = document.getElementById('description');
    const maxLength = 1000;

    if (descriptionField) {
        // Create character counter element
        const counterElement = document.createElement('div');
        counterElement.className = 'form-text text-end';
        counterElement.innerHTML = `<small class="text-muted"><span id="charCount">0</span>/${maxLength} characters</small>`;
        descriptionField.parentNode.appendChild(counterElement);

        const charCountSpan = document.getElementById('charCount');

        descriptionField.addEventListener('input', function() {
            const currentLength = this.value.length;
            charCountSpan.textContent = currentLength;

            if (currentLength > maxLength * 0.9) {
                charCountSpan.parentElement.className = 'text-warning';
            } else if (currentLength === maxLength) {
                charCountSpan.parentElement.className = 'text-danger';
            } else {
                charCountSpan.parentElement.className = 'text-muted';
            }
        });

        // Initial count
        charCountSpan.textContent = descriptionField.value.length;
    }

    // Auto-focus on name field
    document.getElementById('name').focus();

    // Form validation
    document.querySelector('form').addEventListener('submit', function(e) {
        const nameField = document.getElementById('name');
        if (!nameField.value.trim()) {
            e.preventDefault();
            nameField.focus();
            alert('Please enter a status name.');
        }
    });
</script>
@endsection
