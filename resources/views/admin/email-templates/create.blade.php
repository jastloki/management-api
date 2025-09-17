@extends('admin.layouts.app')

@section('title', 'Create Email Template')
@section('heading', 'Create New Email Template')

@section('page-actions')
<div class="btn-group" role="group">
    <a href="{{ route('admin.email-templates.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-2"></i>Back to Templates
    </a>
</div>
@endsection

@section('content')
<div class="row justify-content-center">
    <div class="col-md-10">
        <div class="card">
            <div class="card-header bg-white border-0">
                <h5 class="card-title mb-0">
                    <i class="bi bi-plus-circle me-2 text-primary"></i>Create New Email Template
                </h5>
                <p class="text-muted small mb-0">Design a new email template for your communications</p>
            </div>

            <div class="card-body">
                <form action="{{ route('admin.email-templates.store') }}" method="POST" id="templateForm">
                    @csrf

                    <div class="row">
                        <!-- Template Name -->
                        <div class="col-md-6 mb-4">
                            <label for="name" class="form-label fw-semibold">
                                <i class="bi bi-tag me-1"></i>Template Name <span class="text-danger">*</span>
                            </label>
                            <input
                                type="text"
                                class="form-control @error('name') is-invalid @enderror"
                                id="name"
                                name="name"
                                value="{{ old('name') }}"
                                placeholder="e.g., Welcome Email, Order Confirmation"
                                required
                                maxlength="255"
                            >
                            @error('name')
                                <div class="invalid-feedback">
                                    <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                                </div>
                            @enderror
                            <div class="form-text">
                                <i class="bi bi-info-circle me-1"></i>Enter a unique name to identify this template
                            </div>
                        </div>

                        <!-- Active Status -->
                        <div class="col-md-6 mb-4">
                            <label for="is_active" class="form-label fw-semibold">
                                <i class="bi bi-toggle-on me-1"></i>Status
                            </label>
                            <div class="form-check form-switch">
                                <input
                                    class="form-check-input"
                                    type="checkbox"
                                    id="is_active"
                                    name="is_active"
                                    value="1"
                                    {{ old('is_active', true) ? 'checked' : '' }}
                                >
                                <label class="form-check-label" for="is_active">
                                    Active (Template can be used for sending emails)
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Email Subject -->
                    <div class="mb-4">
                        <label for="subject" class="form-label fw-semibold">
                            <i class="bi bi-envelope me-1"></i>Email Subject <span class="text-danger">*</span>
                        </label>
                        <input
                            type="text"
                            class="form-control @error('subject') is-invalid @enderror"
                            id="subject"
                            name="subject"
                            value="{{ old('subject') }}"
                            placeholder="e.g., Welcome to app_name!"
                            required
                            maxlength="255"
                        >
                        @error('subject')
                            <div class="invalid-feedback">
                                <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                            </div>
                        @enderror
                        <div class="form-text">
                            <i class="bi bi-info-circle me-1"></i>You can use variables like client_name, app_name
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mb-4">
                        <label for="description" class="form-label fw-semibold">
                            <i class="bi bi-text-paragraph me-1"></i>Description
                        </label>
                        <textarea
                            class="form-control @error('description') is-invalid @enderror"
                            id="description"
                            name="description"
                            rows="2"
                            placeholder="Brief description of when this template should be used"
                            maxlength="1000"
                        >{{ old('description') }}</textarea>
                        @error('description')
                            <div class="invalid-feedback">
                                <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                            </div>
                        @enderror
                        <div class="form-text">
                            <span id="descriptionCount">0</span>/1000 characters
                        </div>
                    </div>

                    <!-- Email Content -->
                    <div class="mb-4">
                        <label for="content" class="form-label fw-semibold">
                            <i class="bi bi-file-richtext me-1"></i>Email Content <span class="text-danger">*</span>
                        </label>

                        <!-- Editor Toolbar -->
                        <div class="editor-toolbar border rounded-top p-2 bg-light">
                            <div class="btn-group btn-group-sm me-2" role="group">
                                <button type="button" class="btn btn-outline-secondary" onclick="formatText('bold')" title="Bold">
                                    <i class="bi bi-type-bold"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="formatText('italic')" title="Italic">
                                    <i class="bi bi-type-italic"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="formatText('underline')" title="Underline">
                                    <i class="bi bi-type-underline"></i>
                                </button>
                            </div>

                            <div class="btn-group btn-group-sm me-2" role="group">
                                <button type="button" class="btn btn-outline-secondary" onclick="formatText('justifyLeft')" title="Align Left">
                                    <i class="bi bi-text-left"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="formatText('justifyCenter')" title="Align Center">
                                    <i class="bi bi-text-center"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="formatText('justifyRight')" title="Align Right">
                                    <i class="bi bi-text-right"></i>
                                </button>
                            </div>

                            <div class="btn-group btn-group-sm me-2" role="group">
                                <button type="button" class="btn btn-outline-secondary" onclick="formatText('insertUnorderedList')" title="Bullet List">
                                    <i class="bi bi-list-ul"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="formatText('insertOrderedList')" title="Numbered List">
                                    <i class="bi bi-list-ol"></i>
                                </button>
                            </div>

                            <div class="btn-group btn-group-sm me-2" role="group">
                                <button type="button" class="btn btn-outline-secondary" onclick="insertLink()" title="Insert Link">
                                    <i class="bi bi-link"></i>
                                </button>
                                <button type="button" class="btn btn-outline-secondary" onclick="insertVariable()" title="Insert Variable">
                                    <i class="bi bi-braces"></i>
                                </button>
                            </div>

                            <div class="btn-group btn-group-sm" role="group">
                                <button type="button" class="btn btn-outline-secondary" onclick="toggleSource()" title="View Source">
                                    <i class="bi bi-code"></i>
                                </button>
                            </div>
                        </div>

                        <div class="editor-container border rounded-bottom">
                            <div id="contentEditor" contenteditable="true" class="form-control p-3" style="min-height: 300px; height: auto;">
                                {!! old('content', '') !!}
                            </div>
                            <textarea
                                name="content"
                                id="content"
                                class="form-control @error('content') is-invalid @enderror d-none"
                                required
                            >{{ old('content') }}</textarea>
                        </div>

                        @error('content')
                            <div class="invalid-feedback d-block">
                                <i class="bi bi-exclamation-circle me-1"></i>{{ $message }}
                            </div>
                        @enderror

                        <div class="form-text">
                            <i class="bi bi-info-circle me-1"></i>Use the toolbar to format your email content. You can include variables that will be replaced with actual data.
                        </div>
                    </div>

                    <!-- Available Variables -->
                    <div class="mb-4">
                        <div class="alert alert-info">
                            <h6 class="alert-heading">
                                <i class="bi bi-lightbulb me-2"></i>Available Variables
                            </h6>
                            <p class="mb-2">You can use these variables in your template. They will be automatically replaced with actual data:</p>
                            <div class="d-flex flex-wrap gap-2">
                                <code class="badge bg-white text-primary">client_name</code>
                                <code class="badge bg-white text-primary">client_email</code>
                                <code class="badge bg-white text-primary">app_name</code>
                                <code class="badge bg-white text-primary">current_date</code>
                                <code class="badge bg-white text-primary">current_time</code>
                                <code class="badge bg-white text-primary">company_name</code>
                                <code class="badge bg-white text-primary">support_email</code>
                            </div>
                        </div>
                    </div>

                    <!-- Preview Section -->
                    <div class="card mb-4">
                        <div class="card-header bg-light">
                            <h6 class="mb-0">
                                <i class="bi bi-eye me-2"></i>Live Preview
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="mb-3">
                                <strong>Subject:</strong>
                                <div id="previewSubject" class="text-muted">Your email subject will appear here...</div>
                            </div>
                            <div>
                                <strong>Content:</strong>
                                <div id="previewContent" class="border rounded p-3 bg-light mt-2">
                                    Your email content will appear here...
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Action Buttons -->
                    <div class="d-flex justify-content-between align-items-center">
                        <a href="{{ route('admin.email-templates.index') }}" class="btn btn-outline-secondary">
                            <i class="bi bi-x-lg me-2"></i>Cancel
                        </a>
                        <div>
                            <button type="button" class="btn btn-outline-primary me-2" onclick="saveAsDraft()">
                                <i class="bi bi-save me-2"></i>Save as Draft
                            </button>
                            <button type="submit" class="btn btn-primary">
                                <i class="bi bi-check-lg me-2"></i>Create Template
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Variable Insert Modal -->
<div class="modal fade" id="variableModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Insert Variable</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>Select a variable to insert:</p>
                <div class="d-grid gap-2">
                    <button type="button" class="btn btn-outline-primary" onclick="insertVariableText('client_name')">client_name</button>
                    <button type="button" class="btn btn-outline-primary" onclick="insertVariableText('client_email')">client_email</button>
                    <button type="button" class="btn btn-outline-primary" onclick="insertVariableText('app_name')">app_name</button>
                    <button type="button" class="btn btn-outline-primary" onclick="insertVariableText('current_date')">current_date</button>
                    <button type="button" class="btn btn-outline-primary" onclick="insertVariableText('company_name')">company_name</button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .editor-container {
        position: relative;
    }

    #contentEditor {
        overflow-y: auto;
        max-height: 500px;
        background: white;
    }

    #contentEditor:focus {
        outline: none;
        box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
    }

    .editor-toolbar .btn-group-sm > .btn {
        padding: 0.25rem 0.5rem;
    }

    code.badge {
        font-size: 0.9em;
        padding: 0.4em 0.6em;
    }
</style>
@endsection

@section('scripts')
<script>
    let sourceMode = false;
    const editor = document.getElementById('contentEditor');
    const textarea = document.getElementById('content');
    const subjectInput = document.getElementById('subject');
    const descriptionInput = document.getElementById('description');

    // Character counter for description
    descriptionInput.addEventListener('input', function() {
        document.getElementById('descriptionCount').textContent = this.value.length;
    });

    // Initialize counter
    document.getElementById('descriptionCount').textContent = descriptionInput.value.length;

    // Format text commands
    function formatText(command) {
        document.execCommand(command, false, null);
        updateContent();
    }

    // Insert link
    function insertLink() {
        const url = prompt('Enter URL:');
        if (url) {
            document.execCommand('createLink', false, url);
            updateContent();
        }
    }

    // Insert variable
    function insertVariable() {
        const modal = new bootstrap.Modal(document.getElementById('variableModal'));
        modal.show();
    }

    function insertVariableText(variable) {
        const text = '{{' + variable + '}}';
        document.execCommand('insertText', false, text);
        updateContent();
        bootstrap.Modal.getInstance(document.getElementById('variableModal')).hide();
    }

    // Toggle source view
    function toggleSource() {
        sourceMode = !sourceMode;
        if (sourceMode) {
            const html = editor.innerHTML;
            editor.innerText = html;
        } else {
            const text = editor.innerText;
            editor.innerHTML = text;
        }
    }

    // Update hidden textarea
    function updateContent() {
        textarea.value = editor.innerHTML;
        updatePreview();
    }

    // Update preview
    function updatePreview() {
        const subject = subjectInput.value || 'Your email subject will appear here...';
        const content = editor.innerHTML || 'Your email content will appear here...';

        // Simple variable replacement for preview
        const sampleData = {
            client_name: 'John Doe',
            client_email: 'john@example.com',
            app_name: '{{ config("app.name") }}',
            current_date: new Date().toLocaleDateString(),
            current_time: new Date().toLocaleTimeString(),
            company_name: 'Your Company',
            support_email: 'support@example.com'
        };

        let previewSubject = subject;
        let previewContent = content;

        for (const [key, value] of Object.entries(sampleData)) {
            const regex = new RegExp('{{' + key + '}}', 'g');
            previewSubject = previewSubject.replace(regex, value);
            previewContent = previewContent.replace(regex, value);
        }

        document.getElementById('previewSubject').textContent = previewSubject;
        document.getElementById('previewContent').innerHTML = previewContent;
    }

    // Save as draft
    function saveAsDraft() {
        document.getElementById('is_active').checked = false;
        document.getElementById('templateForm').submit();
    }

    // Event listeners
    editor.addEventListener('input', updateContent);
    editor.addEventListener('paste', function(e) {
        e.preventDefault();
        const text = (e.clipboardData || window.clipboardData).getData('text/plain');
        document.execCommand('insertText', false, text);
        updateContent();
    });

    subjectInput.addEventListener('input', updatePreview);

    // Form submission
    document.getElementById('templateForm').addEventListener('submit', function(e) {
        updateContent();
        if (!textarea.value.trim()) {
            e.preventDefault();
            alert('Please enter email content.');
            editor.focus();
        }
    });

    // Initialize preview
    updatePreview();

    // Auto-focus
    document.getElementById('name').focus();
</script>
@endsection
