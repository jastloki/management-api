@extends('admin.layouts.app')

@section('title', 'Import ' . (request('converted') === 'false' ? 'Leads' : 'Clients'))
@section('heading', 'Import ' . (request('converted') === 'false' ? 'Leads' : 'Clients') . ' from Excel')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white">
                <h5 class="card-title mb-0">
                    <i class="bi bi-file-earmark-excel me-2"></i>Import {{ request('converted') === 'false' ? 'Leads' : 'Clients' }} from Excel
                </h5>
                <p class="mb-0 small opacity-75">Upload an Excel file to import multiple {{ request('converted') === 'false' ? 'leads' : 'clients' }} at once</p>
            </div>

            <div class="card-body">
                <!-- Instructions -->
                <div class="alert alert-info mb-4">
                    <h6 class="alert-heading">
                        <i class="bi bi-info-circle me-2"></i>Import Instructions
                    </h6>
                    <ul class="mb-2">
                        <li>Download the sample template below to see the required format</li>
                        <li>Your Excel file must have headers: <strong>name, email, phone, company, address, status</strong></li>
                        <li>Email addresses must be unique and valid</li>
                        <li>Status should be either "active" or "inactive" (defaults to "active")</li>
                        <li>Phone, company, and address fields are optional</li>
                        <li>Maximum file size: 2MB</li>
                    </ul>
                    <a href="{{ route('admin.clients.template') }}" class="btn btn-outline-info btn-sm">
                        <i class="bi bi-download me-1"></i>Download Sample Template
                    </a>
                </div>

                <!-- Upload Form -->
                <form action="{{ route('admin.clients.import', ['converted' => request('converted', 'true')]) }}" method="POST" enctype="multipart/form-data" id="importForm">
                    @csrf
                    <input type="hidden" name="converted" value="{{ request('converted', 'true') }}">

                    <div class="mb-4">
                        <label for="file" class="form-label fw-bold">Select Excel File</label>
                        <div class="input-group">
                            <input type="file"
                                   class="form-control @error('file') is-invalid @enderror"
                                   id="file"
                                   name="file"
                                   accept=".xlsx,.xls,.csv"
                                   required>
                            <button class="btn btn-primary" type="submit" id="uploadBtn">
                                <i class="bi bi-upload me-1"></i>Import {{ request('converted') === 'false' ? 'Leads' : 'Clients' }}
                            </button>
                        </div>
                        @error('file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted">
                            Supported formats: .xlsx, .xls, .csv (Max: 2MB)
                        </small>
                    </div>
                </form>

                <!-- File Preview -->
                <div id="filePreview" class="d-none">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6 class="card-title">
                                <i class="bi bi-file-earmark-check text-success me-2"></i>Selected File
                            </h6>
                            <div id="fileName" class="text-muted"></div>
                            <div id="fileSize" class="text-muted small"></div>
                        </div>
                    </div>
                </div>

                <!-- Progress Bar -->
                <div id="uploadProgress" class="d-none">
                    <div class="progress mb-2">
                        <div class="progress-bar progress-bar-striped progress-bar-animated"
                             role="progressbar"
                             style="width: 100%">
                        </div>
                    </div>
                    <p class="text-center text-muted">
                        <i class="bi bi-hourglass-split me-1"></i>Processing your file...
                    </p>
                </div>

                <!-- Back Link -->
                <div class="mt-4 text-center">
                    <a href="{{ route('admin.clients.index') }}" class="btn btn-outline-secondary">
                        <i class="bi bi-arrow-left me-1"></i>Back to Clients
                    </a>
                </div>
            </div>
        </div>

        <!-- Import Tips -->
        <div class="card mt-4">
            <div class="card-header">
                <h6 class="card-title mb-0">
                    <i class="bi bi-lightbulb text-warning me-2"></i>Import Tips
                </h6>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <h6 class="text-success">✓ Good Practices</h6>
                        <ul class="small text-muted">
                            <li>Use the provided template</li>
                            <li>Check for duplicate emails</li>
                            <li>Use consistent phone formats</li>
                            <li>Test with a small file first</li>
                        </ul>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-danger">✗ Common Issues</h6>
                        <ul class="small text-muted">
                            <li>Missing required headers</li>
                            <li>Invalid email formats</li>
                            <li>Duplicate email addresses</li>
                            <li>Wrong status values</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const fileInput = document.getElementById('file');
        const filePreview = document.getElementById('filePreview');
        const fileName = document.getElementById('fileName');
        const fileSize = document.getElementById('fileSize');
        const uploadProgress = document.getElementById('uploadProgress');
        const importForm = document.getElementById('importForm');
        const uploadBtn = document.getElementById('uploadBtn');

        // File selection preview
        fileInput.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                fileName.textContent = file.name;
                fileSize.textContent = `Size: ${(file.size / 1024 / 1024).toFixed(2)} MB`;
                filePreview.classList.remove('d-none');
            } else {
                filePreview.classList.add('d-none');
            }
        });

        // Form submission with progress
        importForm.addEventListener('submit', function(e) {
            const file = fileInput.files[0];
            if (!file) {
                e.preventDefault();
                alert('Please select a file to import.');
                return;
            }

            // Show progress and disable button
            uploadProgress.classList.remove('d-none');
            uploadBtn.disabled = true;
            uploadBtn.innerHTML = '<i class="bi bi-hourglass-split me-1"></i>Processing...';
        });
    });
</script>
@endsection
