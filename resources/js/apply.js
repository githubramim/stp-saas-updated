import Cropper from 'cropperjs';
import Alpine from 'alpinejs';

window.Alpine = Alpine;
Alpine.start();

function initCropper(inputId, modalId, previewId, hiddenId, ratio, targetW, targetH) {
    const input = document.getElementById(inputId);
    if (!input) return;

    let cropper = null;
    let modal = null;

    function createModal() {
        const div = document.createElement('div');
        div.innerHTML = `
            <div id="${modalId}" class="fixed inset-0 z-50 flex items-center justify-center bg-black/60 hidden">
                <div class="bg-white rounded-2xl shadow-xl max-w-lg w-full mx-4 overflow-hidden">
                    <div class="p-4 border-b border-gray-100 flex items-center justify-between">
                        <h3 class="font-semibold text-gray-900">Crop Image</h3>
                        <button type="button" class="text-gray-400 hover:text-gray-600 text-2xl leading-none close-${modalId}">&times;</button>
                    </div>
                    <div class="p-4">
                        <img class="cropper-image-${modalId} max-w-full" src="" alt="Crop">
                    </div>
                    <div class="p-4 border-t border-gray-100 flex justify-end gap-2">
                        <button type="button" class="px-4 py-2 text-sm text-gray-600 hover:text-gray-800 close-${modalId}">Cancel</button>
                        <button type="button" class="px-6 py-2 text-sm bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 crop-${modalId}">Crop & Save</button>
                    </div>
                </div>
            </div>`;
        document.body.appendChild(div.firstElementChild);
    }

    input.addEventListener('change', (e) => {
        const file = e.target.files[0];
        if (!file) return;

        if (!modal) {
            createModal();
            modal = document.getElementById(modalId);
            modal.querySelectorAll(`.close-${modalId}`).forEach(el => {
                el.addEventListener('click', () => {
                    modal.classList.add('hidden');
                    input.value = '';
                });
            });
            modal.querySelector(`.crop-${modalId}`).addEventListener('click', () => {
                if (!cropper) return;
                const canvas = cropper.getCroppedCanvas({ width: targetW, height: targetH });
                const hidden = document.getElementById(hiddenId);
                if (hidden) hidden.value = canvas.toDataURL('image/jpeg', 0.9);
                const preview = document.getElementById(previewId);
                if (preview) {
                    preview.src = canvas.toDataURL('image/jpeg', 0.9);
                    preview.classList.remove('hidden');
                }
                const placeholder = document.getElementById(previewId.replace('preview', 'placeholder'));
                if (placeholder) placeholder.classList.add('hidden');
                modal.classList.add('hidden');
                if (cropper) { cropper.destroy(); cropper = null; }
            });
        }

        const reader = new FileReader();
        reader.onload = (e) => {
            modal.classList.remove('hidden');
            const img = modal.querySelector(`.cropper-image-${modalId}`);
            img.src = e.target.result;
            if (cropper) cropper.destroy();
            cropper = new Cropper(img, {
                aspectRatio: ratio,
                viewMode: 1,
                autoCropArea: 1,
                responsive: true,
            });
        };
        reader.readAsDataURL(file);
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initCropper('photo_input', 'photo_modal', 'photo_preview', 'photo_base64', 1, 300, 300);
    initCropper('signature_input', 'signature_modal', 'signature_preview', 'signature_base64', 300 / 80, 300, 80);
});
