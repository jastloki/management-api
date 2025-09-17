@extends('admin.layouts.app')

@section('title', 'Edit Status')
@section('heading', 'Edit Status')

@section('page-actions')
<div class="btn-group" role="group">
    <a href="{{ route('admin.statuses.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back to Statuses
    </a>
    <a href="{{ route('admin.statuses.show', $status) }}" class="btn btn-outline-info">
        <i class="bi bi-eye me-2"></i>View Details
    </a>
</div>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8">
        <div class="card">
            <div class="card-header bg-white border-0">
                <h5 class="card-title mb-0">
                    <i class="bi bi-pencil-square me-2 text-primary"></i>Edit Status: {{ $status->name }}
                </h5>
                <p class="text-muted small mb-0">Update status information</p>
            </div>

            <div class="card-body">
                <form action="{{ route('admin.statuses.update', $status) }}" method="POST">
                    @csrf
                    @method('PUT')

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
                            value="{{ old('name', $status->name) }}"
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
                        >{{ old('description', $status->description) }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">
                                <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                            </div>
                        @enderror
                        <div class="form-text">
                            <i class="bi bi-info-circle me-1"></i>Optional description to explain when this status should be used (max 1000 characters)
                        </div>
                    </div>

                    <!-- Status Information -->
                    <div class="mb-4">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="card bg-light border-0">
                                    <div class="card-body p-3">
                                        <h6 class="card-title small mb-2">
                                            <i class="bi bi-calendar-plus me-1"></i>Created
                                        </h6>
                                        <p class="card-text small mb-0">{{ $status->created_at->format('M d, Y \a\t H:i') }}</p>
                                        <small class="text-muted">{{ $status->created_at->diffForHumans() }}</small>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-light border-0">
                                    <div class="card-body p-3">
                                        <h6 class="card-title small mb-2">
                                            <i class="bi bi-calendar-check me-1"></i>Last Updated
                                        </h6>
                                        <p class="card-text small mb-0">{{ $status->updated_at->format('M d, Y \a\t H:i') }}</p>
                                        <small class="text-muted">{{ $status->updated_at->diffForHumans() }}</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('admin.statuses.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg me-2"></i>Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="bi bi-check-lg me-2"></i>Update Status
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Help Card -->
        <div class="card mt-4">
            <div class="card-body">
                <h6 class="card-title">
                    <i class="bi bi-lightbulb me-2 text-warning"></i>Tips for Editing Statuses
                </h6>
                <ul class="mb-0 small text-muted">
                    <li>Changing the status name may affect other records that reference this status</li>
                    <li>Make sure the new name is still descriptive and unique</li>
                    <li>Consider the impact on existing workflows before making changes</li>
                    <li>Use the description field to clarify any changes in usage</li>
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

    // Highlight changes
    const originalName = @json($status->name);
    const originalDescription = @json($status->description ?? '');

    document.getElementById('name').addEventListener('input', function() {
        if (this.value !== originalName) {
            this.style.borderColor = '#ffc107';
        } else {
            this.style.borderColor = '';
        }
    });

    document.getElementById('description').addEventListener('input', function() {
        if (this.value !== originalDescription) {
            this.style.borderColor = '#ffc107';
        } else {
            this.style.borderColor = '';
        }
    });
</script>
@endsection
