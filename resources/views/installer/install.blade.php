@extends('installer.layout')

@section('content')
<div class="p-8">
    <h2 class="text-2xl font-bold text-gray-800 mb-6">
        <i class="fas fa-cog fa-spin text-purple-600 mr-2"></i>Installing Application
    </h2>

    <div class="mb-8">
        <div class="bg-gray-200 rounded-full h-4 overflow-hidden">
            <div id="progress-bar" class="bg-gradient-to-r from-purple-600 to-purple-800 h-full transition-all duration-500" style="width: 0%"></div>
        </div>
        <p id="progress-text" class="text-center text-gray-600 mt-2">Preparing installation...</p>
    </div>

    <div id="steps" class="space-y-3 mb-8">
        <div class="step-item flex items-center p-3 border rounded" data-step="1">
            <i class="fas fa-circle-notch fa-spin text-gray-400 mr-3"></i>
            <span class="text-gray-600">Clearing caches...</span>
        </div>
        <div class="step-item flex items-center p-3 border rounded" data-step="2">
            <i class="fas fa-circle-notch fa-spin text-gray-400 mr-3"></i>
            <span class="text-gray-600">Migrating database...</span>
        </div>
        <div class="step-item flex items-center p-3 border rounded" data-step="3">
            <i class="fas fa-circle-notch fa-spin text-gray-400 mr-3"></i>
            <span class="text-gray-600">Creating roles & permissions...</span>
        </div>
        <div class="step-item flex items-center p-3 border rounded" data-step="4">
            <i class="fas fa-circle-notch fa-spin text-gray-400 mr-3"></i>
            <span class="text-gray-600">Creating admin user...</span>
        </div>
        <div class="step-item flex items-center p-3 border rounded" data-step="5">
            <i class="fas fa-circle-notch fa-spin text-gray-400 mr-3"></i>
            <span class="text-gray-600">Seeding demo data...</span>
        </div>
        <div class="step-item flex items-center p-3 border rounded" data-step="6">
            <i class="fas fa-circle-notch fa-spin text-gray-400 mr-3"></i>
            <span class="text-gray-600">Creating storage links...</span>
        </div>
        <div class="step-item flex items-center p-3 border rounded" data-step="7">
            <i class="fas fa-circle-notch fa-spin text-gray-400 mr-3"></i>
            <span class="text-gray-600">Optimizing application...</span>
        </div>
        <div class="step-item flex items-center p-3 border rounded" data-step="8">
            <i class="fas fa-circle-notch fa-spin text-gray-400 mr-3"></i>
            <span class="text-gray-600">Finalizing installation...</span>
        </div>
    </div>

    <div id="error-message" class="hidden bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4"></div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    let currentStep = 1;
    const totalSteps = 8;

    function updateProgress(step, message) {
        const percentage = (step / totalSteps) * 100;
        document.getElementById('progress-bar').style.width = percentage + '%';
        document.getElementById('progress-text').textContent = message || 'Processing...';
    }

    function updateStepStatus(step, status, message) {
        const stepEl = document.querySelector(`.step-item[data-step="${step}"]`);
        const icon = stepEl.querySelector('i');
        const text = stepEl.querySelector('span');

        if (status === 'success') {
            icon.className = 'fas fa-check-circle text-green-600 mr-3';
            stepEl.classList.add('bg-green-50', 'border-green-200');
        } else if (status === 'error') {
            icon.className = 'fas fa-times-circle text-red-600 mr-3';
            stepEl.classList.add('bg-red-50', 'border-red-200');
        } else {
            icon.className = 'fas fa-circle-notch fa-spin text-purple-600 mr-3';
            stepEl.classList.add('bg-purple-50', 'border-purple-200');
        }

        if (message) {
            text.textContent = message;
        }
    }

    async function runInstallation() {
        for (let step = 1; step <= totalSteps; step++) {
            try {
                updateStepStatus(step, 'processing');
                updateProgress(step, `Step ${step} of ${totalSteps}...`);

                const response = await fetch('{{ route('installer.process') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ step: step })
                });

                const data = await response.json();

                if (data.success) {
                    updateStepStatus(step, 'success', data.message);
                    
                    if (data.redirect) {
                        setTimeout(() => {
                            window.location.href = data.redirect;
                        }, 1000);
                        return;
                    }
                } else {
                    throw new Error(data.message);
                }

                await new Promise(resolve => setTimeout(resolve, 500));
            } catch (error) {
                updateStepStatus(step, 'error', 'Failed: ' + error.message);
                document.getElementById('error-message').textContent = 'Installation failed: ' + error.message;
                document.getElementById('error-message').classList.remove('hidden');
                return;
            }
        }
    }

    runInstallation();
});
</script>
@endpush
