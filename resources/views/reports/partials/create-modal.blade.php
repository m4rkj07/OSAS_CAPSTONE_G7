<!-- Create Modal -->
<div id="createModal"
    class="hidden fixed inset-0 flex items-center justify-center bg-black bg-opacity-60 z-50 p-3 sm:p-5 overflow-y-auto">

    <div
        class="bg-white shadow-lg border border-gray-200 w-full max-w-lg sm:max-w-xl md:max-w-2xl lg:max-w-3xl transition-all duration-300 scale-95 max-h-[90vh] overflow-y-auto"
        x-transition:enter="ease-out duration-300"
        x-transition:enter-start="opacity-0 scale-90 translate-y-4"
        x-transition:enter-end="opacity-100 scale-100 translate-y-0"
        x-transition:leave="ease-in duration-200"
        x-transition:leave-start="opacity-100 scale-100 translate-y-0"
        x-transition:leave-end="opacity-0 scale-90 translate-y-4">

        <!-- Header -->
        <div
            class="flex justify-between items-center border-b border-gray-200 p-5 sticky top-0 bg-white rounded-t-xl">
            <div>
                <h3 class="text-lg font-medium text-gray-900 flex items-center gap-2">
                    Create New Report
                </h3>
                <p class="text-sm text-gray-500 mt-1">Provide clear details to help us act quickly and accurately.</p>
            </div>
        </div>

        <!-- Form -->
        <form id="createReportForm" action="{{ route('reports.store') }}" method="POST" enctype="multipart/form-data"
            class="p-5 space-y-5">
            @csrf

            <!-- Incident Type -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Incident Type <span class="text-red-500">*</span>
                </label>
                <select name="incident_type" required
                    class="w-full border border-gray-300 px-3 py-2.5 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none focus:border-transparent transition placeholder:text-gray-400">
                    <option value="" disabled selected>Select Incident Type</option>

                    <option value="Medical / Health">Medical / Health</option>
                    <option value="Behavioral / Disciplinary">Behavioral / Disciplinary</option>
                    <option value="Safety / Security Incidents">Safety / Security</option>
                    <option value="Environmental / Facility-Related Incident">Environmental / Facility-Related Incident</option>
                    <option value="Natural Disasters & Emergency Events">Natural Disasters & Emergency Events</option>
                    <option value="Technology / Cyber Incident">Technology / Cyber Incident</option>
                    <option value="Administrative / Policy Violations">Administrative / Policy Violations</option>
                    <option value="Lost & Found">Lost & Found</option>
                    <!-- <option value="Others">Others(Specify in Descriptive Title)</option> -->
                </select>
            </div>

            <!-- Report Title -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Descriptive Title <span class="text-red-500">*</span></label>
                <input type="text" name="description" placeholder="Brief description of the incident" required
                    class="w-full border border-gray-300 px-3 py-2.5 rounded-md text-sm placeholder:text-gray-400 focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
            </div>

            <!-- Full Description -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Full Description</label>
                <textarea name="full_description" rows="4"
                    placeholder="Provide detailed information about the incident"
                    class="w-full border border-gray-300 px-3 py-2.5 rounded-md text-sm placeholder:text-gray-400 focus:ring-2 focus:ring-blue-500 focus:outline-none transition resize"></textarea>
            </div>

            <!-- Location -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Location <span class="text-red-500">*</span></label>
                <input type="text" name="location" placeholder="Building, room, or area" required
                    class="w-full border border-gray-300 px-3 py-2.5 rounded-md text-sm placeholder:text-gray-400 focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
            </div>

            <!-- Reporter & Contact Info -->
            <div class="grid sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Reporter <span class="text-red-500">*</span></label>
                    <input type="text" name="reported_by" placeholder="Full name" required
                        class="w-full border border-gray-300 px-3 py-2.5 rounded-md text-sm placeholder:text-gray-400 focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Contact Info <span class="text-red-500">*</span></label>
                    <input type="text" name="contact_info" placeholder="09********* or Email" required
                        class="w-full border border-gray-300 px-3 py-2.5 rounded-md text-sm placeholder:text-gray-400 focus:ring-2 focus:ring-blue-500 focus:outline-none transition">
                </div>
            </div>

            <!-- ISI Level -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">ISI Level <span class="text-red-500">*</span></label>
                <select name="esi_level" required
                    class="w-full border border-gray-300 px-3 py-2.5 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none transition placeholder:text-gray-400">
                    <option value="" disabled selected>Select Severity Level</option>
                    <option value="1">Critical</option>
                    <option value="2">High</option>
                    <option value="3">Medium</option>
                    <option value="4">Low</option>
                </select>
            </div>

            <!-- Evidence Image Upload -->
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-2">Evidence Image</label>
                <input type="file" name="evidence_image" accept="image/*"
                    class="w-full border border-gray-300 px-3 py-2.5 rounded-md text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none transition file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100">
                <p class="text-xs text-gray-500 mt-2">PNG, JPG, GIF up to 10MB</p>
            </div>

            <!-- Buttons -->
            <div class="flex flex-col sm:flex-row justify-end gap-3 pt-4 border-t border-gray-100">
                <button type="button"
                    class="w-full sm:w-auto px-8 py-2.5 text-sm text-gray rounded-full font-medium hover:bg-gray-300 transition close-modal">
                    Cancel
                </button>
                <button type="button" id="submitCreateReport"
                    class="w-full sm:w-auto px-8 py-2.5 bg-blue-600 text-white text-sm rounded-full font-medium hover:bg-blue-700 transition">
                    Submit
                </button>
            </div>
        </form>
    </div>
</div>
<script>
  document.addEventListener('DOMContentLoaded', function () {
      const modal = document.getElementById('createModal');
      const closeModalBtns = modal.querySelectorAll('.close-modal');
      const submitBtn = document.getElementById('submitCreateReport');
      const form = document.getElementById('createReportForm');
      const requiredFields = form.querySelectorAll('[required]');

      function resetForm() {
          form.reset();
          requiredFields.forEach(field => {
              field.classList.remove('border-red-500', 'focus:ring-red-500');
              field.classList.add('border-gray-300', 'focus:ring-blue-500');
              const errorEl = field.parentElement.querySelector('.error-message');
              if (errorEl) errorEl.remove();
          });
      }

      function validateFields() {
          let isValid = true;
          let firstInvalid = null;
          requiredFields.forEach(field => {
              let errorEl = field.parentElement.querySelector('.error-message');
              if (!errorEl) {
                  errorEl = document.createElement('span');
                  errorEl.className = 'error-message text-red-500 text-sm mt-1 block';
                  field.parentElement.appendChild(errorEl);
              }
              if (!field.value.trim()) {
                  field.classList.add('border-red-500', 'focus:ring-red-500');
                  field.classList.remove('border-gray-300', 'focus:ring-blue-500');
                  errorEl.textContent = 'This field is required.';
                  if (!firstInvalid) firstInvalid = field;
                  isValid = false;
              } else {
                  field.classList.remove('border-red-500', 'focus:ring-red-500');
                  field.classList.add('border-gray-300', 'focus:ring-blue-500');
                  errorEl.textContent = '';
              }
          });
          if (firstInvalid) firstInvalid.focus();
          return isValid;
      }

      requiredFields.forEach(field => {
          field.addEventListener('input', () => {
              if (field.value.trim()) {
                  field.classList.remove('border-red-500', 'focus:ring-red-500');
                  field.classList.add('border-gray-300', 'focus:ring-blue-500');
                  const errorEl = field.parentElement.querySelector('.error-message');
                  if (errorEl) errorEl.textContent = '';
              }
          });
          field.addEventListener('change', () => {
              if (field.value.trim()) {
                  field.classList.remove('border-red-500', 'focus:ring-red-500');
                  field.classList.add('border-gray-300', 'focus:ring-blue-500');
                  const errorEl = field.parentElement.querySelector('.error-message');
                  if (errorEl) errorEl.textContent = '';
              }
          });
      });

      closeModalBtns.forEach(btn => {
          btn.addEventListener('click', () => {
              modal.classList.add('hidden');
              resetForm();
          });
      });

      modal.addEventListener('click', e => {
          if (e.target === modal) {
              modal.classList.add('hidden');
              resetForm();
          }
      });

      document.addEventListener('keydown', (e) => {
          if (e.key === 'Escape' && !modal.classList.contains('hidden')) {
              modal.classList.add('hidden');
              resetForm();
          }
      });

      submitBtn.addEventListener('click', function () {
          if (!validateFields()) return;

          Swal.fire({
              title: 'Submit Report?',
              text: "Please make sure all details are correct before submitting.",
              showCancelButton: true,
              confirmButtonColor: '#2563eb',
              confirmButtonText: 'Submit',
              cancelButtonText: 'Cancel',
              reverseButtons: true
          }).then((result) => {
              if (result.isConfirmed) form.submit();
          });
      });
      
      @if(session('success'))
          Swal.fire({
              title: 'Success!',
              text: "{{ session('success') }}",
              icon: 'success'
          });
      @endif
  });
</script>